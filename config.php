<?php
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
if ( !isset( $_SESSION ) ) session_start();

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
    );";

    if (!$link->exec($ddl)) {
        die("ERROR: Could not create table employees.");
    }

    // Create the employees table if it does not exist
    $ddl = "
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );";

    if (!$link->exec($ddl)) {
        die("ERROR: Could not create table users.");
    }

    return $link;
}

// Example usage (equivalent to $link in original code)
$link = connectToDatabase();
