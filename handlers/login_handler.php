<?php
require_once '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = array('success' => false, 'message' => '');
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$_POST['email']]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($_POST['password'], $user['password'])) {
            if ($user['status'] !== 'active') {
                $response['message'] = "Account is not active";
            } else {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['profile_pic'] = $user['profile_pic'];
                
                // Update last login
                $update_stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
                $update_stmt->execute([$user['user_id']]);
                
                $response['success'] = true;
                $response['redirect'] = 'index.php';
            }
        } else {
            $response['message'] = "Invalid email or password";
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $response['message'] = "Login failed. Please try again later.";
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
