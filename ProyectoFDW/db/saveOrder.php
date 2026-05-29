<?php
require "db.php";
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    return;
}

$mesa     = mysqli_real_escape_string($conn, $_POST['mesa'] ?? 'Sin mesa');
$metodo   = mysqli_real_escape_string($conn, $_POST['metodo'] ?? 'efectivo');
$total    = floatval($_POST['total'] ?? 0);
$items    = $_POST['items'] ?? '';
$username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');

// Insertar la orden principal
$sql = "INSERT INTO orders (mesa, metodo_pago, total, username, fecha) VALUES ('$mesa', '$metodo', $total, '$username', NOW())";

if (!mysqli_query($conn, $sql)) {
    echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
    return;
}

$orderId = mysqli_insert_id($conn);

// Insertar los items de la orden
if (!empty($items)) {
    $pairs = explode(',', $items); // formato: "id:qty,id:qty"
    foreach ($pairs as $pair) {
        [$productId, $qty] = explode(':', $pair);
        $productId = intval($productId);
        $qty = intval($qty);

        // Obtener precio actual del producto
        $pSql = "SELECT price FROM products WHERE id = $productId";
        $pRes = mysqli_query($conn, $pSql);
        $product = mysqli_fetch_assoc($pRes);
        if (!$product) continue;

        $price = $product['price'];
        $subtotal = $price * $qty;

        $iSql = "INSERT INTO order_items (order_id, product_id, qty, price, subtotal)
                 VALUES ($orderId, $productId, $qty, $price, $subtotal)";
        mysqli_query($conn, $iSql);
    }
}

echo json_encode(['success' => true, 'order_id' => $orderId]);
?>
