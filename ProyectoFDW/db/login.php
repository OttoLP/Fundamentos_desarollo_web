<?php
require "db.php";
session_start();

if (!isset($_POST["username"]) || !isset($_POST["password"])) {
    header("Location: ../login.php?error=Datos incompletos");
    return;
}

$username = $_POST["username"];
$password = $_POST["password"];

// Usar prepared statements para evitar SQL injection
$sql = "SELECT role FROM users WHERE username = ? AND password = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ss", $username, $password);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_array($result);

if ($user) {
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $user['role'];

    if ($user['role'] == 'admin') {
        header("Location: ../adminProducts.php");
    } else {
        header("Location: ../ventas.php");
    }
} else {
    header("Location: ../login.php?error=Usuario o contraseña incorrectos");
}
?>
