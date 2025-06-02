<?php
session_start();
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

// Capture error message from session
$error = $_SESSION['error_message'] ?? '';
// Clear error message from session
unset($_SESSION['error_message']);
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SocialBook</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <div class="nav-left">
            <a href="index.php"><img src="images/logo2.png" class="logo"></a>
        </div>
    </nav>

    <div class="form-container">
        <h2>Login</h2>
        <?php if($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form action="handlers/login_handler.php" id="login-form" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="form-btn">Login</button>
        </form>
        <p style="margin-top: 15px;">Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>
