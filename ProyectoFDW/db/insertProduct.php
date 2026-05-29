<?php
require "db.php";
session_start();

if (!isset($_POST["name"]) || !isset($_POST["price"]) || empty($_FILES['image']['name'])) {
    header("Location: ../adminProducts.php?error=Datos incompletos");
    return;
}

$name     = mysqli_real_escape_string($conn, $_POST["name"]);
$price    = floatval($_POST["price"]);
$category = mysqli_real_escape_string($conn, $_POST["category"] ?? 'Cervezas');

$imageName = $_FILES['image']['name'];
$type      = $_FILES['image']['type'];
$tmpPath   = $_FILES['image']['tmp_name'];

// Bug corregido: in_array es más claro y seguro que strpos()
$allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
if (!in_array($type, $allowed)) {
    header("Location: ../adminProducts.php?error=Imagen invalida, usa jpg/png/gif");
    return;
}

$sql = "INSERT INTO products (name, price, category, image) VALUES ('$name', $price, '$category', '$imageName')";

if (mysqli_query($conn, $sql)) {
    move_uploaded_file($tmpPath, "../images/$imageName");
    header("Location: ../adminProducts.php?success=Producto creado");
} else {
    header("Location: ../adminProducts.php?error=" . mysqli_error($conn));
}
?>
