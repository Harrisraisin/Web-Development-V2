<?php
/**
 * Admin Users Management
 */

require_once '../includes/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

$conn = getDBConnection();

// Handle user actions
if (isset($_POST['action'])) {
    $user_id = intval($_POST['user_id']);
    
    switch ($_POST['action']) {
        case 'ban':
            $stmt = $conn->prepare("UPDATE users SET status = 'banned' WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            break;
            
        case 'unban':
            $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            break;
            
        case 'delete':
            // Delete user and all related data
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            break;
    }
}

// Get users with pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clause = "";
if ($search) {
    $search_param = "%$search%";
    $where_clause = " WHERE username LIKE ? OR email LIKE ? OR full_name LIKE ?";
}

// Get total users count
$count_query = "SELECT COUNT(*) as total FROM users" . $where_clause;
if ($search) {
    $stmt = $conn->prepare($count_query);
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
    $stmt->execute();
    $total_users = $stmt->get_result()->fetch_assoc()['total'];
} else {
    $total_users = $conn->query($count_query)->fetch_assoc()['total'];
}

$total_pages = ceil($total_users / $per_page);

// Get users
$users_query = "SELECT u.*, 
                (SELECT COUNT(*) FROM posts WHERE user_id = u.user_id) as post_count,
                (SELECT COUNT(*) FROM friends WHERE user_id = u.user_id OR friend_id = u.user_id) as friend_count
                FROM users u" . $where_clause . " 
                ORDER BY u.created_at DESC 
                LIMIT ? OFFSET ?";

if ($search) {
    $stmt = $conn->prepare($users_query);
    $stmt->bind_param("sssii", $search_param, $search_param, $search_param, $per_page, $offset);
} else {
    $stmt = $conn->prepare($users_query);
    $stmt->bind_param("ii", $per_page, $offset);
}
$stmt->execute();
$users = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .search-bar {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .search-form {
            display: flex;
            gap: 10px;
        }
        
        .search-input {
            flex: 1;
            padding: 10px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 16px;
        }
        
        .users-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .users-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .users-table th {
            background: #f7fafc;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #4a5568;
        }
        
        .users-table td {
            padding: 15px;
            border-top: 1px solid #e2e8f0;
        }
        
        .user-status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .status-active {
            background: #c6f6d5;
            color: #22543d;
        }
        
        .status-banned {
            background: #fed7d7;
            color: #742a2a;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-ban {
            background: #e53e3e;
            color: white;
        }
        
        .btn-unban {
            background: #48bb78;
            color: white;
        }
        
        .btn-delete {
            background: #718096;
            color: white;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }
        
        .page-link {
            padding: 10px 15px;
            background: white;
            border-radius: 10px;
            text-decoration: none;
            color: #4a5568;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .page-link.active {
            background: #667eea;
            color: white;
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
        <h1>Manage Users</h1>
        
        <div class="admin-container">
            <!-- Sidebar -->
            <div class="admin-sidebar">
                <div class="admin-menu">
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="users.php" class="active">
                        <i class="fas fa-users"></i> Manage Users
                    </a>
                    <a href="reports.php">
                        <i class="fas fa-flag"></i> Reports
                    </a>
                    <a href="statistics.php">
                        <i class="fas fa-chart-bar"></i> Statistics
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="admin-content">
                <!-- Search Bar -->
                <div class="search-bar">
                    <form class="search-form" method="GET">
                        <input type="text" name="search" class="search-input" 
                               placeholder="Search by username, email, or name..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <?php if ($search): ?>
                            <a href="users.php" class="btn btn-secondary">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <!-- Users Table -->
                <div class="users-table">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Full Name</th>
                                <th>Posts</th>
                                <th>Friends</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $user['user_id']; ?></td>
                                    <td>
                                        <a href="../profile.php?id=<?php echo $user['user_id']; ?>" target="_blank" style="color: #667eea;">
                                            <?php echo htmlspecialchars($user['username']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo $user['post_count']; ?></td>
                                    <td><?php echo $user['friend_count']; ?></td>
                                    <td>
                                        <span class="user-status status-<?php echo $user['status']; ?>">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                            <div class="action-buttons">
                                                <?php if ($user['status'] == 'active'): ?>
                                                    <button type="submit" name="action" value="ban" class="action-btn btn-ban">
                                                        Ban
                                                    </button>
                                                <?php else: ?>
                                                    <button type="submit" name="action" value="unban" class="action-btn btn-unban">
                                                        Unban
                                                    </button>
                                                <?php endif; ?>
                                                <button type="submit" name="action" value="delete" class="action-btn btn-delete"
                                                        onclick="return confirm('Are you sure you want to delete this user?')">
                                                    Delete
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>" class="page-link">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
                               class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>" class="page-link">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>