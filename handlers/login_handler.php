<?php
session_start();
require_once '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = array('success' => false, 'message' => '');
    
    try {
        // Get email and password from POST
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        
        // Debug log
        error_log("Login attempt for email: " . $email);

        // Prepare and execute query
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'active') {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['profile_pic'] = $user['profile_pic'] ?? 'images/profile-pic.png';
                
                // Update last login
                $update_stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
                $update_stmt->execute([$user['user_id']]);
                
                $response['success'] = true;
                $response['redirect'] = '../index.php';
                
                error_log("Login successful for user: " . $user['username']);
            } else {
                $response['message'] = "Account is not active";
            }
        } else {
            $response['message'] = "Invalid email or password";
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        $response['message'] = "Login failed. Please try again later.";
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
