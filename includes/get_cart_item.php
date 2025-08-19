<?php
require_once '../config/database.php';

if(isset($_GET['item_id'])) {
    $item_id = (int)$_GET['item_id'];
    
    $stmt = $conn->prepare("SELECT price, product_name FROM cart WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($row = $result->fetch_assoc()) {
        echo json_encode($row);
    }
}
?>