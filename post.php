<?php
// Initialize error reporting and session
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// Load configurations and check authentication 
require_once 'config.php';
requireLogin();

// Constants for image handling
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Validate post_id
$post_id = filter_input(INPUT_GET, 'post_id', FILTER_VALIDATE_INT);
if (!$post_id) {
    $_SESSION['error'] = "Invalid post ID";
    header('Location: index.php');
    exit();
}

try {
    // Get post data with error handling
    $stmt = $pdo->prepare("
        SELECT p.*, u.username, u.display_name, u.profile_pic,
               COUNT(DISTINCT l.id) as likes_count,
               COUNT(DISTINCT c.id) as comments_count,
               EXISTS(SELECT 1 FROM likes WHERE user_id = ? AND post_id = p.id) as user_liked
        FROM posts p
        JOIN users u ON p.user_id = u.user_id
        LEFT JOIN likes l ON p.id = l.post_id 
        LEFT JOIN comments c ON p.id = c.post_id
        WHERE p.id = ?
        GROUP BY p.id
    ");
    
    $stmt->execute([$_SESSION['user_id'], $post_id]);
    $post = $stmt->fetch();

    if (!$post) {
        throw new Exception("Post not found");
    }

    // Security check for post access permissions
    if ($post['privacy'] === 'private' && $post['user_id'] !== $_SESSION['user_id']) {
        throw new Exception("You don't have permission to view this post");
    }

    // Format post data
    $post['created_at_formatted'] = date('F j, Y g:i a', strtotime($post['created_at']));
    $post['is_owner'] = ($post['user_id'] === $_SESSION['user_id']);
    
    // Get post media
    $media_stmt = $pdo->prepare("SELECT * FROM post_media WHERE post_id = ?");
    $media_stmt->execute([$post_id]);
    $post['media'] = $media_stmt->fetchAll();

} catch (Exception $e) {
    error_log("Post error: " . $e->getMessage());
    $_SESSION['error'] = "Error loading post";
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Post - <?php echo htmlspecialchars($post['username']); ?></title>
    <link rel="stylesheet" href="style.css">
    <script src="https://kit.fontawesome.com/c4254e24a8.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <div class="container post-view">
        <!-- Post Display -->
        <div class="post-container">
            <div class="post-header">
                <div class="user-profile">
                    <img src="<?php echo htmlspecialchars($post['profile_pic']); ?>" alt="Profile">
                    <div>
                        <p class="username"><?php echo htmlspecialchars($post['username']); ?></p>
                        <span class="time"><?php echo $post['created_at_formatted']; ?></span>
                    </div>
                </div>
                <?php if ($post['is_owner']): ?>
                <div class="post-actions">
                    <button onclick="deletePost(<?php echo $post['id']; ?>)">Delete</button>
                </div>
                <?php endif; ?>
            </div>

            <!-- Post Content -->
            <div class="post-content">
                <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                <?php foreach ($post['media'] as $media): ?>
                    <img src="<?php echo htmlspecialchars($media['file_path']); ?>" alt="Post image">
                <?php endforeach; ?>
            </div>

            <!-- Post Stats -->
            <div class="post-stats">
                <span><?php echo $post['likes_count']; ?> likes</span>
                <span><?php echo $post['comments_count']; ?> comments</span>
            </div>
        </div>
    </div>

    <?php include 'partials/footer.php'; ?>
    <script src="script.js"></script>
</body>
</html>
