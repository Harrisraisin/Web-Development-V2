<?php
// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to redirect to a specific page
function redirect($url) {
    header("Location: $url");
    exit();
}
?>