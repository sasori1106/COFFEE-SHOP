<?php
session_start();
include "db.php";

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id']);

// Get order items to restore stock
$items = $conn->query("SELECT * FROM order_items WHERE order_id=$id");
while($item = $items->fetch_assoc()) {
    // Restore stock
    $conn->query("UPDATE products SET stock_quantity = stock_quantity + {$item['quantity']} WHERE id = {$item['product_id']}");
    
    // Log inventory
    $order_num = $conn->query("SELECT order_number FROM orders WHERE id=$id")->fetch_assoc()['order_number'];
    $conn->query("INSERT INTO inventory_logs (product_id, transaction_type, quantity_change, notes, created_by) 
                 VALUES ({$item['product_id']}, 'adjustment', {$item['quantity']}, 'Order cancelled: $order_num', '{$_SESSION['username']}')");
}

// Cancel order
$conn->query("UPDATE orders SET order_status='cancelled' WHERE id=$id");

header("Location: orders.php");
?>