<?php
require "db.php";
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php?error=Acceso denegado");
    return;
}

if (!isset($_POST["id"]) || !isset($_POST["name"]) || !isset($_POST["price"])) {
    header("Location: ../adminProducts.php?error=Datos incompletos");
    return;
}

$id       = intval($_POST["id"]);
$name     = mysqli_real_escape_string($conn, $_POST["name"]);
$price    = floatval($_POST["price"]);
$category = mysqli_real_escape_string($conn, $_POST["category"] ?? 'Cervezas');

// Si se sube nueva imagen, procesarla; si no, mantener la actual
if (!empty($_FILES['image']['name'])) {
    $imageName = $_FILES['image']['name'];
    $type      = $_FILES['image']['type'];
    $tmpPath   = $_FILES['image']['tmp_name'];

    $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!in_array($type, $allowed)) {
        header("Location: ../editProduct.php?id=$id&error=Imagen invalida");
        return;
    }

    move_uploaded_file($tmpPath, "../images/$imageName");
    $sql = "UPDATE products SET name='$name', price=$price, category='$category', image='$imageName' WHERE id=$id";
} else {
    $sql = "UPDATE products SET name='$name', price=$price, category='$category' WHERE id=$id";
}

if (mysqli_query($conn, $sql)) {
    header("Location: ../adminProducts.php?success=Producto actualizado");
} else {
    header("Location: ../editProduct.php?id=$id&error=" . mysqli_error($conn));
}
?>
