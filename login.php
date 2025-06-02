<?php
session_start();
require_once 'config.php';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$_POST['email']]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($_POST['password'], $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['profile_pic'] = $user['profile_pic'];
            
            // Update last login time
            $update_stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $update_stmt->execute([$user['user_id']]);
            
            header("Location: index.html");
            exit();
        } else {
            $error = "Invalid email or password!";
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $error = "Login failed. Please try again later.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SocialBook</title>
    <link rel="stylesheet" href="style.css">
    <style></style>
        .form-container {
            background: var(--bg-color);
            padding: 20px;
            border-radius: 6px;
            max-width: 400px;
            margin: 100px auto;
        }
        .form-container input {
            width: 100%;
            padding: 10px;
            margin: 5px 0 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-btn {
            background: #1876f2;
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        .error { color: red; }
    </style>
</head>
<body>
    <nav>
        <div class="nav-left">
            <a href="index.html"><img src="images/logo2.png" class="logo"></a>
        </div>
    </nav>

    <div class="form-container">
        <h2>Login</h2>
        <?php if($error) echo "<p class='error'>$error</p>"; ?>
        
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="form-btn">Login</button>
        </form>
        <p style="margin-top: 15px;">Don't have an account? <a href="register.php">Register here</a></p>
    </div>

    <div class="footer">
        <p>Copyright 2021 - Easy Tutorials YouTube Channel</p>
    </div>
</body>
</html>
