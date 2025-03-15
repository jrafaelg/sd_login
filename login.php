<?php
// Initialize the session
if (!isset($_SESSION)) session_start();

// definindo variável para impedir acesso direto ao arquivo config.php
const _DEFVAR = 1;

// Check if the user is already logged in, if yes then redirect him to welcome page
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.php");
    exit;
}

// Include config file
require_once "config.php";

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";


// Processing form data when form is submitted
if (!empty($_POST)) {

    // Check if username is empty
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate credentials
    if (empty($username_err) && empty($password_err)) {

        // Prepare a select statement
        $sql = "SELECT id, username, password FROM users WHERE username = :username";

        // For SQLite3, use a different parameter binding approach
        $stmt = $link->prepare($sql);

        // SQLite3 uses different binding method
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);


        // Execute the prepared statement
        if ($stmt->execute()) {

            $row = $stmt->fetch();

            if ($row) { // Check if there are results

                //dump($row);
                //extract($stmt->fetch(), EXTR_OVERWRITE);
                //$id = !empty($id) ? $id : 0;

                $id = !empty($row["id"]) ? $row["id"] : 0;
                $hashed_password = $row["password"];

                if (password_verify($password, $hashed_password)) {

                    // Store data in session variables
                    $_SESSION["loggedin"] = true;
                    $_SESSION["id"] = $id;
                    $_SESSION["username"] = $username;

                    // Redirect user to welcome page
                    //header("location: index.php");
                    header("location: checkotp.php");
                    exit();

                } else {
                    $login_err = "Invalid username or password.";
                }
            } else {
                $login_err = "Invalid username or password.";
            }

        } else {
            // Username doesn't exist
            $login_err = "Invalid username or password.";
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
                    <p class="text-secondary">Please fill this form to login.</p>
                </div>

                <?php
                if (!empty($login_err)) {
                    echo '<div class="alert alert-danger">' . $login_err . '</div>';
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
                        <!--
                        <div class="col-12">
                            <div class="d-flex gap-2 justify-content-between">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" name="rememberMe" id="rememberMe">
                                    <label class="form-check-label text-secondary" for="rememberMe">
                                        Keep me logged in
                                    </label>
                                </div>
                                <a href="#!" class="link-primary text-decoration-none">Forgot password?</a>
                            </div>
                        </div>
                        -->
                        <div class="col-12">
                            <div class="d-grid my-3">
                                <button class="btn btn-primary btn-lg" type="submit">Log in</button>
                            </div>
                        </div>
                        <div class="col-12">
                            <p class="m-0 text-secondary text-center">Don't have an account? <a href="register.php"
                                                                                                class="link-primary text-decoration-none">Click
                                    here</a></p>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>


<!--<div class="wrapper">-->
<!--    <div class="container container-sm">-->
<!--        <div class="row justify-content-center">-->
<!--            <div class="col-4">-->
<!--                <div class="mt-5 mb-3 clearfix">-->
<!---->
<!--                    <h2 class="text-center">Login</h2>-->
<!---->
<!--                    <p class="text-center">Preencha suas credenciais para logar.</p>-->
<!---->
<!--                    --><?php
//                    if (!empty($login_err)) {
//                        echo '<div class="alert alert-danger">' . $login_err . '</div>';
//                    }
//                    ?>
<!---->
<!--                    <form class="form-container" action="-->
<?php //echo htmlspecialchars($_SERVER["PHP_SELF"]); ?><!--" method="post">-->
<!--                        <div class="form-group mb-3 text-left">-->
<!--                            <label class="text-left !important">Username</label>-->
<!--                            <input type="text" name="username"-->
<!--                                   class="form-control -->
<?php //echo (!empty($username_err)) ? 'is-invalid' : ''; ?><!--"-->
<!--                                   value="--><?php //echo $username; ?><!--">-->
<!--                            <span class="invalid-feedback">--><?php //echo $username_err; ?><!--</span>-->
<!--                        </div>-->
<!--                        <div class="form-group mb-3">-->
<!--                            <label>Password</label>-->
<!--                            <input type="password" name="password"-->
<!--                                   class="form-control -->
<?php //echo (!empty($password_err)) ? 'is-invalid' : ''; ?><!--">-->
<!--                            <span class="invalid-feedback">--><?php //echo $password_err; ?><!--</span>-->
<!--                        </div>-->
<!--                        <div class="form-group mb-3 text-center">-->
<!--                        </div>-->
<!--                        <!-- -->
<!--                        <div class="col-12">-->
<!--                            <div class="d-flex gap-2 justify-content-between">-->
<!--                                <div class="form-check">-->
<!--                                    <input class="form-check-input" type="checkbox" value="" name="rememberMe" id="rememberMe">-->
<!--                                    <label class="form-check-label text-secondary" for="rememberMe">-->
<!--                                        Keep me logged in-->
<!--                                    </label>-->
<!--                                </div>-->
<!--                                <a href="#!" class="link-primary text-decoration-none">Forgot password?</a>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                        --> -->
<!--                        <div class="d-grid my-3">-->
<!--                            <button type="submit" class="btn btn-primary d-grid">Sign in</button>-->
<!--                        </div>-->
<!--                        <p class="text-center">Don't have an account? <a href="register.php">Sign up now</a>.</p>-->
<!--                    </form>-->
<!--                </div>-->
<!---->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--</div>-->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="js/bootstrap.bundle.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>