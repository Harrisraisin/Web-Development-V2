<?php
$servername = "localhost:3306";
$username = "s4303980_dbuser"; // Updated to match hosting username
$password = "password"; // Replace with actual database password
$dbname = "s4303980_dbuser";

try {
    // Create connection with charset
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8mb4");

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    // Log error and show user-friendly message
    error_log($e->getMessage());
    die("Sorry, there was a problem connecting to the database. Error: " . $e->getMessage());
}
?>