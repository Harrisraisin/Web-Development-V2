<?php
/**
 * AJAX handler for removing friends
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
$friend_id = isset($input['friend_id']) ? intval($input['friend_id']) : 0;

if (!$friend_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid friend ID']);
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Remove friendship
$delete_query = "DELETE FROM friends WHERE 
                 (user_id = ? AND friend_id = ?) OR 
                 (user_id = ? AND friend_id = ?)";
$stmt = $conn->prepare($delete_query);
$stmt->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error removing friend']);
}
?>