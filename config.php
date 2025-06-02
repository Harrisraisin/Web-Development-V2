<?php
// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/db_connect.php';

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Function to redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Function to get base URL
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    return $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
}

// Upload directories
define('UPLOAD_BASE_DIR', __DIR__ . '/uploads/');
define('PROFILE_PICS_DIR', UPLOAD_BASE_DIR . 'profile_pics/');
define('POSTS_DIR', UPLOAD_BASE_DIR . 'posts/');
define('TEMP_DIR', UPLOAD_BASE_DIR . 'temp/');

// Create directories if they don't exist
$directories = [
    UPLOAD_BASE_DIR,
    PROFILE_PICS_DIR,
    POSTS_DIR,
    TEMP_DIR
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}
