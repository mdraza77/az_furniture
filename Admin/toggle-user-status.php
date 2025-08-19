<?php
require_once 'authentication/check_admin_session.php';
require '../config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_id']) || !isset($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request parameters']);
    exit;
}

$user_id = intval($data['user_id']);
$status = $data['status'] ? 1 : 0;

$query = "UPDATE users SET is_active = ? WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $status, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}