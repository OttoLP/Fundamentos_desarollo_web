<?php
session_start();

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $role = $_SESSION['role'];
} else {
    header("Location: ./login.php?error=Inicia sesión");
    return;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
</head>

<body>
    <h1>Bienvenido <?php echo $username ?></h1>
    <h2>Tu rol es <?php echo $role ?></h2>

    <?php if ($role == 'admin') { ?>
        <a href="./adminProducts.php">Administrar productos</a><br>
    <?php } ?>

    <a href="./db/logout.php">Cerrar sesión</a>
</body>

</html>