<?php
require "db.php";
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php?error=Acceso denegado");
    return;
}

if (!isset($_GET["id"])) {
    header("Location: ../adminProducts.php?error=Id invalido");
    return;
}

$id = intval($_GET["id"]);

// Obtener nombre de imagen para borrarla del disco
$sql = "SELECT image FROM products WHERE id = $id";
$result = mysqli_query($conn, $sql);
$product = mysqli_fetch_array($result);

if (!$product) {
    header("Location: ../adminProducts.php?error=Producto no encontrado");
    return;
}

$sql = "DELETE FROM products WHERE id = $id";

if (mysqli_query($conn, $sql)) {
    // Borrar imagen del servidor si existe
    $imagePath = "../images/" . $product['image'];
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
    header("Location: ../adminProducts.php?success=Producto eliminado");
} else {
    header("Location: ../adminProducts.php?error=" . mysqli_error($conn));
}
?>
