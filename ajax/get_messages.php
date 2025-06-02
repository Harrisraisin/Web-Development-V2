<?php
/**
 * AJAX handler for getting messages (for real-time updates)
 */

require_once '../includes/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$other_user_id = isset($_GET['user']) ? intval($_GET['user']) : 0;

if (!$other_user_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get messages
$messages_query = "
    SELECT m.*, 
           s.username as sender_username, s.profile_pic as sender_pic,
           r.username as receiver_username, r.profile_pic as receiver_pic
    FROM messages m
    JOIN users s ON m.sender_id = s.user_id
    JOIN users r ON m.receiver_id = r.user_id
    WHERE (m.sender_id = ? AND m.receiver_id = ?) 
       OR (m.sender_id = ? AND m.receiver_id = ?)
    ORDER BY m.created_at ASC
";

$stmt = $conn->prepare($messages_query);
$stmt->bind_param("iiii", $user_id, $other_user_id, $other_user_id, $user_id);
$stmt->execute();
$messages = $stmt->get_result();

// Mark messages as read
$update_query = "UPDATE messages SET is_read = 1 
                WHERE sender_id = ? AND receiver_id = ? AND is_read = 0";
$stmt = $conn->prepare($update_query);
$stmt->bind_param("ii", $other_user_id, $user_id);
$stmt->execute();

// Build HTML
$html = '';
while ($msg = $messages->fetch_assoc()) {
    $is_sent = $msg['sender_id'] == $user_id;
    $html .= '<div class="message ' . ($is_sent ? 'sent' : 'received') . '">';
    $html .= '<div class="message-content">';
    $html .= nl2br(htmlspecialchars($msg['message']));
    $html .= '<div style="font-size: 12px; opacity: 0.7; margin-top: 5px;">';
    $html .= date('H:i', strtotime($msg['created_at']));
    $html .= '</div></div></div>';
}

echo json_encode([
    'success' => true,
    'html' => $html
]);
?>