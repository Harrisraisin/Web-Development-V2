<?php
session_start(); // Start the session
include 'includes/config.php'; // Include database connection
include 'includes/functions.php'; // Include common functions

if (!isLoggedIn()) {
    redirect('index.php'); // Redirect to login if not logged in
}

// Fetch user posts
$stmt = $conn->prepare("SELECT * FROM posts WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Dashboard - SocialSphere</title>
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo $_SESSION['username']; ?></h1>
        <a href="post.php">Create a Post</a>
        <h2>Your Posts</h2>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="post">
                <p><?php echo $row['content']; ?></p>
                <p><small><?php echo $row['created_at']; ?></small></p>
            </div>
        <?php endwhile; ?>
        <a href="logout.php">Logout</a>
    </div>
</body>
</html>