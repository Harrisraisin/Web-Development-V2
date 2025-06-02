<?php
/**
 * Blocked Users Page - Manage blocked users
 */

require_once 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get blocked users
$blocked_query = "
    SELECT u.*, b.created_at as blocked_date
    FROM blocks b
    JOIN users u ON b.blocked_user_id = u.user_id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
";

$stmt = $conn->prepare($blocked_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$blocked_users = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blocked Users - Social Media</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .blocked-list {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        
        .blocked-user {
            display: flex;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .blocked-user:last-child {
            border-bottom: none;
        }
        
        .blocked-info {
            flex: 1;
            margin-left: 15px;
        }
        
        .unblock-btn {
            background: #e53e3e;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .unblock-btn:hover {
            background: #c53030;
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
        <h1>Blocked Users</h1>
        
        <div class="blocked-list">
            <?php if ($blocked_users->num_rows > 0): ?>
                <?php while ($user = $blocked_users->fetch_assoc()): ?>
                    <div class="blocked-user">
                        <img src="images/<?php echo $user['profile_pic'] ?? 'default.jpg'; ?>" 
                             alt="Profile" class="post-profile-pic">
                        <div class="blocked-info">
                            <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
                            <p style="color: #718096;">
                                @<?php echo htmlspecialchars($user['username']); ?> • 
                                Blocked on <?php echo date('d M Y', strtotime($user['blocked_date'])); ?>
                            </p>
                        </div>
                        <button class="unblock-btn" onclick="unblockUser(<?php echo $user['user_id']; ?>)">
                            Unblock
                        </button>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; color: #718096; padding: 40px;">
                    You haven't blocked any users.
                </p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function unblockUser(userId) {
            if (!confirm('Are you sure you want to unblock this user?')) return;
            
            fetch('ajax/unblock_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ user_id: userId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error unblocking user');
                }
            });
        }
    </script>
</body>
</html>