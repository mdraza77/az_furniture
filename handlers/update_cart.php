<?php
require_once '../config/database.php';
require_once '../authentication/check_session.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_data']['id']) || !isset($_POST['item_id']) || !isset($_POST['change'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$user_id = $_SESSION['user_data']['id'];
$item_id = filter_var($_POST['item_id'], FILTER_VALIDATE_INT);
$change = filter_var($_POST['change'], FILTER_VALIDATE_INT);

if ($item_id === false || $change === false) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

try {
    // पहले कार्ट आइटम की जानकारी प्राप्त करें
    $stmt = $conn->prepare("SELECT quantity, price, product_name FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $item_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();

    if ($item) {
        // नई मात्रा की गणना करें
        $new_quantity = max(1, $item['quantity'] + $change);
        $new_total = $new_quantity * $item['price'];

        // कार्ट अपडेट करें
        $update = $conn->prepare("UPDATE cart SET quantity = ?, total_price = ? WHERE id = ? AND user_id = ?");
        $update->bind_param("idii", $new_quantity, $new_total, $item_id, $user_id);

        if ($update->execute()) {
            // सफल अपडेट के बाद सभी आवश्यक जानकारी वापस भेजें
            echo json_encode([
                'success' => true,
                'quantity' => $new_quantity,
                'total' => $new_total,
                'product_name' => $item['product_name'],
                'price' => $item['price']
            ]);
        } else {
            throw new Exception('Database update error');
        }
        $update->close();
    } else {
        throw new Exception('Item not found');
    }
} catch (Exception $e) {
    error_log("Cart Update Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

if (isset($stmt)) {
    $stmt->close();
}
if (isset($conn)) {
    $conn->close();
}
exit;
