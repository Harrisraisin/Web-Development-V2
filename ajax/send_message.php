<?php
/**
 * AJAX handler for sending messages
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
$receiver_id = isset($input['receiver_id']) ? intval($input['receiver_id']) : 0;
$message = isset($input['message']) ? trim($input['message']) : '';

if (!$receiver_id || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

$conn = getDBConnection();
$sender_id = $_SESSION['user_id'];

// Check if receiver is not blocked
$block_check = "SELECT * FROM blocks WHERE 
                (user_id = ? AND blocked_user_id = ?) OR 
                (user_id = ? AND blocked_user_id = ?)";
$stmt = $conn->prepare($block_check);
$stmt->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Cannot send message to this user']);
    exit();
}

// Insert message
$insert_query = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
$stmt = $conn->prepare($insert_query);
$stmt->bind_param("iis", $sender_id, $receiver_id, $message);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error sending message']);
}
?>