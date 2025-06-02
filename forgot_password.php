<?php
session_start();
include('db.php'); // Include your database connection file

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    // Validate email
    if (empty($email)) {
        $message = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } else {
        // Check if the email exists in the database
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Generate a unique token for password reset
            $token = bin2hex(random_bytes(50));
            $stmt->close();

            // Store the token in the database with an expiration time
            $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))");
            $stmt->bind_param("ss", $email, $token);
            $stmt->execute();

            // Send the password reset email
            $resetLink = "http://yourdomain.com/reset_password.php?token=" . $token; // Change to your domain
            $subject = "Password Reset Request";
            $body = "Click the following link to reset your password: " . $resetLink;
            $headers = "From: no-reply@yourdomain.com"; // Change to your email

            if (mail($email, $subject, $body, $headers)) {
                $message = "A password reset link has been sent to your email address.";
            } else {
                $message = "Failed to send email. Please try again.";
            }
        } else {
            $message = "No account found with that email address.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Forgot Password - SocialSphere</title>
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f0f4f8; /* Light background color */
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            text-align: center;
            max-width: 400px;
            width: 100%;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }

        h1 {
            font-size: 28px;
            color: #007bff; /* Blue color for the main heading */
            margin-bottom: 20px;
        }

        p {
            font-size: 16px;
            margin-bottom: 20px;
            color: #555; /* Darker text for the description */
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 5px;
            font-size: 16px;
            transition: all 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #007bff; /* Focus color for input fields */
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.3);
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-block;
            width: 100%;
            background: #007bff; /* Button color */
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
        }

        .message {
            margin-top: 20px;
            color: #d9534f; /* Red color for error messages */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Forgot Password</h1>
        <p>Please enter your email address to receive a password reset link.</p>
        <form action="forgot_password.php" method="POST">
            <div class="form-group">
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            <button type="submit" class="btn">Send Reset Link</button>
        </form>
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
    </div>
</body>
</html>