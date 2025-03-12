<?php
if (!isset($_SESSION)) session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Check existence of id parameter before processing further
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    // Include config file
    require_once "config.php";

    // Prepare a select statement
    $sql = "SELECT * FROM employees WHERE id = :id";

    if ($stmt = $link->prepare($sql)) {
        // Bind parameter
        $stmt->bindValue(':id', trim($_GET["id"]), SQLITE3_INTEGER);

        // Attempt to execute the prepared statement
        $result = $stmt->execute();

        if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            // Retrieve individual field values
            $name = $row["name"];
            $address = $row["address"];
            $salary = $row["salary"];
        } else {
            // URL doesn't contain a valid id parameter. Redirect to error page
            header("location: error.php");
            exit();
        }
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }

    // Close statement
    $stmt->close();

    // Close connection (optional, SQLite auto-closes on script termination)
    $link->close();
} else {
    // URL doesn't contain id parameter. Redirect to error page
    header("location: error.php");
    exit();
}
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
                <h1 class="mt-5 mb-3">View Record</h1>
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
                <p><a href="index.php" class="btn btn-primary">Back</a></p>
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