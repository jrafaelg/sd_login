<?php

if (!isset($_SESSION)) session_start();

// definindo variável para impedir acesso direto ao arquivo config.php
const _DEFVAR = 1;

// Include config file
require_once "config.php";
checkLongIn();
checkOTP();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

    <style>
        .wrapper {
            width: 600px;
            margin: 0 auto;
        }

        table tr td:last-child {
            width: 120px;
        }
    </style>
    <script>
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10">
            <div class="card-body p-5 p-md-4 p-xl-12">

                <div class="text-center mb-3">
                    <h2 class="fw-normal text-center  mb-4">Employees Details</h2>

                </div>
                <div class="row">
                    <div class="d-flex justify-content-center">
                        <a href="create.php" class="btn btn-success pull-right ">
                            <i class="fa fa-plus"></i> Add New Employee
                        </a>
                    </div>

                </div>

                <div class="row justify-content-center mt-4">
                    <div class="col-12 col-sm-10">


                        <?php


                        // Attempt select query execution
                        $sql = "SELECT * FROM employees";
                        $result = $link->query($sql);

                        if ($result) {

                            $rows = $result->fetchAll();

                            if (count($rows) > 0) { // Check if there are results
                                echo '<table class="table table-bordered table-striped">';
                                echo "<thead>";
                                echo "<tr>";
                                echo "<th>#</th>";
                                echo "<th>Name</th>";
                                echo "<th>Address</th>";
                                echo "<th>Salary</th>";
                                echo "<th>Action</th>";
                                echo "</tr>";
                                echo "</thead>";
                                echo "<tbody>";

                                foreach ($rows as $row) {

                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['address']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['salary']) . "</td>";
                                    echo "<td>";
                                    echo '<a href="read.php?id=' . $row['id'] . '" class="me-2" title="View Record" data-toggle="tooltip"><span class="fa fa-eye"></span></a>';
                                    echo '<a href="update.php?id=' . $row['id'] . '" class="me-2" title="Update Record" data-toggle="tooltip"><span class="fa fa-pencil"></span></a>';
                                    echo '<a href="delete.php?id=' . $row['id'] . '" class="me-2" title="Delete Record" data-toggle="tooltip"><span class="fa fa-trash"></span></a>';
                                    echo "</td>";
                                    echo "</tr>";
                                }
                                echo "</tbody>";
                                echo "</table>";
                            } else {
                                echo '<div class="alert alert-info"><em>No records were found.</em></div>';
                            }
                        } else {
                            echo "ERROR: Could not execute query: $sql. " . var_dump($link->errorInfo());
                        }
                        // destruindo as variáveis do bando de dados
                        disconnectDataBase();
                        ?>

                    </div>
                </div>
                <div class="row justify-content-center mt-4">
                    <div class="col12 col-md-10">
                        <div class="clearfix">
                            <p class="pull-right"><a href="logout.php">Logout</a></p>
                        </div>
                    </div>
                </div>
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