<?php
/**
 * Admin Reports Management
 */

require_once '../includes/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

$conn = getDBConnection();

// Handle report actions
if (isset($_POST['action']) && isset($_POST['report_id'])) {
    $report_id = intval($_POST['report_id']);
    $action = $_POST['action'];
    
    switch ($action) {
        case 'resolve':
            $stmt = $conn->prepare("UPDATE reports SET status = 'resolved', resolved_at = NOW() WHERE report_id = ?");
            $stmt->bind_param("i", $report_id);
            $stmt->execute();
            break;
            
        case 'dismiss':
            $stmt = $conn->prepare("UPDATE reports SET status = 'dismissed', resolved_at = NOW() WHERE report_id = ?");
            $stmt->bind_param("i", $report_id);
            $stmt->execute();
            break;
            
        case 'ban_user':
            // Get reported user ID
            $stmt = $conn->prepare("SELECT reported_user_id FROM reports WHERE report_id = ?");
            $stmt->bind_param("i", $report_id);
            $stmt->execute();
            $reported_user_id = $stmt->get_result()->fetch_assoc()['reported_user_id'];
            
            // Ban the user
            $stmt = $conn->prepare("UPDATE users SET status = 'banned' WHERE user_id = ?");
            $stmt->bind_param("i", $reported_user_id);
            $stmt->execute();
            
            // Mark report as resolved
            $stmt = $conn->prepare("UPDATE reports SET status = 'resolved', resolved_at = NOW() WHERE report_id = ?");
            $stmt->bind_param("i", $report_id);
            $stmt->execute();
            break;
    }
}

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'pending';

// Get reports
$reports_query = "
    SELECT r.*, 
           reporter.username as reporter_username, reporter.full_name as reporter_name,
           reported.username as reported_username, reported.full_name as reported_name,
           reported.status as reported_user_status
    FROM reports r
    JOIN users reporter ON r.reporter_id = reporter.user_id
    JOIN users reported ON r.reported_user_id = reported.user_id
";

if ($filter != 'all') {
    $reports_query .= " WHERE r.status = ?";
}

$reports_query .= " ORDER BY r.created_at DESC";

if ($filter != 'all') {
    $stmt = $conn->prepare($reports_query);
    $stmt->bind_param("s", $filter);
} else {
    $stmt = $conn->prepare($reports_query);
}

$stmt->execute();
$reports = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .filter-tabs {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            gap: 10px;
        }
        
        .filter-tab {
            padding: 10px 20px;
            background: #f7fafc;
            border-radius: 10px;
            text-decoration: none;
            color: #4a5568;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .filter-tab.active {
            background: #667eea;
            color: white;
        }
        
        .reports-list {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .report-item {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
        }
        
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        
        .report-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-resolved {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-dismissed {
            background: #e0e7ff;
            color: #3730a3;
        }
        
        .report-content {
            margin-bottom: 15px;
        }
        
        .report-parties {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .party-info {
            padding: 15px;
            background: #f7fafc;
            border-radius: 10px;
        }
        
        .party-info h4 {
            color: #4a5568;
            margin-bottom: 10px;
        }
        
        .report-actions {
            display: flex;
            gap: 10px;
        }
        
        .report-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .btn-resolve {
            background: #48bb78;
            color: white;
        }
        
        .btn-dismiss {
            background: #718096;
            color: white;
        }
        
        .btn-ban {
            background: #e53e3e;
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
        <h1>User Reports</h1>
        
        <div class="admin-container">
            <!-- Sidebar -->
            <div class="admin-sidebar">
                <div class="admin-menu">
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="users.php">
                        <i class="fas fa-users"></i> Manage Users
                    </a>
                    <a href="reports.php" class="active">
                        <i class="fas fa-flag"></i> Reports
                    </a>
                    <a href="statistics.php">
                        <i class="fas fa-chart-bar"></i> Statistics
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="admin-content">
                <!-- Filter Tabs -->
                <div class="filter-tabs">
                    <a href="?filter=pending" class="filter-tab <?php echo $filter == 'pending' ? 'active' : ''; ?>">
                        Pending
                    </a>
                    <a href="?filter=resolved" class="filter-tab <?php echo $filter == 'resolved' ? 'active' : ''; ?>">
                        Resolved
                    </a>
                    <a href="?filter=dismissed" class="filter-tab <?php echo $filter == 'dismissed' ? 'active' : ''; ?>">
                        Dismissed
                    </a>
                    <a href="?filter=all" class="filter-tab <?php echo $filter == 'all' ? 'active' : ''; ?>">
                        All Reports
                    </a>
                </div>
                
                <!-- Reports List -->
                <div class="reports-list">
                    <?php if ($reports->num_rows > 0): ?>
                        <?php while ($report = $reports->fetch_assoc()): ?>
                            <div class="report-item">
                                <div class="report-header">
                                    <div>
                                        <h3>Report #<?php echo $report['report_id']; ?></h3>
                                        <p style="color: #718096; font-size: 14px;">
                                            <?php echo date('d M Y H:i', strtotime($report['created_at'])); ?>
                                        </p>
                                    </div>
                                    <span class="report-status status-<?php echo $report['status']; ?>">
                                        <?php echo ucfirst($report['status']); ?>
                                    </span>
                                </div>
                                
                                <div class="report-parties">
                                    <div class="party-info">
                                        <h4>Reporter</h4>
                                        <p><strong><?php echo htmlspecialchars($report['reporter_name']); ?></strong></p>
                                        <p>@<?php echo htmlspecialchars($report['reporter_username']); ?></p>
                                    </div>
                                    
                                    <div class="party-info">
                                        <h4>Reported User</h4>
                                        <p><strong><?php echo htmlspecialchars($report['reported_name']); ?></strong></p>
                                        <p>@<?php echo htmlspecialchars($report['reported_username']); ?></p>
                                        <p style="margin-top: 5px;">
                                            Status: 
                                            <span class="user-status status-<?php echo $report['reported_user_status']; ?>">
                                                <?php echo ucfirst($report['reported_user_status']); ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="report-content">
                                    <h4>Reason for Report:</h4>
                                    <p style="margin-top: 10px; padding: 15px; background: #f7fafc; border-radius: 10px;">
                                        <?php echo nl2br(htmlspecialchars($report['reason'])); ?>
                                    </p>
                                </div>
                                
                                <?php if ($report['status'] == 'pending'): ?>
                                    <div class="report-actions">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                            <button type="submit" name="action" value="resolve" class="report-btn btn-resolve">
                                                <i class="fas fa-check"></i> Mark Resolved
                                            </button>
                                        </form>
                                        
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                            <button type="submit" name="action" value="dismiss" class="report-btn btn-dismiss">
                                                <i class="fas fa-times"></i> Dismiss
                                            </button>
                                        </form>
                                        
                                        <?php if ($report['reported_user_status'] != 'banned'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                                <button type="submit" name="action" value="ban_user" class="report-btn btn-ban"
                                                        onclick="return confirm('Are you sure you want to ban this user?')">
                                                    <i class="fas fa-ban"></i> Ban User
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <a href="../profile.php?id=<?php echo $report['reported_user_id']; ?>" 
                                           target="_blank" class="report-btn" 
                                           style="background: #667eea; color: white; text-decoration: none; display: inline-block;">
                                            <i class="fas fa-eye"></i> View Profile
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <p style="color: #718096; font-style: italic;">
                                        Resolved on <?php echo date('d M Y H:i', strtotime($report['resolved_at'])); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #718096; padding: 40px;">
                            No reports found.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html><?php
/**
 * Admin Reports Management
 */

require_once '../includes/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

$conn = getDBConnection();

// Handle report actions
if (isset($_POST['action']) && isset($_POST['report_id'])) {
    $report_id = intval($_POST['report_id']);
    $action = $_POST['action'];
    
    switch ($action) {
        case 'resolve':
            $stmt = $conn->prepare("UPDATE reports SET status = 'resolved', resolved_at = NOW() WHERE report_id = ?");
            $stmt->bind_param("i", $report_id);
            $stmt->execute();
            break;
            
        case 'dismiss':
            $stmt = $conn->prepare("UPDATE reports SET status = 'dismissed', resolved_at = NOW() WHERE report_id = ?");
            $stmt->bind_param("i", $report_id);
            $stmt->execute();
            break;
            
        case 'ban_user':
            // Get reported user ID
            $stmt = $conn->prepare("SELECT reported_user_id FROM reports WHERE report_id = ?");
            $stmt->bind_param("i", $report_id);
            $stmt->execute();
            $reported_user_id = $stmt->get_result()->fetch_assoc()['reported_user_id'];
            
            // Ban the user
            $stmt = $conn->prepare("UPDATE users SET status = 'banned' WHERE user_id = ?");
            $stmt->bind_param("i", $reported_user_id);
            $stmt->execute();
            
            // Mark report as resolved
            $stmt = $conn->prepare("UPDATE reports SET status = 'resolved', resolved_at = NOW() WHERE report_id = ?");
            $stmt->bind_param("i", $report_id);
            $stmt->execute();
            break;
    }
}

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'pending';

// Get reports
$reports_query = "
    SELECT r.*, 
           reporter.username as reporter_username, reporter.full_name as reporter_name,
           reported.username as reported_username, reported.full_name as reported_name,
           reported.status as reported_user_status
    FROM reports r
    JOIN users reporter ON r.reporter_id = reporter.user_id
    JOIN users reported ON r.reported_user_id = reported.user_id
";

if ($filter != 'all') {
    $reports_query .= " WHERE r.status = ?";
}

$reports_query .= " ORDER BY r.created_at DESC";

if ($filter != 'all') {
    $stmt = $conn->prepare($reports_query);
    $stmt->bind_param("s", $filter);
} else {
    $stmt = $conn->prepare($reports_query);
}

$stmt->execute();
$reports = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .filter-tabs {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            gap: 10px;
        }
        
        .filter-tab {
            padding: 10px 20px;
            background: #f7fafc;
            border-radius: 10px;
            text-decoration: none;
            color: #4a5568;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .filter-tab.active {
            background: #667eea;
            color: white;
        }
        
        .reports-list {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .report-item {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
        }
        
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        
        .report-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-resolved {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-dismissed {
            background: #e0e7ff;
            color: #3730a3;
        }
        
        .report-content {
            margin-bottom: 15px;
        }
        
        .report-parties {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .party-info {
            padding: 15px;
            background: #f7fafc;
            border-radius: 10px;
        }
        
        .party-info h4 {
            color: #4a5568;
            margin-bottom: 10px;
        }
        
        .report-actions {
            display: flex;
            gap: 10px;
        }
        
        .report-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .btn-resolve {
            background: #48bb78;
            color: white;
        }
        
        .btn-dismiss {
            background: #718096;
            color: white;
        }
        
        .btn-ban {
            background: #e53e3e;
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
        <h1>User Reports</h1>
        
        <div class="admin-container">
            <!-- Sidebar -->
            <div class="admin-sidebar">
                <div class="admin-menu">
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="users.php">
                        <i class="fas fa-users"></i> Manage Users
                    </a>
                    <a href="reports.php" class="active">
                        <i class="fas fa-flag"></i> Reports
                    </a>
                    <a href="statistics.php">
                        <i class="fas fa-chart-bar"></i> Statistics
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="admin-content">
                <!-- Filter Tabs -->
                <div class="filter-tabs">
                    <a href="?filter=pending" class="filter-tab <?php echo $filter == 'pending' ? 'active' : ''; ?>">
                        Pending
                    </a>
                    <a href="?filter=resolved" class="filter-tab <?php echo $filter == 'resolved' ? 'active' : ''; ?>">
                        Resolved
                    </a>
                    <a href="?filter=dismissed" class="filter-tab <?php echo $filter == 'dismissed' ? 'active' : ''; ?>">
                        Dismissed
                    </a>
                    <a href="?filter=all" class="filter-tab <?php echo $filter == 'all' ? 'active' : ''; ?>">
                        All Reports
                    </a>
                </div>
                
                <!-- Reports List -->
                <div class="reports-list">
                    <?php if ($reports->num_rows > 0): ?>
                        <?php while ($report = $reports->fetch_assoc()): ?>
                            <div class="report-item">
                                <div class="report-header">
                                    <div>
                                        <h3>Report #<?php echo $report['report_id']; ?></h3>
                                        <p style="color: #718096; font-size: 14px;">
                                            <?php echo date('d M Y H:i', strtotime($report['created_at'])); ?>
                                        </p>
                                    </div>
                                    <span class="report-status status-<?php echo $report['status']; ?>">
                                        <?php echo ucfirst($report['status']); ?>
                                    </span>
                                </div>
                                
                                <div class="report-parties">
                                    <div class="party-info">
                                        <h4>Reporter</h4>
                                        <p><strong><?php echo htmlspecialchars($report['reporter_name']); ?></strong></p>
                                        <p>@<?php echo htmlspecialchars($report['reporter_username']); ?></p>
                                    </div>
                                    
                                    <div class="party-info">
                                        <h4>Reported User</h4>
                                        <p><strong><?php echo htmlspecialchars($report['reported_name']); ?></strong></p>
                                        <p>@<?php echo htmlspecialchars($report['reported_username']); ?></p>
                                        <p style="margin-top: 5px;">
                                            Status: 
                                            <span class="user-status status-<?php echo $report['reported_user_status']; ?>">
                                                <?php echo ucfirst($report['reported_user_status']); ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="report-content">
                                    <h4>Reason for Report:</h4>
                                    <p style="margin-top: 10px; padding: 15px; background: #f7fafc; border-radius: 10px;">
                                        <?php echo nl2br(htmlspecialchars($report['reason'])); ?>
                                    </p>
                                </div>
                                
                                <?php if ($report['status'] == 'pending'): ?>
                                    <div class="report-actions">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                            <button type="submit" name="action" value="resolve" class="report-btn btn-resolve">
                                                <i class="fas fa-check"></i> Mark Resolved
                                            </button>
                                        </form>
                                        
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                            <button type="submit" name="action" value="dismiss" class="report-btn btn-dismiss">
                                                <i class="fas fa-times"></i> Dismiss
                                            </button>
                                        </form>
                                        
                                        <?php if ($report['reported_user_status'] != 'banned'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                                <button type="submit" name="action" value="ban_user" class="report-btn btn-ban"
                                                        onclick="return confirm('Are you sure you want to ban this user?')">
                                                    <i class="fas fa-ban"></i> Ban User
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <a href="../profile.php?id=<?php echo $report['reported_user_id']; ?>" 
                                           target="_blank" class="report-btn" 
                                           style="background: #667eea; color: white; text-decoration: none; display: inline-block;">
                                            <i class="fas fa-eye"></i> View Profile
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <p style="color: #718096; font-style: italic;">
                                        Resolved on <?php echo date('d M Y H:i', strtotime($report['resolved_at'])); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #718096; padding: 40px;">
                            No reports found.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>