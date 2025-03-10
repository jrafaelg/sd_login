<?php
// Initialize the session
if (!isset($_SESSION)) session_start();

// Check if the user is already logged in, if yes then redirect him to welcome page
if (!isset($_SESSION["loggedin"]) or $_SESSION["loggedin"] === false) {
    header("location: login.php");
    exit;
}

// Include config file
require_once "config.php";

include 'vendor\autoload.php';

use PragmaRX\Google2FA\Google2FA;

// Define variables and initialize with empty values
$otp_key = "";
$otp_err = "";

// Processing form data when form is submitted
if (!empty($_POST)) {

    if (!empty($_POST['otp_key'])) {
        $otp_key = (int)trim($_POST['otp_key']);
    }

    // Check if otp_key is empty
    if (empty($otp_key)) {
        $otp_err = "insira seu código OTP.";
    }

    // Validate credentials
    if (empty($otp_err)) {

        $user_id = $_SESSION["id"];

        // Prepare a select statement
        $sql = "SELECT id, username, password, otp_secret, otp_ts FROM users WHERE id = :id";

        // For SQLite3, use a different parameter binding approach
        $stmt = $link->prepare($sql);

        // SQLite3 uses different binding method
        $stmt->bindValue(':id', $user_id, SQLITE3_TEXT);

        // Execute the prepared statement
        $result = $stmt->execute();

        // Use fetchArray() on the result, not on the statement
        if ($row = $result->fetchArray(SQLITE3_ASSOC)) {

            $otp_secret = $row["otp_secret"];
            $otp_ts = $row["otp_ts"];

            $google2fa = new Google2FA();

            // metodo para garantir que cada código seja utilizado uma única vez
            $timestamp = $google2fa->verifyKeyNewer($otp_secret, $otp_key, $otp_ts);

            // se for diferente de false, é pq o código é válido
            if ($timestamp !== false) {
                // sucesso

                // neste caso, precisa atualizar o banco para registrar o novo
                // timestamp
                $sql = "UPDATE users SET otp_ts = :otp_ts WHERE id = :id";

                if ($stmt = $link->prepare($sql)) {
                    // Bind variables to the prepared statement as parameters
                    $stmt->bindValue(':otp_ts', $timestamp, SQLITE3_INTEGER);
                    $stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);

                    // Attempt to execute the prepared statement
                    if ($stmt->execute()) {
                        // Records updated successfully. Redirect to landing page
                        header("location: index.php");
                        exit();
                    } else {
                        // não conseguiu atualizar
                        header("location: error.php");
                        exit;
                    }
                }

            } else {
                // não conseguiu logar
                $otp_err = "código OTP incorreto.";
            }

        } else {
            // Username doesn't exist
            $login_err = "código OTP incorreto.";
        }


        // Close statement
        $stmt->close();

        // Close connection
        $link->close();
    }

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
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
    <h2>Login</h2>
    <p>Please fill in your credentials to login.</p>

    <?php
    if (!empty($otp_err)) {
        echo '<div class="alert alert-danger">' . $otp_err . '</div>';
    }
    ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group">
            <label>Código OTP</label>
            <input type="text" name="otp_key"
                   class="form-control <?php echo (!empty($otp_err)) ? 'is-invalid' : ''; ?>"
                   value="<?php echo $otp_key; ?>">
            <span class="invalid-feedback"><?php echo $otp_err; ?></span>
        </div>
        <div class="form-group">
            <input type="submit" class="btn btn-primary" value="Login">
        </div>
    </form>
</div>
<script src="js/bootstrap.bundle.js"></script>
</body>
</html>