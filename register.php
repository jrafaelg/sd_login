<?php
if (!isset($_SESSION)) session_start();

const _DEFVAR = 1;

// Include config file
require_once "config.php";

include $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use \helper\PasswordStrengthValidatorHelper;
use \phpseclib3\Crypt\RSA;
use \helper\CipherHelper;

// Define variables and initialize with empty values
$username = $password = $confirm_password = $password_sign = $confirm_password_sign = "";
$username_err = $password_err = $confirm_password_err = $password_sign_err = $confirm_password_sign_err = "";
$error = "";

// Processing form data when form is submitted
if (!empty($_POST)) {

    $username = !empty($_POST["username"]) ? trim($_POST["username"]) : "";

    // Validate username
    if (empty($username)) {
        $username_err = "Please enter a username.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $username_err = "Username can only contain letters, numbers, and underscores.";
    } else {
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE username = :username";
        $sql = "SELECT COUNT(*) FROM users WHERE username = :username";

        $stmt = $link->prepare($sql);

        // Bind variables to the prepared statement as parameters
        $stmt->bindValue(":username", $username, PDO::PARAM_STR);

        if ($stmt->execute()) {

            // Fetch the result
            if ($row = $stmt->fetch()) {
                if ($row[0] > 0) {  // Check if username exists
                    $username_err = "This username is already taken.";
                }
            } else {
                $error = "Oops! Something went wrong. Please try again.";
            }
        }
    }

    $password = !empty($_POST["password"]) ? trim($_POST["password"]) : "";

    // Validate password
    if (empty($password)) {
        $password_err = "Please enter a password.";
    }

    // check password strength
    $passValidator = new PasswordStrengthValidatorHelper($password, 8);

    if (!$passValidator->isValid()) {
        $password_err = $passValidator->getErrorMessage();
    }

    $confirm_password = !empty($_POST["confirm_password"]) ? trim($_POST["confirm_password"]) : "";

    // Validate confirm password
    if (empty($confirm_password)) {
        $confirm_password_err = "Please confirm password.";
    } else {
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    $password_sign = !empty($_POST["password_sign"]) ? trim($_POST["password_sign"]) : "";

    // Validate password
    if (empty($password_sign)) {
        $password_sign_err = "Please enter a signature password.";
    }

    // check password strength
    $passSignValidator = new PasswordStrengthValidatorHelper(
        $password_sign,
        6,
        false,
        false,
        false,
        false);

    if (!$passSignValidator->isValid()) {
        $password_sign_err = $passSignValidator->getErrorMessage();
    }

    $confirm_password_sign = !empty($_POST["confirm_password_sign"]) ? trim($_POST["confirm_password_sign"]) : "";

    // Validate confirm password
    if (empty($confirm_password_sign)) {
        $confirm_password_sign_err = "Please confirm signature password.";
    } else {
        if (empty($password_sign_err) && ($password_sign != $confirm_password_sign)) {
            $confirm_password_sign_err = "Password did not match.";
        }
    }


    // Check input errors before inserting into database
    if (empty($username_err)
        && empty($password_err)
        && empty($confirm_password_err)
        && empty($password_sign_err)
        && empty($confirm_password_sign_err)) {

        // gerando as chaves
        $private_key = RSA::createKey(2048);
        $public_key = $private_key->getPublicKey();

        // encriptando a chave privada com a senha de assinatura
        $cipher = new CipherHelper();
        $ciphedPrivateKey = $cipher->encrypt($private_key, $password_sign);

        // Prepare an insert statement
        $sql = "INSERT INTO users (username, password, private_key, public_key) VALUES (:username, :password,  :private_key, :public_key)";

        $stmt = $link->prepare($sql);

        // encrypt password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Bind variables to the prepared statement as parameters
        $stmt->bindValue(":username", $username, PDO::PARAM_STR);
        $stmt->bindValue(":password", $hashed_password, PDO::PARAM_STR);
        $stmt->bindValue(":private_key", $ciphedPrivateKey, PDO::PARAM_STR);
        $stmt->bindValue(":public_key", $public_key, PDO::PARAM_STR);

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // gravando o id do usuário na session
            $_SESSION["id"] = $link->lastInsertId();

            // Redirect to login page
            header("location: registerotp.php");
            exit();
        } else {
            $error = "Oops! Something went wrong. Please try again.";
        }
    }


}

// destruindo as variáveis do bando de dados
disconnectDataBase();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5 col-xxl-4">
            <div class="card-body p-3 p-md-4 p-xl-5">

                <div class="text-center mb-3">
                    <h2 class="fw-normal text-center  mb-4">Sign up</h2>
                    <p class="text-secondary">Please fill this form to create an account.</p>
                </div>

                <?php
                if (!empty($error)) {
                    echo '<div class="alert alert-danger">' . $error . '</div>';
                }
                ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="row gy-2 overflow-hidden">
                        <div class="col-12">
                            <div class="form-floating mb-3">
                                <input type="text" name="username" id="username" placeholder="Username"
                                       class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>"
                                       value="<?php echo $username; ?>" required>
                                <label for="username" class="form-label">Username</label>
                                <span class="invalid-feedback"><?php echo $username_err; ?></span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating mb-3">
                                <input type="password" name="password" id="password" placeholder="Password"
                                       class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>"
                                       value="<?php echo $password; ?>" required>
                                <span class="invalid-feedback"><?php echo $password_err; ?></span>
                                <label for="password" class="form-label">Password</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating mb-3">
                                <input type="password" name="confirm_password" id="confirm_password"
                                       placeholder="Password"
                                       class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>"
                                       value="<?php echo $confirm_password; ?>" required>
                                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating mb-3">
                                <input type="password" name="password_sign" id="password_sign"
                                       placeholder="Password Sing"
                                       class="form-control <?php echo (!empty($password_sign_err)) ? 'is-invalid' : ''; ?>"
                                       value="<?php echo $password_sign; ?>" required>
                                <span class="invalid-feedback"><?php echo $password_sign_err; ?></span>
                                <label for="password_sign" class="form-label">Signature password</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating mb-3">
                                <input type="password" name="confirm_password_sign" id="confirm_password_sign"
                                       placeholder="Confirm Password Sing"
                                       class="form-control <?php echo (!empty($confirm_password_sign_err)) ? 'is-invalid' : ''; ?>"
                                       value="<?php echo $confirm_password_sign; ?>" required>
                                <span class="invalid-feedback"><?php echo $confirm_password_sign_err; ?></span>
                                <label for="confirm_password_sign" class="form-label">Confirm signature
                                    password </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-grid my-3">
                                <button class="btn btn-primary btn-lg" type="submit">Create</button>
                            </div>
                        </div>
                        <div class="col-12">
                            <p class="m-0 text-secondary text-center">
                                Already have an account? <a href="login.php" class="link-primary text-decoration-none">Click
                                    here</a>
                            </p>
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
