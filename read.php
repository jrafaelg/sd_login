<?php

if (!isset($_SESSION)) session_start();

const _DEFVAR = 1;

// Include config file
require_once "config.php";
checkLongIn();
checkOTP();

include $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use \phpseclib3\Crypt\RSA;
use \helper\CipherHelper;

$name = $address = $salary = $digital_sign = '';

$error = "";

$id = !empty($_GET['id']) ? (int)$_GET['id'] : '';

// Check existence of id parameter before processing further
if (!empty($id)) {

    // Prepare a select statement
    $sql = "SELECT * FROM employees WHERE id = :id";
    $stmt = $link->prepare($sql);
    // Bind parameter
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {

        // Attempt to execute the prepared statement
        $row = $stmt->fetch();

        //dump($row);

        if ($row) {
            $name = $row["name"];
            $address = $row["address"];
            $salary = $row["salary"];
            $id_user = $row["cod_user"];
            $stored_signature = $row["digital_sign"];

            // Prepare a select statement
            $sql = "SELECT private_key, public_key FROM users WHERE id = :id_user";

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
            }

            if (empty($error)) {

                $stored_public_key = $row['public_key'];

                // get resume form register data
                $resume = $name . $address . $salary . $id_user;

                $public_key = RSA::loadPublicKey($stored_public_key);

                $deciphed_signature = base64_decode($stored_signature);
                $digital_sign = $public_key->verify($resume, $deciphed_signature) ? 'valid signature' : 'invalid signature';

            }


        } else {
            $error = "registro não encontrado";
        }

    } else {
        $error = "Oops! Algo deu errado. Tente novamente mais tarde.";
    }

} else {
    // URL doesn't contain id parameter. Redirect to error page
    header("location: error.php");
    exit();
}

// destruindo as variáveis do bando de dados
disconnectDataBase();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Record</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5 col-xxl-4">
            <div class="card-body p-3 p-md-4 p-xl-5">

                <div class="text-center mb-3">
                    <h2 class="fw-normal text-center  mb-4">See Record</h2>
                    <p class="text-secondary">Record details.</p>
                </div>

                <?php if (!empty($error)) { ?>
                    <div class="alert alert-info"><em><?php echo $error ?></em></div>

                <?php } else { ?>
                    <div class="form-group">
                        <label>Name</label>
                        <p><b><?php echo $name; ?></b></p>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <p><b><?php echo $address; ?></b></p>
                    </div>
                    <div class="form-group">
                        <label>Salary</label>
                        <p><b><?php echo $salary; ?></b></p>
                    </div>

                    <div class="form-group">
                        <label>Digital signature</label>
                        <p class="fw-bold  <?php echo $digital_sign == "valid signature" ? 'text-success' : 'text-danger' ?>">
                            <?php echo $digital_sign; ?>
                        </p>
                    </div>
                <?php } ?>
                <p class="text-center">
                    <a href="index.php" class="btn btn-primary">Voltar</a>
                </p>

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