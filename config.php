<?php
function connectToDatabase()
{
    $db_file = 'empregados.db';

    // Attempt to connect to SQLite database
    $link = new SQLite3($db_file);

    // Create the employees table if it does not exist
    $ddl = "
    CREATE TABLE IF NOT EXISTS employees (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        address TEXT NOT NULL,
        salary INTEGER NOT NULL
    )";

    if (!$link->exec($ddl)) {
        die("ERROR: Could not create table employees.");
    }

    // Create the employees table if it does not exist
    $ddl = "
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT NOT NULL,
        hash_password TEXT NOT NULL
    )";

    if (!$link->exec($ddl)) {
        die("ERROR: Could not create table users.");
    }

    return $link;
}

// Example usage (equivalent to $link in original code)
$link = connectToDatabase();
?>
