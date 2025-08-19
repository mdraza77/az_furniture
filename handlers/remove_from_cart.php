<?php
require_once '../config/database.php';
require_once '../authentication/check_session.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_data']['id']) || !isset($_POST['item_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$user_id = $_SESSION['user_data']['id'];
$item_id = filter_var($_POST['item_id'], FILTER_VALIDATE_INT);

if ($item_id === false) {
    echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $item_id, $user_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Item not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error removing item']);
}

$stmt->close();
exit;
