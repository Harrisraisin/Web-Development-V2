<?php
$servername = "localhost";
$username = "root"; // Change this to your actual username
$password = ""; // Change this to your actual password
$dbname = "social_media"; // Change this to your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>