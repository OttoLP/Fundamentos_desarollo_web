<?php
require "./db/db.php";
session_start();

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $role = $_SESSION['role'];
} else {
    header("Location: ./login.php?error=Inicia sesión");
    return;
}

$sql = "SELECT * FROM products";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos</title>

    <link rel="stylesheet" href="styles.css">
    <style>
        html, body {
            min-height: 100vh;
            margin: 0;
        }

        body.admin-page {
            background: linear-gradient(135deg, #f7ecd7 0%, #ddc0a8 45%, #bba27c 100%) !important;
            color: #4b2f1b;
        }

        .admin-form {
            width: 520px;
            max-width: 100%;
            padding: 22px;
            border-radius: 16px;
            background: rgba(255, 248, 238, 0.96);
            box-shadow: 0 14px 28px rgba(84, 60, 40, 0.14);
            margin: 0 auto 24px auto;
        }

        .admin-page .title {
            text-align: center;
        }
    </style>
</head>

<body class="admin-page">

    <body class="admin-page">

    <nav style="background:#2c1a0e; padding:12px 24px; display:flex; gap:16px; align-items:center; margin-bottom:20px;">
        <span style="color:#f5c842; font-weight:700; margin-right:auto;">🧹 Las 3 Escobas</span>
        <a href="./adminProducts.php" style="color:#f5c842; text-decoration:none; font-size:14px;">Productos</a>
        <a href="./ventas.php"        style="color:#c9a97a; text-decoration:none; font-size:14px;">Terminal</a>
        <a href="./finanzas.php"      style="color:#c9a97a; text-decoration:none; font-size:14px;">Finanzas</a>
        <a href="./db/logout.php"     style="color:#c07070; text-decoration:none; font-size:14px;">Salir</a>
    </nav>

   <h1 class="title">Crear producto</h1>
    <form action="./db/insertProduct.php" method="POST" enctype="multipart/form-data" class="admin-form">
        <label for="name">Nombre</label>
        <input type="text" id="name" name="name">
    
        <label for="price">Price</label>
        <input type="number" id="price" name="price">

        <label for="image">Imagen</label>
        <input type="file" id="image" name="image">

        <button type="submit">Crear</button>

        <?php
        if (isset($_GET['error'])) {
            echo "<span class='error'> Error: " . $_GET['error'] . "</span>";
        }
    ?>
    </form>



    <h1 class="title">Administrar productos</h1>
    <div class="products-container">
        <?php
        while ($product = mysqli_fetch_array($result)) {
        ?>
            <div class="product-item">
                <img src="./images/<?php echo $product['image'] ?>" alt="Imagen del producto">
                <p class="product-title">
                    <?php echo $product['name'] ?>
                </p>
                <p class="product-price">
                    <?php echo "$" . number_format($product['price'], 2, ".") ?>
                </p>
                <button onclick="window.location.href='./editProduct.php?id=<?php echo $product['id'] ?>'">
                    Editar
                </button>
                <button onclick="window.location.href='./db/deleteProduct.php?id=<?php echo $product['id'] ?>'">
                    Eliminar
                </button>
            </div>
        <?php
        }
        ?>
    </div>
    <a href="./db/logout.php">Cerrar sesión</a>
</body>

</html>