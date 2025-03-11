<?php
if (!isset($_SESSION)) session_start();

// Include config file
require_once "config.php";

include 'vendor\autoload.php';

use PragmaRX\Google2FA\Google2FA;
use chillerlan\QRCode\{QRCode, QROptions};

if (!isset($_SESSION["id"]) || empty((int)$_SESSION["id"])) {
    // não conseguiu pegar o id do usuário
    header("location: error.php");
    exit;
}

$user_id = (int)$_SESSION["id"];

// verificar se o usuário já possui um otp_secret armazenado no banco
// Prepare a select statement
$sql = "SELECT otp_secret FROM users WHERE id = :id";

// For SQLite3, use a different parameter binding approach
$stmt = $link->prepare($sql);

// SQLite3 uses different binding method
$stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);

// Execute the prepared statement
$result = $stmt->execute();

// Use fetchArray() on the result, not on the statement
if ($row = $result->fetchArray(SQLITE3_ASSOC)) {

    $otp_secret = $row["otp_secret"];

    $google2fa = new Google2FA();

    // se não tiver, precisa gera um novo
    if (empty($otp_secret)) {

        // gerando um otp_secret
        $otp_secret = $google2fa->generateSecretKey();

        // atualizar o banco para registrar o novo otp_secret
        $sql = "UPDATE users SET otp_secret = :otp_secret WHERE id = :id";

        if ($stmt = $link->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindValue(':otp_secret', $otp_secret, SQLITE3_TEXT);
            $stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);

            // Attempt to execute the prepared statement
            if (!$stmt->execute()) {
                // não conseguiu atualizar
                header("location: error.php");
                exit;
            }
        }
    }

    $qrCodeUrl = $google2fa->getQRCodeUrl(
        'Company Name',
        'company@email.com',
        $otp_secret
    );

    $qrcode_url = (new QRCode)->render($qrCodeUrl);

} else {
    header("location: error.php");
    exit;
}


// Close statement
$stmt->close();

// Close connection
$link->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        body {
            font: 14px sans-serif;
        }

        .wrapper {
            width: 360px;
            padding: 20px;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <h2>Sign Up</h2>
    <p>Código OTP: <?php echo $otp_secret; ?></p>
    <p><img src="<?php echo $qrcode_url ?>" alt=""></p>

</div>
<script src="js/bootstrap.bundle.js"></script>

</body>
</html>
