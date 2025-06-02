<?php
$host = "localhost:3306";
$dbname = "s4303980_social_media";
$username = "s4303980_dbuser";
$password = "91#tf08jH";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    error_log("Database connection successful.");
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later." . $e->getMessage());
}