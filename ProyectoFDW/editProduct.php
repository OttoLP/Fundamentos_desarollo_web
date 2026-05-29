<?php
require "./db/db.php";
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ./login.php?error=Inicia sesión como administrador");
    return;
}

$username = $_SESSION['username'];
$sql = "SELECT * FROM products ORDER BY category, name";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar productos – Las 3 Escobas</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="admin-header">
        <h1>🧹 Las 3 Escobas — Panel Admin</h1>
        <nav>
            <a href="./ventas.php">Terminal de ventas</a>
            <a href="./finanzas.php">Finanzas</a>
            <a href="./db/logout.php">Cerrar sesión</a>
        </nav>
    </header>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert-success"><?php echo htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert-error">Error: <?php echo htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <section class="form-section">
        <h2 class="title">Crear producto</h2>
        <form action="./db/insertProduct.php" method="POST" enctype="multipart/form-data">
            <label for="name">Nombre</label>
            <input type="text" id="name" name="name" required>

            <label for="price">Precio ($)</label>
            <input type="number" id="price" name="price" step="0.01" min="0" required>

            <label for="category">Categoría</label>
            <select id="category" name="category">
                <option value="Cervezas">Cervezas</option>
                <option value="Coctelería">Coctelería</option>
                <option value="Snacks">Snacks</option>
                <option value="Bebidas">Bebidas</option>
            </select>

            <label for="image">Imagen (jpg/png/gif)</label>
            <input type="file" id="image" name="image" accept="image/*" required>

            <button type="submit">Crear producto</button>
        </form>
    </section>

    <section>
        <h2 class="title">Productos del menú</h2>
        <div class="products-container">
            <?php while ($product = mysqli_fetch_array($result)): ?>
                <div class="product-item">
                    <img src="./images/<?php echo htmlspecialchars($product['image']) ?>" alt="<?php echo htmlspecialchars($product['name']) ?>">
                    <span class="product-category"><?php echo htmlspecialchars($product['category'] ?? '') ?></span>
                    <p class="product-title"><?php echo htmlspecialchars($product['name']) ?></p>
                    <p class="product-price">$<?php echo number_format($product['price'], 2) ?></p>
                    <div class="product-actions">
                        <button onclick="window.location.href='./editProduct.php?id=<?php echo $product['id'] ?>'">Editar</button>
                        <button class="btn-danger" onclick="confirmDelete(<?php echo $product['id'] ?>, '<?php echo htmlspecialchars($product['name']) ?>')">Eliminar</button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <script>
        function confirmDelete(id, name) {
            if (confirm('¿Eliminar "' + name + '"? Esta acción no se puede deshacer.')) {
                window.location.href = './db/deleteProduct.php?id=' + id;
            }
        }
    </script>
</body>
</html>
