<?php
require_once 'config.php';
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Check if username or email exists
        $check_stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
        $check_stmt->execute([$_POST['email'], $_POST['username']]);
        
        if ($check_stmt->rowCount() > 0) {
            $existing_user = $check_stmt->fetch();
            if ($existing_user['email'] == $_POST['email']) {
                $error = "Email already exists!";
            } else {
                $error = "Username already taken!";
            }
        } else {
            // Insert new user
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, status, role) 
                                 VALUES (?, ?, ?, ?, 'active', 'user')");
            
            $stmt->execute([
                $_POST['username'],
                $_POST['email'],
                password_hash($_POST['password'], PASSWORD_DEFAULT),
                $_POST['full_name']
            ]);
            
            $success = "Registration successful! Please <a href='login.php'>login here</a>";
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $error = "Registration failed. Please try again later.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SocialBook</title>
    <link rel="stylesheet" href="style.css">
    <style>
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
        .success { color: green; }
    </style>
</head>
<body>
    <nav>
        <div class="nav-left">
            <a href="index.html"><img src="images/logo2.png" class="logo"></a>
        </div>
    </nav>

    <div class="form-container">
        <h2>Register</h2>
        <?php if($error) echo "<p class='error'>$error</p>"; ?>
        <?php if($success) echo "<p class='success'>$success</p>"; ?>
        
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required minlength="6">
            <button type="submit" class="form-btn">Register</button>
        </form>
        <p style="margin-top: 15px;">Already have an account? <a href="login.php">Login here</a></p>
    </div>

    <div class="footer">
        <p>Copyright 2021 - Easy Tutorials YouTube Channel</p>
    </div>
</body>
</html>
