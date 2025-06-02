<?php
/**
 * AJAX handler for blocking users
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
$blocked_user_id = isset($input['blocked_user_id']) ? intval($input['blocked_user_id']) : 0;

if (!$blocked_user_id || $blocked_user_id == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Check if already blocked
$check_query = "SELECT * FROM blocks WHERE user_id = ? AND blocked_user_id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("ii", $user_id, $blocked_user_id);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'User already blocked']);
    exit();
}

// Insert block
$insert_query = "INSERT INTO blocks (user_id, blocked_user_id) VALUES (?, ?)";
$stmt = $conn->prepare($insert_query);
$stmt->bind_param("ii", $user_id, $blocked_user_id);

if ($stmt->execute()) {
    // Remove friendship if exists
    $remove_friend = "DELETE FROM friends WHERE 
                      (user_id = ? AND friend_id = ?) OR 
                      (user_id = ? AND friend_id = ?)";
    $stmt = $conn->prepare($remove_friend);
    $stmt->bind_param("iiii", $user_id, $blocked_user_id, $blocked_user_id, $user_id);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error blocking user']);
}
?>