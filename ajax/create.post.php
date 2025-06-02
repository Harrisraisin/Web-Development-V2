<?php
/**
 * AJAX handler for creating posts
 */

require_once '../includes/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get post data
$content = isset($_POST['content']) ? trim($_POST['content']) : '';
$location_name = isset($_POST['location_name']) ? trim($_POST['location_name']) : null;
$location_lat = isset($_POST['location_lat']) ? floatval($_POST['location_lat']) : null;
$location_lng = isset($_POST['location_lng']) ? floatval($_POST['location_lng']) : null;

if (empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Post content cannot be empty']);
    exit();
}

// Handle image upload
$image = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $_FILES['image']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (in_array($ext, $allowed)) {
        $new_filename = 'post_' . $user_id . '_' . time() . '.' . $ext;
        $upload_path = '../uploads/' . $new_filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            $image = $new_filename;
        } else {
            echo json_encode(['success' => false, 'message' => 'Error uploading image']);
            exit();
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid file type']);
        exit();
    }
}

// Insert post
$insert_query = "INSERT INTO posts (user_id, content, image, location_name, location_lat, location_lng) 
                 VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insert_query);
$stmt->bind_param("isssdd", $user_id, $content, $image, $location_name, $location_lat, $location_lng);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error creating post']);
}
?>