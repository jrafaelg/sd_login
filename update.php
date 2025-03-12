<?php
// Initialize the session
if (!isset($_SESSION)) session_start();

const _DEFVAR = 1;

// Include config file
require_once "config.php";
checkLongIn();
checkOTP();

// Define variables and initialize with empty values
$name = $address = $salary = "";
$name_err = $address_err = $salary_err = "";
$error = "";

// Processing form data when form is submitted
if (!empty($_POST["id"])) {

    // Get hidden input value
    $id = $_POST["id"];

    // Validate name
    $input_name = trim($_POST["name"]);
    if (empty($input_name)) {
        $name_err = "Please enter a name.";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $input_name)) {
        $name_err = "Please enter a valid name.";
    } else {
        $name = $input_name;
    }

    // Validate address
    $input_address = trim($_POST["address"]);
    if (empty($input_address)) {
        $address_err = "Please enter an address.";
    } else {
        $address = $input_address;
    }

    // Validate salary
    $input_salary = trim($_POST["salary"]);
    if (empty($input_salary)) {
        $salary_err = "Please enter the salary amount.";
    } elseif (!ctype_digit($input_salary)) {
        $salary_err = "Please enter a positive integer value.";
    } else {
        $salary = $input_salary;
    }

    // Check input errors before updating the database
    if (empty($name_err) && empty($address_err) && empty($salary_err)) {
        // Prepare an update statement
        $sql = "UPDATE employees SET name = :name, address = :address, salary = :salary WHERE id = :id";

        if ($stmt = $link->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindValue(':name', $name, PDO::PARAM_STR);
            $stmt->bindValue(':address', $address, PDO::PARAM_STR);
            $stmt->bindValue(':salary', $salary, PDO::PARAM_INT);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Records updated successfully. Redirect to landing page
                header("location: index.php");
                exit();
            } else {
                $error = "algo deu errado";
            }
        }
    }

} else {
    // trata-se de acesso à página

    // Get URL parameter
    $id = isset($_GET["id"]) ? (int)$_GET["id"] : null;


    // Check existence of id parameter before processing further
    if (!empty($id)) {

        // Prepare a select statement
        $sql = "SELECT * FROM employees WHERE id = :id";

        $stmt = $link->prepare($sql);
        // Bind parameter
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);


        if ($stmt->execute()) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            // Attempt to execute the prepared statement
            $row = $stmt->fetch();

            if ($row) {
                // Retrieve individual field values
                $name = $row["name"];
                $address = $row["address"];
                $salary = $row["salary"];
            } else {
                // URL doesn't contain a valid id. Redirect to error page
                $error = "registro não encontrado";
            }
        } else {
            $error = "registro não encontrado";
        }

    } else {
        $error = "registro não encontrado";
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
                <h2 class="mt-5">Update Record</h2>
                <?php if (!empty($error)) { ?>
                    <div class="alert alert-warning"><em><?php echo $error ?></em></div>
                    <p><a href="index.php" class="btn btn-primary">Voltar</a></p>
                <?php } else { ?>
                    <p>Please edit the input values and submit to update the employee record.</p>
                    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name"
                                   class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>"
                                   value="<?php echo $name; ?>">
                            <span class="invalid-feedback"><?php echo $name_err; ?></span>
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="address"
                                      class="form-control <?php echo (!empty($address_err)) ? 'is-invalid' : ''; ?>"><?php echo $address; ?></textarea>
                            <span class="invalid-feedback"><?php echo $address_err; ?></span>
                        </div>
                        <div class="form-group">
                            <label>Salary</label>
                            <input type="text" name="salary"
                                   class="form-control <?php echo (!empty($salary_err)) ? 'is-invalid' : ''; ?>"
                                   value="<?php echo $salary; ?>">
                            <span class="invalid-feedback"><?php echo $salary_err; ?></span>
                        </div>
                        <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="index.php" class="btn btn-secondary ml-2">Cancel</a>
                    </form>
                <?php } ?>
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