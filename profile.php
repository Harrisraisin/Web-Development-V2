<?php
/**
 * User Profile Page
 */

require_once 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get user ID from URL or use current user
$profile_user_id = isset($_GET['id']) ? intval($_GET['id']) : $user_id;

// Get user information
$user_query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $profile_user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header('Location: dashboard.php');
    exit();
}

// Check if viewing own profile
$is_own_profile = ($profile_user_id == $user_id);

// Check friendship status
$is_friend = false;
if (!$is_own_profile) {
    $friend_check = "SELECT * FROM friends WHERE 
                     (user_id = ? AND friend_id = ?) OR 
                     (user_id = ? AND friend_id = ?)";
    $stmt = $conn->prepare($friend_check);
    $stmt->bind_param("iiii", $user_id, $profile_user_id, $profile_user_id, $user_id);
    $stmt->execute();
    $is_friend = $stmt->get_result()->num_rows > 0;
}

// Get user's posts
$posts_query = "SELECT p.*, 
                (SELECT COUNT(*) FROM likes WHERE post_id = p.post_id) as like_count,
                (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id) as comment_count
                FROM posts p
                WHERE p.user_id = ?
                ORDER BY p.created_at DESC";
$stmt = $conn->prepare($posts_query);
$stmt->bind_param("i", $profile_user_id);
$stmt->execute();
$posts = $stmt->get_result();

// Get friend count
$friend_count_query = "SELECT COUNT(*) as count FROM friends WHERE user_id = ? OR friend_id = ?";
$stmt = $conn->prepare($friend_count_query);
$stmt->bind_param("ii", $profile_user_id, $profile_user_id);
$stmt->execute();
$friend_count = $stmt->get_result()->fetch_assoc()['count'];

// Get post count
$post_count = $posts->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['full_name']); ?> - Profile</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="dashboard.php" class="nav-brand">Social Media</a>
            <div class="nav-menu">
                <a href="dashboard.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
                <a href="messages.php" class="nav-link"><i class="fas fa-envelope"></i> Messages</a>
                <a href="profile.php" class="nav-link active"><i class="fas fa-user"></i> Profile</a>
                <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="profile-header">
            <img src="images/<?php echo $user['profile_pic'] ?? 'default.jpg'; ?>" alt="Profile" class="profile-header-pic">
            <div class="profile-header-info">
                <h1><?php echo htmlspecialchars($user['full_name']); ?></h1>
                <p>@<?php echo htmlspecialchars($user['username']); ?></p>
                <?php if ($user['bio']): ?>
                    <p class="bio"><?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
                <?php endif; ?>
                
                <div class="profile-stats">
                    <div class="stat">
                        <strong><?php echo $post_count; ?></strong>
                        <span>Posts</span>
                    </div>
                    <div class="stat">
                        <strong><?php echo $friend_count; ?></strong>
                        <span>Friends</span>
                    </div>
                </div>
                
                <?php if (!$is_own_profile): ?>
                    <div class="profile-actions">
                        <?php if ($is_friend): ?>
                            <button class="btn btn-secondary" onclick="removeFriend(<?php echo $profile_user_id; ?>)">
                                <i class="fas fa-user-minus"></i> Remove Friend
                            </button>
                        <?php else: ?>
                            <button class="btn btn-primary" onclick="addFriend(<?php echo $profile_user_id; ?>)">
                                <i class="fas fa-user-plus"></i> Add Friend
                            </button>
                        <?php endif; ?>
                        
                        <a href="messages.php?user=<?php echo $profile_user_id; ?>" class="btn btn-secondary">
                            <i class="fas fa-envelope"></i> Message
                        </a>
                        
                        <button class="btn btn-secondary" onclick="blockUser(<?php echo $profile_user_id; ?>)">
                            <i class="fas fa-ban"></i> Block
                        </button>
                    </div>
                <?php else: ?>
                    <a href="settings.php" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Profile
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="profile-posts">
            <h2>Posts</h2>
            <?php if ($post_count > 0): ?>
                <?php while ($post = $posts->fetch_assoc()): ?>
                    <div class="post" data-post-id="<?php echo $post['post_id']; ?>">
                        <div class="post-header">
                            <img src="images/<?php echo $user['profile_pic']; ?>" alt="Profile" class="post-profile-pic">
                            <div class="post-info">
                                <h4><?php echo htmlspecialchars($user['full_name']); ?></h4>
                                <p class="post-time"><?php echo date('d M Y H:i', strtotime($post['created_at'])); ?></p>
                            </div>
                        </div>
                        
                        <div class="post-content">
                            <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                            
                            <?php if ($post['image']): ?>
                                <img src="uploads/<?php echo $post['image']; ?>" alt="Post image" class="post-image">
                            <?php endif; ?>
                            
                            <?php if ($post['location_name']): ?>
                                <div class="post-location">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($post['location_name']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="post-stats">
                            <span class="like-count"><?php echo $post['like_count']; ?> likes</span>
                            <span><?php echo $post['comment_count']; ?> comments</span>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-posts">No posts yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        function addFriend(userId) {
            fetch('ajax/add_friend.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ friend_id: userId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error adding friend');
                }
            });
        }

        function removeFriend(userId) {
            if (!confirm('Are you sure you want to remove this friend?')) return;
            
            fetch('ajax/remove_friend.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ friend_id: userId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error removing friend');
                }
            });
        }
    </script>
</body>
</html>