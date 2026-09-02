<?php
header("Content-Type: application/json");
require_once "db.php";

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid Data"]);
    exit;
}

$client_name  = $data['client_name'];
$client_email = $data['client_email'];
$client_phone = $data['client_phone'];
$cart_items   = $data['cart_items'];
$total_amount = $data['total_amount'];

// 1. save client details
$stmt = $conn->prepare("INSERT INTO orders (client_name, client_email, client_phone, total_amount) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sssd", $client_name, $client_email, $client_phone, $total_amount);

if ($stmt->execute()) {
    $order_id = $conn->insert_id;

    // 2. save order items
    $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_name, price, quantity) VALUES (?, ?, ?, ?)");
    foreach ($cart_items as $item) {
        $p_name = $item['name'];
        $p_price = $item['price'];
        $p_qty = $item['quantity'];
        
        $item_stmt->bind_param("isdi", $order_id, $p_name, $p_price, $p_qty);
        $item_stmt->execute();
    }
    
    echo json_encode(["status" => "success", "message" => "Order Saved Successfully!", "order_id" => $order_id]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to save order"]);
}

$conn->close();
?>