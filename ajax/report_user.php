<?php
/**
 * AJAX handler for reporting users
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
$reported_user_id = isset($input['reported_user_id']) ? intval($input['reported_user_id']) : 0;
$reason = isset($input['reason']) ? trim($input['reason']) : '';

if (!$reported_user_id || empty($reason) || $reported_user_id == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

$conn = getDBConnection();
$reporter_id = $_SESSION['user_id'];

// Check if already reported
$check_query = "SELECT * FROM reports WHERE reporter_id = ? AND reported_user_id = ? AND status = 'pending'";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("ii", $reporter_id, $reported_user_id);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already reported this user']);
    exit();
}

// Insert report
$insert_query = "INSERT INTO reports (reporter_id, reported_user_id, reason) VALUES (?, ?, ?)";
$stmt = $conn->prepare($insert_query);
$stmt->bind_param("iis", $reporter_id, $reported_user_id, $reason);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error submitting report']);
}
?>