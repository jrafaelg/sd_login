<?php
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
if (!isset($_SESSION)) session_start();

function connectToDatabase()
{

    //$dsn = ':memory:';
    //$pdo = new \PDO($dsn);

    $db_file = 'empregados.db';

    $dsn = "sqlite:$db_file";
    $link = new PDO($dsn);

    // Attempt to connect to SQLite database
    //$link = new SQLite3($db_file);

    // Create the employees table if it does not exist
    $ddl = "
    CREATE TABLE IF NOT EXISTS employees (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        address TEXT NOT NULL,
        salary INTEGER NOT NULL
    );";

    if (!$link->query($ddl)) {
        die("ERROR: Could not create table employees.");
    }

    // Create the backupcodes table if it does not exist
    $ddl = "
    CREATE TABLE IF NOT EXISTS backupcodes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        cod_user INTEGER NOT NULL,
        backup_code TEXT NOT NULL,
        used INTEGER DEFAULT 0 NOT NULL
    );";

    if (!$link->query($ddl)) {
        die("ERROR: Could not create table backupcodes.");
    }


    $sql = "SELECT count(*) as total FROM sqlite_master WHERE type='table' AND name='users';";
    $result = $link->query($sql);

    if (!$result) {
        die("ERROR: Could not check table users.");
    }

    $row = $result->fetch();

    if ($row['total'] != 1) {

        // Create the employees table if it does not exist
        $ddl = "
                CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT NOT NULL UNIQUE,
                    password TEXT NOT NULL,
                    otp_secret TEXT DEFAULT NULL,
                    otp_ts INTEGER DEFAULT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );";

        if (!$link->query($ddl)) {
            die("ERROR: Could not create table users.");
        }

        $sql = "INSERT INTO users (username, password, otp_secret) VALUES (:username, :password, :otp_secret)";

        if ($stmt = $link->prepare($sql)) {
            // Set parameters
            $param_username = 'jrafaelg';
            $param_password = '123';
            $param_otp_secret = 'NNQXZMEVASLTH26P';


            // Bind variables to the prepared statement as parameters
            $stmt->bindValue(":username", $param_username, PDO::PARAM_STR);
            $stmt->bindValue(":password", $param_password, PDO::PARAM_STR);
            $stmt->bindValue(":otp_secret", $param_otp_secret, PDO::PARAM_STR);

            // Attempt to execute the prepared statement
            if (!$stmt->execute()) {
                die("ERROR: Could not insert first user.");
            }
        }

        // Close statement
        unset($stmt);
    }

    return $link;
}

// Example usage (equivalent to $link in original code)
$link = connectToDatabase();
