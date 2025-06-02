<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirect to login if not logged in
    exit();
}

// Fetch user information from the database if needed
include 'includes/config.php';

// Example: Fetch user details
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Social Media Home</title>
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f0f4f8; /* Light background color */
            display: flex;
            flex-direction: column; /* Change to column for layout */
            min-height: 100vh;
        }

        /* Navbar Styles */
        .navbar {
            background-color: #007bff; /* Blue background for the navbar */
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h1 {
            font-size: 24px;
        }

        /* User Info Styles */
        .user-info {
            display: flex;
            align-items: center;
        }

        .user-info span {
            margin-right: 15px;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background-color: #f8f9fa; /* Light gray background */
            padding: 20px;
            border-right: 1px solid #e2e8f0;
        }

        .sidebar h2 {
            font-size: 20px;
            margin-bottom: 15px;
        }

        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }

        .sidebar ul li {
            margin: 10px 0;
            cursor: pointer;
        }

        /* Main Content Styles */
        .main-content {
            flex-grow: 1;
            padding: 20px;
        }

        .main-content h2 {
            font-size: 22px;
            margin-bottom: 15px;
        }

        .main-content textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
            border: 2px solid #e2e8f0;
            border-radius: 5px;
            margin-bottom: 10px;
            resize: none; /* Prevent resizing */
        }

        .main-content button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #007bff; /* Button color */
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .main-content button:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }

        /* Feed Styles */
        .feed {
            margin-top: 20px;
        }

        .post {
            border: 1px solid #e2e8f0;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            background-color: #ffffff; /* White background for posts */
        }

        .post p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>SocialSphere</h1>
        <div class="user-info">
            <span><?php echo htmlspecialchars($username); ?></span>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    <div class="sidebar">
        <h2>Your Shortcuts</h2>
        <ul>
            <li>Latest News</li>
            <li>Friends</li>
            <li>Groups</li>
            <li>Marketplace</li>
            <li>Watch</li>
        </ul>
    </div>
    <div class="main-content">
        <h2>What's on your mind, <?php echo htmlspecialchars($username); ?>?</h2>
        <textarea placeholder="Write something..."></textarea>
        <button>Post</button>
        <div class="feed">
            <h3>Feed</h3>
            <div class="post">
                <p><strong><?php echo htmlspecialchars($username); ?></strong> - June 24, 2021</p>
                <p>Welcome to SocialSphere!</p>
            </div>
            <!-- More posts can be added here -->
        </div>
    </div>
</body>
</html>