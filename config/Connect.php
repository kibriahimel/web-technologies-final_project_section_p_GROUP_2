<?php
// ============================================================
// config/Connect.php
// This file connects to the MySQL database using mysqli
// ============================================================

function connect() {
    $host     = "localhost";
    $username = "root";
    $password = "";           // XAMPP default has no password
    $database = "library_db";

    $conn = mysqli_connect($host, $username, $password, $database);

    if (!$conn) {
        die("Database connection failed: " . mysqli_connect_error());
    }

    return $conn;
}
