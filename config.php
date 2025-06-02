<?php
define('DB_SERVER', 'localhost:3306');
define('DB_USERNAME', 's4303980_dbuser');
define('DB_PASSWORD', 'password');
define('DB_NAME', 's4303980_social_media');

// Create connection
try {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Database connection error. Please try again later.");
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}
