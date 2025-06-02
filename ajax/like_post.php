<?php
/**
 * AJAX handler for liking/unliking posts
 */

require_once '../includes/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
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
$user_id = $_SESSION['user_id'];

// Check if already liked
$check_query = "SELECT * FROM likes WHERE user_id = ? AND post_id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("ii", $user_id, $post_id);
$stmt->execute();
$existing_like = $stmt->get_result()->fetch_assoc();

if ($existing_like) {
    // Unlike
    $delete_query = "DELETE FROM likes WHERE user_id = ? AND post_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("ii", $user_id, $post_id);
    $liked = false;
} else {
    // Like
    $insert_query = "INSERT INTO likes (user_id, post_id) VALUES (?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ii", $user_id, $post_id);
    $liked = true;
}

$success = $stmt->execute();

// Get new like count
$count_query = "SELECT COUNT(*) as count FROM likes WHERE post_id = ?";
$stmt = $conn->prepare($count_query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$like_count = $stmt->get_result()->fetch_assoc()['count'];

echo json_encode([
    'success' => $success,
    'liked' => $liked,
    'like_count' => $like_count
]);
?>