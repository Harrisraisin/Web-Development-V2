<?php
session_start();
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SocialBook</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <nav>
        <div class="nav-left">
            <a href="index.html"><img src="images/logo2.png" class="logo"></a>
        </div>
    </nav>

    <div class="form-container">
        <h2>Login</h2>
        <div id="error-message" class="error" style="display: none;"></div>
        
        <form id="login-form" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="form-btn">Login</button>
        </form>
        <p style="margin-top: 15px;">Don't have an account? <a href="register.php">Register here</a></p>
    </div>

    <div class="footer">
        <p>Copyright 2021 - Easy Tutorials YouTube Channel</p>
    </div>

    <script>
    $(document).ready(function() {
        $('#login-form').on('submit', function(e) {
            e.preventDefault();
            $('#error-message').hide();
            
            $.ajax({
                type: 'POST',
                url: 'handlers/login_handler.php',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    console.log('Response:', response); // Debug log
                    if (response.success) {
                        window.location.href = response.redirect;
                    } else {
                        $('#error-message')
                            .text(response.message || 'Login failed')
                            .show();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Ajax error:', error); // Debug log
                    $('#error-message')
                        .text('An error occurred. Please try again.')
                        .show();
                }
            });
        });
    });
    </script>
</body>
</html>
