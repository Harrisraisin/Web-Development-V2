<?php
/**
 * Logout script - Destroys session and redirects to index
 */

session_start();

// Destroy all session data
session_unset();
session_destroy();

// Delete remember me cookie if exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect to index page
header('Location: index.php');
exit();
?>