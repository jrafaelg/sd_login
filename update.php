<?php
// Initialize the session
if (!isset($_SESSION)) session_start();

const _DEFVAR = 1;

// Include config file
require_once "config.php";
checkLongIn();
checkOTP();

include $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use \phpseclib3\Crypt\RSA;
use \helper\CipherHelper;


// Define variables and initialize with empty values
$name = $address = $salary = $password_sign = $deciphed_private_key = $stored_signature = $digital_sign = "";
$error = [];

// Processing form data when form is submitted
if (!empty($_POST["id"])) {

    // Get hidden input value
    $id = (int)$_POST["id"];

    // Validate name
    $input_name = !empty($_POST["name"]) ? trim($_POST["name"]) : "";
    if (empty($input_name)) {
        $error['name_err'] = "Please enter a name.";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $input_name)) {
        $error['name_err'] = "Please enter a valid name.";
    } else {
        $name = $input_name;
    }

    // Validate address
    $input_address = !empty($_POST["address"]) ? trim($_POST["address"]) : "";
    if (empty($input_address)) {
        $error['address_err'] = "Please enter an address.";
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

    if (empty($error)) {

        if (empty($password_sign)) {
            $error['password_sign_err'] = "Password is required";
        }

        $id_user = $_SESSION["id"];

        // Prepare a select statement
        $sql = "SELECT private_key FROM users WHERE id = :id_user";


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

            //instanciando a classe helper para cifrar/decifrar
            $cipher = new CipherHelper();

            // tentando decriptografar a chave privada
            $deciphed_private_key = $cipher->decrypt($row['private_key'], $password_sign);

            if (empty($deciphed_private_key)) {
                $error['msg'] = "Invalid signature password.";
            }
        }
    }

    // Check input errors before updating the database
    if (empty($error)) {

        // get resume form update data
        $resume = $name . $address . $salary . $id_user;

        // loading the deciphed private key
        $private = RSA::loadPrivateKey($deciphed_private_key);

        // sign the resume
        $sing = $private->sign($resume);
        $digital_sign = base64_encode($sing);


        // Prepare an update statement
        $sql = "UPDATE employees SET name = :name, address = :address, salary = :salary, digital_sign = :digital_sign WHERE id = :id";

        if ($stmt = $link->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindValue(':name', $name, PDO::PARAM_STR);
            $stmt->bindValue(':address', $address, PDO::PARAM_STR);
            $stmt->bindValue(':salary', $salary, PDO::PARAM_INT);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':digital_sign', $digital_sign, PDO::PARAM_STR);


            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Records updated successfully. Redirect to landing page
                header("location: index.php");
                exit();
            } else {
                $error['msg'] = "Oops! Something went wrong. Please try again.";
            }
        }
    } else {

    }

} else {
    // trata-se de acesso à página

    // Get URL parameter
    $id = isset($_GET["id"]) ? (int)$_GET["id"] : null;


    // Check existence of id parameter before processing further
    if (!empty($id)) {

        // trazendo os dados
        // Prepare a select statement
        $sql = "SELECT * FROM employees WHERE id = :id";
        $stmt = $link->prepare($sql);

        // Bind parameter
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            // Attempt to execute the prepared statement
            $row = $stmt->fetch();

            if ($row) {
                // Retrieve individual field values
                $name = $row["name"];
                $address = $row["address"];
                $salary = $row["salary"];
                $id_user = $row["cod_user"];

            } else {
                // register not found
                $error['msg'] = "registro não encontrado";
            }
        } else {
            $error['msg'] = "registro não encontrado";
        }

    } else {
        $error['msg'] = "registro não encontrado";
    }
}

if (!empty($id)) {

    // trazendo os dados
    // Prepare a select statement
    $sql = "SELECT cod_user, digital_sign FROM employees WHERE id = :id";

    $stmt = $link->prepare($sql);

    // Bind parameter
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {

        // Attempt to execute the prepared statement
        $row = $stmt->fetch();

        if ($row) {
            // Retrieve individual field values
            $id_user = $row["cod_user"];
            $stored_signature = $row["digital_sign"];

            // checando a assinatura
            // Prepare a select statement
            $sql = "SELECT public_key FROM users WHERE id = :id_user";

            $stmt = $link->prepare($sql);

            // Bind variables to the prepared statement as parameters
            $stmt->bindValue(":id_user", $id_user, PDO::PARAM_INT);

            if ($stmt->execute()) {

                // Fetch the result
                if ($row = $stmt->fetch()) {
                    if (empty($row['public_key'])) {  // Check public key
                        $error['msg'] = "Invalid key.";
                    }
                } else {
                    $error['msg'] = "Oops! Something went wrong. Please try again.";
                }
            } else {
                // não conseguiu executar o sql de busca das chaves
                $error['msg'] = "retrive keys error";
            }

            $stored_public_key = $row['public_key'];

            if (!empty($stored_public_key)) {

                // get resume form register data
                $resume = $name . $address . $salary . $id_user;

                // loading public key
                $public_key = RSA::loadPublicKey($stored_public_key);

                // deciphe signature
                $deciphed_signature = base64_decode($stored_signature);

                // checking stored signature
                $digital_sign = $public_key->verify($resume, $deciphed_signature) ? 'valid signature' : 'invalid signature - update disabled';

            } else {
                $digital_sign = false;
                $error['msg'] = "retrive keys error";
            }

        } else {
            // register not found
            $error['msg'] = "registro não encontrado";
        }
    } else {
        $error['msg'] = "registro não encontrado";
    }

}


if (!empty($error)) {
    if (empty($error['msg'])) {
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
    <title>Update Record</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <!--    <link rel="stylesheet" href="https://bootstrapform.com/wp-content/themes/bootscore-5/css/lib/bootstrap.min.css?ver=6.7.2">-->

</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5 col-xxl-4">
            <div class="card-body p-3 p-md-4 p-xl-5">

                <div class="text-center mb-3">
                    <h2 class="fw-normal text-center  mb-4">Update Record</h2>
                    <p class="text-secondary">Please edit the input values and submit to update the employee record.</p>
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
                                       value="<?php echo $salary; ?>">
                                <span class="invalid-feedback"><?php echo !empty($error['password_sign_err']); ?></span>
                                <label for="password_sign">Password Sign</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Digital signature:</label>
                            <p class="fw-bold  <?php echo $digital_sign == "valid signature" ? 'text-success' : 'text-danger' ?>">
                                <?php echo $digital_sign; ?>
                            </p>
                        </div>

                        <input type="hidden" name="id" value="<?php echo $id; ?>"/>

                        <div class="d-flex gap-5 justify-content-center">
                            <button type="submit" <?php echo $digital_sign != "valid signature" ? 'disabled' : '' ?>
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


<script src=" https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="js/bootstrap.bundle.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>