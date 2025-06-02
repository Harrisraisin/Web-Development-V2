<?php
/**
 * AJAX handler for adding friends
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

if (!$friend_id || $friend_id == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Invalid friend ID']);
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Check if already friends
$check_query = "SELECT * FROM friends WHERE 
                (user_id = ? AND friend_id = ?) OR 
                (user_id = ? AND friend_id = ?)";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Already friends']);
    exit();
}

// Check if blocked
$block_check = "SELECT * FROM blocks WHERE 
                (user_id = ? AND blocked_user_id = ?) OR 
                (user_id = ? AND blocked_user_id = ?)";
$stmt = $conn->prepare($block_check);
$stmt->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Cannot add this user as friend']);
    exit();
}

// Add friend
$insert_query = "INSERT INTO friends (user_id, friend_id) VALUES (?, ?)";
$stmt = $conn->prepare($insert_query);
$stmt->bind_param("ii", $user_id, $friend_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error adding friend']);
}
?>