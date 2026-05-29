<?php
require "db.php";
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    return;
}

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    echo json_encode(['success' => false, 'error' => 'ID inválido']);
    return;
}

$orderSql = "SELECT * FROM orders WHERE id = $id";
$order = mysqli_fetch_assoc(mysqli_query($conn, $orderSql));

if (!$order) {
    echo json_encode(['success' => false, 'error' => 'Orden no encontrada']);
    return;
}

$itemsSql = "SELECT oi.*, p.name 
             FROM order_items oi 
             JOIN products p ON oi.product_id = p.id 
             WHERE oi.order_id = $id";
$itemsResult = mysqli_query($conn, $itemsSql);
$items = [];
while ($item = mysqli_fetch_assoc($itemsResult)) {
    $items[] = $item;
}

echo json_encode(['success' => true, 'order' => $order, 'items' => $items]);
?>
