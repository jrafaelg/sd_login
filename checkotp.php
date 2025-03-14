<?php
// Initialize the session
if (!isset($_SESSION)) session_start();

const _DEFVAR = 1;

// Include config file
require_once "config.php";
checkLongIn();

include 'vendor\autoload.php';

use PragmaRX\Google2FA\Google2FA;

// Define variables and initialize with empty values
$otp_key = "";
$otp_err = "";

// Processing form data when form is submitted
if (!empty($_POST)) {

    if (!empty($_POST['otp_key'])) {
        $otp_key = trim($_POST['otp_key']);
    }

    // Check if otp_key is empty
    if (empty($otp_key)) {
        $otp_err = "insira seu código OTP.";
    }

    // Validate credentials
    if (empty($otp_err)) {

        $otp_len = strlen($otp_key);

        $user_id = $_SESSION["id"];

        if ($otp_len == 6) {

            // Prepare a select statement
            $sql = "SELECT otp_secret, otp_ts FROM users WHERE id = :id";

            // For SQLite3, use a different parameter binding approach
            $stmt = $link->prepare($sql);

            // SQLite3 uses different binding method
            $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);

            // Use fetchArray() on the result, not on the statement
            if ($stmt->execute()) {

                $row = $stmt->fetch();

                $otp_secret = $row["otp_secret"];
                $otp_ts = $row["otp_ts"];

                $google2fa = new Google2FA();

                // metodo para garantir que cada código seja utilizado uma única vez
                $timestamp = $google2fa->verifyKeyNewer($otp_secret, $otp_key, $otp_ts);

                // se for diferente de false, é pq o código é válido
                if ($timestamp !== false) {

                    // neste caso, precisa atualizar o banco para registrar o novo
                    // timestamp
                    $sql = "UPDATE users SET otp_ts = :otp_ts WHERE id = :id";

                    if ($stmt = $link->prepare($sql)) {
                        // Bind variables to the prepared statement as parameters
                        $stmt->bindValue(':otp_ts', $timestamp, PDO::PARAM_INT);
                        $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);

                        // Attempt to execute the prepared statement
                        if ($stmt->execute()) {
                            // Records updated successfully. Redirect to landing page
                            $_SESSION['otp'] = true;
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
                // não conseguiu executar o SQL
                $otp_err = "código OTP incorreto.";
            }

        } else {
            // se o comprimento for diferente de 6 verifica se é um código de reserva


            // Prepare a select statement
            $sql = "SELECT id, backup_code FROM backupcodes WHERE cod_user = :id AND backup_code = :otp_key AND used = 0";

            // For SQLite3, use a different parameter binding approach
            $stmt = $link->prepare($sql);

            // SQLite3 uses different binding method
            $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
            $stmt->bindValue(':otp_key', $otp_key, PDO::PARAM_STR);

            if ($stmt->execute()) {

                $result = $stmt->fetchAll();

                // contando para ver quantos retornaram
                $rows = count($result);
                //dump($result);


                if ($rows > 0) {

                    $sql = "UPDATE backupcodes SET used = 1 WHERE id = :id";
                    $stmt = $link->prepare($sql);
                    $stmt->bindValue(':id', $result[0]["id"], PDO::PARAM_INT);

                    //dump($result);

                    // Attempt to execute the prepared statement
                    if ($stmt->execute()) {
                        //dump($stmt);
                        $_SESSION['otp'] = true;
                        // Records updated successfully. Redirect to landing page
                        header("location: index.php");
                        exit();
                    } else {
                        // não conseguiu executar a SQL de atualização
                        $otp_err = "código OTP incorreto.";
                    }


                } else {
                    // significa que o usuário não possui mais códigos de reserva
                    $otp_err = "código OTP incorreto.";
                }


            } else {

                // não conseguiu executar o SQL
                $otp_err = "código OTP incorreto.";

            }

        }//if ($otp_len == 6)


    }//if (empty($otp_err)) {

}

// destruindo as variáveis do bando de dados
disconnectDataBase();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5 col-xxl-4">
            <div class="card-body p-3 p-md-4 p-xl-5">

                <div class="text-center mb-3">
                    <h2 class="fw-normal text-center  mb-4">Sign in</h2>
                    <p class="text-secondary">Check your OTP key to login.</p>
                </div>

                <?php
                if (!empty($otp_err)) {
                    echo '<div class="alert alert-danger">' . $otp_err . '</div>';
                }
                ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="row gy-2 overflow-hidden">
                        <div class="col-12">
                            <div class="form-floating mb-3">
                                <input type="text" name="otp_key" id="otp_key" placeholder="otp_key"
                                       class="form-control <?php echo (!empty($otp_err)) ? 'is-invalid' : ''; ?>"
                                       value="<?php echo $otp_key; ?>" required>
                                <label for="otp_key" class="form-label">OTP key</label>
                                <span class="invalid-feedback"><?php echo $otp_err; ?></span>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-grid my-3">
                                <button class="btn btn-primary btn-lg" type="submit">Check</button>
                            </div>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="js/bootstrap.bundle.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>