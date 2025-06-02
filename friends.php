<?php
/**
 * Friends Page - Manage friend list
 */

require_once 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get friends list
$friends_query = "
    SELECT u.*, f.created_at as friend_since
    FROM friends f
    JOIN users u ON (
        CASE 
            WHEN f.user_id = ? THEN f.friend_id = u.user_id
            ELSE f.user_id = u.user_id
        END
    )
    WHERE f.user_id = ? OR f.friend_id = ?
    ORDER BY f.created_at DESC
";

$stmt = $conn->prepare($friends_query);
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$friends = $stmt->get_result();

// Get friend suggestions (users who are not friends and not blocked)
$suggestions_query = "
    SELECT u.*
    FROM users u
    WHERE u.user_id != ?
    AND u.user_id NOT IN (
        SELECT CASE 
            WHEN f.user_id = ? THEN f.friend_id 
            ELSE f.user_id 
        END
        FROM friends f
        WHERE f.user_id = ? OR f.friend_id = ?
    )
    AND u.user_id NOT IN (
        SELECT blocked_user_id FROM blocks WHERE user_id = ?
    )
    AND u.user_id NOT IN (
        SELECT user_id FROM blocks WHERE blocked_user_id = ?
    )
    ORDER BY RAND()
    LIMIT 5
";

$stmt = $conn->prepare($suggestions_query);
$stmt->bind_param("iiiiii", $user_id, $user_id, $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$suggestions = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends - Social Media</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .friends-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .friend-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .friend-card:hover {
            transform: translateY(-5px);
        }
        
        .friend-card .profile-pic {
            width: 80px;
            height: 80px;
            margin-bottom: 15px;
        }
        
        .friend-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 15px;
        }
        
        .suggestions-section {
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="dashboard.php" class="nav-brand">Social Media</a>
            <div class="nav-menu">
                <a href="dashboard.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
                <a href="messages.php" class="nav-link"><i class="fas fa-envelope"></i> Messages</a>
                <a href="profile.php" class="nav-link"><i class="fas fa-user"></i> Profile</a>
                <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1>Friends</h1>
        
        <!-- Current Friends -->
        <div class="friends-section">
            <h2>Your Friends (<?php echo $friends->num_rows; ?>)</h2>
            
            <?php if ($friends->num_rows > 0): ?>
                <div class="friends-grid">
                    <?php while ($friend = $friends->fetch_assoc()): ?>
                        <div class="friend-card">
                            <img src="images/<?php echo $friend['profile_pic'] ?? 'default.jpg'; ?>" 
                                 alt="Profile" class="profile-pic">
                            <h3><?php echo htmlspecialchars($friend['full_name']); ?></h3>
                            <p style="color: #718096;">@<?php echo htmlspecialchars($friend['username']); ?></p>
                            <p style="font-size: 14px; color: #a0aec0;">
                                Friends since <?php echo date('d M Y', strtotime($friend['friend_since'])); ?>
                            </p>
                            
                            <div class="friend-actions">
                                <a href="profile.php?id=<?php echo $friend['user_id']; ?>" class="btn btn-secondary">
                                    <i class="fas fa-user"></i> Profile
                                </a>
                                <a href="messages.php?user=<?php echo $friend['user_id']; ?>" class="btn btn-secondary">
                                    <i class="fas fa-envelope"></i> Message
                                </a>
                                <button class="btn btn-secondary" onclick="removeFriend(<?php echo $friend['user_id']; ?>)">
                                    <i class="fas fa-user-minus"></i> Remove
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: #718096; margin: 40px 0;">
                    You don't have any friends yet. Check out the suggestions below!
                </p>
            <?php endif; ?>
        </div>
        
        <!-- Friend Suggestions -->
        <div class="suggestions-section">
            <h2>People You May Know</h2>
            
            <?php if ($suggestions->num_rows > 0): ?>
                <div class="friends-grid">
                    <?php while ($suggestion = $suggestions->fetch_assoc()): ?>
                        <div class="friend-card">
                            <img src="images/<?php echo $suggestion['profile_pic'] ?? 'default.jpg'; ?>" 
                                 alt="Profile" class="profile-pic">
                            <h3><?php echo htmlspecialchars($suggestion['full_name']); ?></h3>
                            <p style="color: #718096;">@<?php echo htmlspecialchars($suggestion['username']); ?></p>
                            
                            <div class="friend-actions">
                                <a href="profile.php?id=<?php echo $suggestion['user_id']; ?>" class="btn btn-secondary">
                                    <i class="fas fa-user"></i> Profile
                                </a>
                                <button class="btn btn-primary" onclick="addFriend(<?php echo $suggestion['user_id']; ?>)">
                                    <i class="fas fa-user-plus"></i> Add Friend
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: #718096; margin: 40px 0;">
                    No suggestions available at the moment.
                </p>
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