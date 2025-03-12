<?php
if (!isset($_SESSION)) session_start();

const _DEFVAR = 1;

// Include config file
require_once "config.php";
checkLongIn();
checkOTP();

$error = "";

// Check existence of id parameter before processing further
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {

    // Prepare a select statement
    $sql = "SELECT * FROM employees WHERE id = :id";
    $stmt = $link->prepare($sql);
    // Bind parameter
    $stmt->bindValue(':id', trim($_GET["id"]), PDO::PARAM_INT);

    if ($stmt->execute()) {

        // Attempt to execute the prepared statement
        $row = $stmt->fetch();

        //dump($row);

        if ($row) {
            $name = $row["name"];
            $address = $row["address"];
            $salary = $row["salary"];
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
    <style>
        .wrapper {
            width: 600px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h1 class="mt-5 mb-3">Ver registro</h1>
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
                <?php } ?>
                <p><a href="index.php" class="btn btn-primary">Voltar</a></p>
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