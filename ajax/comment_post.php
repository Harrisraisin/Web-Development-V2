<?php
/**
 * AJAX handler for posting comments
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
$comment = isset($input['comment']) ? trim($input['comment']) : '';

if (!$post_id || empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Insert comment
$insert_query = "INSERT INTO comments (user_id, post_id, comment) VALUES (?, ?, ?)";
$stmt = $conn->prepare($insert_query);
$stmt->bind_param("iis", $user_id, $post_id, $comment);

if ($stmt->execute()) {
    // Get comment count
    $count_query = "SELECT COUNT(*) as count FROM comments WHERE post_id = ?";
    $stmt = $conn->prepare($count_query);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $comment_count = $stmt->get_result()->fetch_assoc()['count'];
    
    echo json_encode([
        'success' => true,
        'comment_count' => $comment_count
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error posting comment']);
}
?>