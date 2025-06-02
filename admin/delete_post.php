<?php
/**
 * AJAX handler for admin to delete posts
 */

require_once '../includes/config.php';

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$post_id = isset($input['post_id']) ? intval($input['post_id']) : 0;

if (!$post_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit();
}

$conn = getDBConnection();

// Get post image to delete
$stmt = $conn->prepare("SELECT image FROM posts WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

// Check if the post exists
if (!$post) {
    echo json_encode(['success' => false, 'message' => 'Post not found']);
    exit();
}

// Delete post (cascade will handle likes and comments)
$stmt = $conn->prepare("DELETE FROM posts WHERE post_id = ?");
$stmt->bind_param("i", $post_id);

if ($stmt->execute()) {
    // Delete image file if exists
    if ($post['image'] && file_exists('../uploads/' . $post['image'])) {
        unlink('../uploads/' . $post['image']);
    }
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting post']);
}
?>