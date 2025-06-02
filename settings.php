<?php
/**
 * Settings Page - User profile settings
 */

require_once 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Get current user data
$user_query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $bio = trim($_POST['bio']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Update basic info
    $update_query = "UPDATE users SET full_name = ?, email = ?, bio = ? WHERE user_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssi", $full_name, $email, $bio, $user_id);
    
    if ($stmt->execute()) {
        $message = "Profile updated successfully!";
        $_SESSION['user_name'] = $full_name;
        
        // Handle password change
        if (!empty($current_password) && !empty($new_password)) {
            if ($new_password === $confirm_password) {
                if (password_verify($current_password, $user['password'])) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $pwd_query = "UPDATE users SET password = ? WHERE user_id = ?";
                    $stmt = $conn->prepare($pwd_query);
                    $stmt->bind_param("si", $hashed_password, $user_id);
                    $stmt->execute();
                    $message .= " Password changed successfully!";
                } else {
                    $error = "Current password is incorrect.";
                }
            } else {
                $error = "New passwords do not match.";
            }
        }
        
        // Handle profile picture upload
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['profile_pic']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
                $upload_path = 'images/' . $new_filename;
                
                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_path)) {
                    // Delete old profile pic if not default
                    if ($user['profile_pic'] != 'default.jpg' && file_exists('images/' . $user['profile_pic'])) {
                        unlink('images/' . $user['profile_pic']);
                    }
                    
                    // Update database
                    $pic_query = "UPDATE users SET profile_pic = ? WHERE user_id = ?";
                    $stmt = $conn->prepare($pic_query);
                    $stmt->bind_param("si", $new_filename, $user_id);
                    $stmt->execute();
                    
                    $message .= " Profile picture updated!";
                }
            } else {
                $error = "Invalid file type. Please upload an image.";
            }
        }
        
        // Refresh user data
        $stmt = $conn->prepare($user_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
    } else {
        $error = "Error updating profile.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Social Media</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .settings-container {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .settings-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .profile-pic-upload {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .current-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
            border: 5px solid #667eea;
        }
        
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }
        
        .file-input-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }
        
        .file-input-label {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .file-input-label:hover {
            background: #5a67d8;
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
        <div class="settings-container">
            <h1>Settings</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <!-- Profile Picture Section -->
                <div class="settings-section">
                    <h2>Profile Picture</h2>
                    <div class="profile-pic-upload">
                        <img src="images/<?php echo $user['profile_pic'] ?? 'default.jpg'; ?>" 
                             alt="Profile" class="current-pic">
                        <div class="file-input-wrapper">
                            <label for="profile_pic" class="file-input-label">
                                <i class="fas fa-camera"></i> Change Photo
                            </label>
                            <input type="file" id="profile_pic" name="profile_pic" accept="image/*">
                        </div>
                    </div>
                </div>
                
                <!-- Basic Information Section -->
                <div class="settings-section">
                    <h2>Basic Information</h2>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" 
                               value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="bio">Bio</label>
                        <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <!-- Password Section -->
                <div class="settings-section">
                    <h2>Change Password</h2>
                    <p style="color: #718096; margin-bottom: 20px;">
                        Leave blank if you don't want to change your password
                    </p>
                    
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password">
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        // Preview profile picture before upload
        document.getElementById('profile_pic').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('.current-pic').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>