<?php
/**
 * AJAX handler for getting comments
 */

require_once '../includes/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

if (!$post_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit();
}

$conn = getDBConnection();

// Get comments
$comments_query = "
    SELECT c.*, u.username, u.full_name, u.profile_pic
    FROM comments c
    JOIN users u ON c.user_id = u.user_id
    WHERE c.post_id = ?
    ORDER BY c.created_at DESC
";

$stmt = $conn->prepare($comments_query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];
while ($comment = $result->fetch_assoc()) {
    $comment['created_at'] = date('d M Y H:i', strtotime($comment['created_at']));
    $comments[] = $comment;
}

echo json_encode([
    'success' => true,
    'comments' => $comments
]);
?>