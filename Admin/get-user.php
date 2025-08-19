<?php
require_once 'authentication/check_admin_session.php';
require '../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'User ID not provided']);
    exit;
}

$user_id = intval($_GET['id']);

$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode(['error' => 'User not found']);
    exit;
}

// Remove sensitive information
unset($user['password_hash']);
unset($user['reset_password_token']);
unset($user['reset_password_expires']);

echo json_encode($user);