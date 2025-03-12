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
$stmt->bindValue(':id', $user_id, PDO::PARAM_INT);

// Execute the prepared statement
$stmt->execute();

if ($stmt->execute()) {

    $row = $stmt->fetch();
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

    // gerando os dados para o qrcode
    $qrCodeUrl = $google2fa->getQRCodeUrl(
        'Company Name',
        'company@email.com',
        $otp_secret
    );



    // gerando a url que direciona para o qrcode
    $qrcode_url = (new QRCode)->render($qrCodeUrl);

    // pesquisar no banco os códigos de backup que o usuário já possa ter gerado
    $sql = "SELECT backup_code FROM backupcodes WHERE cod_user = :id AND used = 0";

    // For SQLite3, use a different parameter binding approach
    $stmt = $link->prepare($sql);

    // SQLite3 uses different binding method
    $stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);

    $recovery_codes = [];

    $link->beginTransaction();
    $link->commit();

    if ($stmt->execute()) {

        $rows = $stmt->rowCount();

//        print_r($rows);
//        exit();

        // verficar se retornou algum resultado
        if ($rows > 0) {
            while ($row = $stmt->fetch()) {
                $recovery_codes[] = $row["backup_code"];
            }
        } else {

            $link->beginTransaction();
            for ($i = 0; $i < 8; $i++) {
                $randon = random_int(1000, 99999999);
                $recovery_codes[$i] = sprintf("%'.08d", $randon);

                // Prepare an insert statement
                $sql = "INSERT INTO backupcodes (cod_user, backup_code) VALUES (:cod_user, :backupcode)";

                $stmt = $link->prepare($sql);
                // Set parameters
                $cod_user = $user_id;
                $backupcode = $recovery_codes[$i];

                // Bind variables to the prepared statement as parameters
                $stmt->bindValue(":cod_user", $cod_user, PDO::PARAM_STR);
                $stmt->bindValue(":backupcode", $backupcode, PDO::PARAM_STR);

                // Attempt to execute the prepared statement
                if (!$stmt->execute()) {
                    print_r($stmt);
                    exit();
                }
            }
            $link->commit();
        }

    } else {
        header("location: error.php");
        exit;
    }

} else {
    header("location: error.php");
    exit;
}

// Close statement
unset($stmt);

// Close connection
unset($link);

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

    <?php
    if (!empty($recovery_codes)) {
        ?>
        <p>códigos de recuperação: </p>


        <?php
        for ($i = 0; $i < count($recovery_codes); $i++) {
            echo "<p>" . $recovery_codes[$i] . "</p>";
        }
        ?>
        <?php
    }
    ?>

</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="js/bootstrap.bundle.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>
