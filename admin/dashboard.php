<?php
/**
 * Admin Dashboard
 */

require_once '../includes/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

$conn = getDBConnection();

// Get statistics
$stats = [];

// Total users
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$stats['total_users'] = $result->fetch_assoc()['count'] ?? 0; // Default to 0 if null

// Total posts
$result = $conn->query("SELECT COUNT(*) as count FROM posts");
$stats['total_posts'] = $result->fetch_assoc()['count'] ?? 0; // Default to 0 if null

// Total messages
$result = $conn->query("SELECT COUNT(*) as count FROM messages");
$stats['total_messages'] = $result->fetch_assoc()['count'] ?? 0; // Default to 0 if null

// Pending reports
$result = $conn->query("SELECT COUNT(*) as count FROM reports WHERE status = 'pending'");
$stats['pending_reports'] = $result->fetch_assoc()['count'] ?? 0; // Default to 0 if null

// Recent activity
$recent_users = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
$recent_posts = $conn->query("SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.user_id ORDER BY p.created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Social Media</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container {
            display: flex;
            gap: 20px;
        }
        
        .admin-sidebar {
            width: 250px;
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .admin-content {
            flex: 1;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card i {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .stat-card h3 {
            font-size: 36px;
            color: #2d3748;
            margin-bottom: 5px;
        }
        
        .activity-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .admin-menu a {
            display: block;
            padding: 12px 20px;
            color: #4a5568;
            text-decoration: none;
            border-radius: 10px;
            margin-bottom: 5px;
            transition: all 0.3s;
        }
        
        .admin-menu a:hover,
        .admin-menu a.active {
            background: #f7fafc;
            color: #667eea;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="../dashboard.php" class="nav-brand">Social Media - Admin</a>
            <div class="nav-menu">
                <a href="../dashboard.php" class="nav-link"><i class="fas fa-home"></i> Main Site</a>
                <a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1>Admin Dashboard</h1>
        
        <div class="admin-container">
            <!-- Sidebar -->
            <div class="admin-sidebar">
                <div class="admin-menu">
                    <a href="dashboard.php" class="active" aria-label="Dashboard">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="users.php" aria-label="Manage Users">
                        <i class="fas fa-users"></i> Manage Users
                    </a>
                    <a href="reports.php" aria-label="Reports">
                        <i class="fas fa-flag"></i> Reports
                        <?php if ($stats['pending_reports'] > 0): ?>
                            <span style="background: #e53e3e; color: white; padding: 2px 8px; border-radius: 10px; float: right;">
                                <?php echo $stats['pending_reports']; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <a href="statistics.php" aria-label="Statistics">
                        <i class="fas fa-chart-bar"></i> Statistics
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="admin-content">
                <!-- Statistics -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <i class="fas fa-users"></i>
                        <h3><?php echo $stats['total_users']; ?></h3>
                        <p>Total Users</p>
                    </div>
                    
                    <div class="stat-card">
                        <i class="fas fa-file-alt"></i>
                        <h3><?php echo $stats['total_posts']; ?></h3>
                        <p>Total Posts</p>
                    </div>
                    
                    <div class="stat-card">
                        <i class="fas fa-envelope"></i>
                        <h3><?php echo $stats['total_messages']; ?></h3>
                        <p>Total Messages</p>
                    </div>
                    
                    <div class="stat-card">
                        <i class="fas fa-flag"></i>
                        <h3><?php echo $stats['pending_reports']; ?></h3>
                        <p>Pending Reports</p>
                    </div>
                </div>
                
                <!-- Recent Users -->
                <div class="activity-section">
                    <h2>Recent Users</h2>
                    <table style="width: 100%; margin-top: 15px;">
                        <thead>
                            <tr style="text-align: left;">
                                <th>Username</th>
                                <th>Email</th>
                                <th>Joined</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $recent_users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <a href="users.php?id=<?php echo $user['user_id']; ?>" style="color: #667eea;" aria-label="View User">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Recent Posts -->
                <div class="activity-section">
                    <h2>Recent Posts</h2>
                    <table style="width: 100%; margin-top: 15px;">
                        <thead>
                            <tr style="text-align: left;">
                                <th>User</th>
                                <th>Content</th>
                                <th>Posted</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($post = $recent_posts->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($post['username']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($post['content'], 0, 50)) . '...'; ?></td>
                                    <td><?php echo date('d M Y H:i', strtotime($post['created_at'])); ?></td>
                                    <td>
                                        <a href="#" onclick="deletePost(<?php echo $post['post_id']; ?>)" style="color: #e53e3e;" aria-label="Delete Post">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function deletePost(postId) {
            if (!confirm('Are you sure you want to delete this post?')) return;
            
            fetch('../ajax/admin_delete_post.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ post_id: postId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error deleting post');
                }
            })
            .catch(error => {
                alert('An error occurred: ' + error.message);
            });
        }
    </script>
</body>
</html>