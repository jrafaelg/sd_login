<?php
// Initialize the session
if (!isset($_SESSION)) session_start();

// definindo variável para impedir acesso direto ao arquivo config.php
const _DEFVAR = 1;

// Include config file
require_once "config.php";
checkLongIn();
checkOTP();

include $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use \phpseclib3\Crypt\RSA;
use \helper\CipherHelper;


// Define variables and initialize with empty values
$name = $address = $salary = $password_sign = "";
//$name_err = $address_err = $salary_err = $password_sign_err = "";
$error = [];

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate name
    $input_name = trim($_POST["name"]);
    if (empty($input_name)) {
        $error['name_err'] = "Name is required";
    } else {
        $name = $input_name;
    }

    // Validate address
    $input_address = trim($_POST["address"]);
    if (empty($input_address)) {
        $error['address_err'] = "Address is required";
    } else {
        $address = $input_address;
    }

    // Validate salary
    $input_salary = !empty($_POST["salary"]) ? (int)$_POST["salary"] : 0;


    if ($input_salary < 1) {
        $error['salary_err'] = "Please enter the salary amount.";
    } else {
        $salary = $input_salary;
    }

    $password_sign = !empty($_POST["password_sign"]) ? $_POST["password_sign"] : "";

    if (empty($password_sign)) {
        $error['password_sign_err'] = "Password signature is required";
    }


    if (empty($error)) {
        $id_user = $_SESSION["id"];

        // Prepare a select statement
        $sql = "SELECT private_key, public_key FROM users WHERE id = :id_user";


        $stmt = $link->prepare($sql);

        // Bind variables to the prepared statement as parameters
        $stmt->bindValue(":id_user", $id_user, PDO::PARAM_INT);

        if ($stmt->execute()) {

            // Fetch the result
            if ($row = $stmt->fetch()) {
                if (empty($row['private_key'])) {  // Check private key
                    $error['msg'] = "Invalid private key.";
                }
            } else {
                $error['msg'] = "Oops! Something went wrong. Please try again.";
            }
        }

        $deciphed_private_key = '';

        if (empty($error)) {


            $cipher = new CipherHelper();

            // tentando decriptografar a chave privada
            $deciphed_private_key = $cipher->decrypt($row['private_key'], $password_sign);

            if (empty($deciphed_private_key)) {
                $error['msg'] = "Invalid password sign.";
            }
        }
    }

    // Check input errors before inserting in database
    if (empty($error)) {

        // get resume form register data
        $resume = $name . $address . $salary . $id_user;

        // loading the deciphed private key
        $private = RSA::loadPrivateKey($deciphed_private_key);

        // sign the resume
        $sing = $private->sign($resume);
        $digital_sign = base64_encode($sing);

        // Prepare an insert statement
        $sql = "INSERT INTO employees (name, address, salary, cod_user, digital_sign) VALUES (:name, :address, :salary, :cod_user, :digital_sign)";


        $stmt = $link->prepare($sql);

        // Bind parameters
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':address', $address, PDO::PARAM_STR);
        $stmt->bindValue(':salary', $salary, PDO::PARAM_INT);
        $stmt->bindValue(':cod_user', $id_user, PDO::PARAM_INT);
        $stmt->bindValue(':digital_sign', $digital_sign, PDO::PARAM_STR);

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Records created successfully. Redirect to landing page
            header("location: index.php");
            exit();
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }

    } else {
        $error['msg'] = "Oops! Something went wrong. Please try again.";
    }

}

// destruindo as variáveis do bando de dados
disconnectDataBase();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Record</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5 col-xxl-4">
            <div class="card-body p-3 p-md-4 p-xl-5">

                <div class="text-center mb-3">
                    <h2 class="fw-normal text-center  mb-4">Create Record</h2>
                    <p class="text-secondary">Please fill this form and submit to add employee record to the
                        database.</p>
                </div>

                <?php
                if (!empty($error)) {
                    echo '<div class="alert alert-danger">' . $error['msg'] . '</div>';
                }
                ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="row gy-2 overflow-hidden">
                        <div class="col-12">
                            <div class="form-floating mb-4">
                                <input type="text" name="name" id="name" placeholder="Name" required
                                       class="form-control <?php echo (!empty($error['name_err'])) ? 'is-invalid' : ''; ?>"
                                       value="<?php echo $name; ?>">
                                <span class="invalid-feedback"><?php echo !empty($error['name_err']); ?></span>
                                <label for="name">Name</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-floating mb-3">
                                <textarea
                                        name="address"
                                        id="address"
                                        placeholder="Address"
                                        class="form-control <?php echo (!empty($error['address_err'])) ? 'is-invalid' : ''; ?>"
                                        required
                                ><?php echo $address; ?></textarea>
                                <span class="invalid-feedback"><?php echo !empty($error['address_err']); ?></span>
                                <label for="address">Address</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-floating mb-3">
                                <input type="text" name="salary" id="salary" placeholder="Salary" required
                                       class="form-control <?php echo (!empty($error['salary_err'])) ? 'is-invalid' : ''; ?>"
                                       value="<?php echo $salary; ?>">
                                <span class="invalid-feedback"><?php echo !empty($error['salary_err']); ?></span>
                                <label for="salary">Salary</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-floating mb-3">
                                <input type="password" name="password_sign" id="password_sign" required
                                       placeholder="Password Sign"
                                       class="form-control <?php echo (!empty($error['password_sign_err'])) ? 'is-invalid' : ''; ?>"
                                       value="<?php echo $password_sign; ?>">
                                <span class="invalid-feedback"><?php echo !empty($error['password_sign_err']); ?></span>
                                <label for="password_sign">Password Sign</label>
                            </div>
                        </div>

                        <div class="d-flex gap-5 justify-content-center">
                            <button type="submit"
                                    class="btn btn-primary btn-block w-100">
                                Submit
                            </button>
                            <a href="index.php" class="btn btn-block btn-secondary w-100">Cancel</a>
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
