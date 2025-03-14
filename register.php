<?php
if (!isset($_SESSION)) session_start();

const _DEFVAR = 1;

// Include config file
require_once "config.php";

include 'helper\PasswordValidator.php';


// Define variables and initialize with empty values
$username = $password = $confirm_password = "";
$username_err = $password_err = $confirm_password_err = "";

// Processing form data when form is submitted
if (!empty($_POST)) {

    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"]))) {
        $username_err = "Username can only contain letters, numbers, and underscores.";
    } else {
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE username = :username";
        $sql = "SELECT COUNT(*) FROM users WHERE username = :username";

        $stmt = $link->prepare($sql);

        // Set parameters
        $param_username = trim($_POST["username"]);

        // Bind variables to the prepared statement as parameters
        $stmt->bindValue(":username", $param_username, PDO::PARAM_STR);

        if ($stmt->execute()) {

            // Fetch the result
            if ($row = $stmt->fetch()) {
                if ($row[0] > 0) {  // Check if username exists
                    $username_err = "This username is already taken.";
                } else {
                    $username = trim($_POST["username"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // check password strength
    $passValidator = new PasswordStrengthValidator($password, 8);

    if (!$passValidator->isValid()) {
        $password_err = $passValidator->getErrorMessage();
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    // Check input errors before inserting into database
    if (empty($username_err) && empty($password_err) && empty($confirm_password_err)) {
        // Prepare an insert statement
        $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";

        $stmt = $link->prepare($sql);

        // Set parameters
        $param_username = $username;
        // encrypt password
        $param_password = password_hash($password, PASSWORD_DEFAULT);

        // Bind variables to the prepared statement as parameters
        $stmt->bindValue(":username", $param_username, PDO::PARAM_STR);
        $stmt->bindValue(":password", $param_password, PDO::PARAM_STR);

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // gravando o id do usuário na session
            $_SESSION["id"] = $link->lastInsertId();

            // Redirect to login page
            header("location: registerotp.php");
            exit();
        } else {
            echo "Oops! Something went wrong. Please try again later.";
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
                                <input type="password" name="confirm_password" id="confirm_password" placeholder="Password"
                                       class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>"
                                       value="<?php echo $confirm_password; ?>" required>
                                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-grid my-3">
                                <button class="btn btn-primary btn-lg" type="submit">Log in</button>
                            </div>
                        </div>
                        <div class="col-12">
                            <p class="m-0 text-secondary text-center">Already have an account? <a href="login.php" class="link-primary text-decoration-none">Click here</a></p>
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
