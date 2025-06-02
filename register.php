<?php
require_once 'config.php';
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);

        // Check if username or email already exists
        $check_existing = "SELECT * FROM users WHERE email='$email' OR username='$username'";
        $result = $conn->query($check_existing);
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($user['email'] == $email) {
                $error = "Email already exists!";
            } else {
                $error = "Username already taken!";
            }
        } else {
            $sql = "INSERT INTO users (username, email, password, full_name, status, role) 
                    VALUES ('$username', '$email', '$password', '$full_name', 'active', 'user')";
            
            if ($conn->query($sql) === TRUE) {
                $success = "Registration successful! Please login.";
            } else {
                throw new Exception($conn->error);
            }
        }
    } catch (Exception $e) {
        $error = "Registration failed. Please try again later.";
        error_log($e->getMessage());
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
