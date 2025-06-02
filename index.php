<?php
session_start(); // Start the session
include 'includes/config.php'; // Include database connection

$error = ""; // Initialize error variable

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and execute the SQL statement to fetch user data
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    // Check if the user exists
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($userId, $hashedPassword);
        $stmt->fetch();

        // Verify the password
        if (password_verify($password, $hashedPassword)) {
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $username;
            header("Location: dashboard.php"); // Redirect to dashboard
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Invalid username or password.";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>SocialSphere - Connect & Share</title>
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #6a11cb, #2575fc); /* Gradient background */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            text-align: center;
            max-width: 400px;
            width: 100%;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 40px;
            transition: transform 0.3s;
        }

        h1 {
            font-size: 28px;
            color: #007bff; /* Blue color for the main heading */
            margin-bottom: 20px;
        }

        p {
            font-size: 16px;
            margin-bottom: 30px;
            color: #555; /* Darker text for the description */
        }

        .auth-form {
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left; /* Align labels to the left */
            position: relative; /* Position relative for toggle password */
        }

        .form-group label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block; /* Make label a block element */
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

        .toggle-password {
            position: absolute;
            right: 15px; /* Adjust this value to position the icon */
            top: 50%; /* Center vertically */
            transform: translateY(-50%); /* Adjust for perfect centering */
            cursor: pointer;
            color: #007bff;
            font-size: 18px; /* Increase icon size for better visibility */
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
            margin-top: 10px; /* Add margin for spacing */
        }

        .btn:hover {
            background: #0056b3; /* Darker blue on hover */
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
        }

        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #777;
        }

        .footer p {
            margin: 10px 0;
        }

        .error-message {
            color: red;
            margin-top: 10px;
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }

            h1 {
                font-size: 24px;
            }

            p {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="welcome-wrapper">
            <h1>Connect & Share Your World</h1>
            <p>Join SocialSphere to connect with friends and the community around the globe.</p>
            <div class="auth-form">
                <h2>Sign In</h2>
                <form action="index.php" method="POST" aria-labelledby="login-form">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Username" required aria-label="Username">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div style="position: relative;">
                            <input type="password" id="password" name="password" placeholder="Password" required aria-label="Password">
                            <span class="toggle-password" onclick="togglePasswordVisibility()" aria-label="Toggle password visibility" role="button" tabindex="0" onkeypress="if(event.key === 'Enter') togglePasswordVisibility();">👁️</span>
                        </div>
                    </div>
                    <button type="submit" class="btn">Login</button>
                    <div class="error-message" id="error-message" aria-live="assertive"><?php echo $error; ?></div>
                </form>
                <p><a href="forgot_password.php">Forgot Password?</a></p>
                <p><a href="register.php">Sign Up</a></p>
            </div>
        </div>
        <footer class="footer">
            <p>&copy; 2025 SocialSphere. All rights reserved.</p>
        </footer>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-password');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.textContent = '🙈'; // Change icon to indicate visibility
            } else {
                passwordInput.type = 'password';
                toggleIcon.textContent = '👁️'; // Change icon back to original
            }
        }
    </script>
</body>
</html>