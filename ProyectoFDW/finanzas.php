<?php
require "./db/db.php";
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ./login.php?error=Solo administradores");
    return;
}

$fecha = $_GET['fecha'] ?? date('Y-m-d');

// Resumen del día
$sqlResumen = "SELECT 
    COUNT(*) as total_ordenes,
    SUM(total) as ingresos,
    AVG(total) as promedio,
    SUM(CASE WHEN metodo_pago='efectivo' THEN total ELSE 0 END) as efectivo,
    SUM(CASE WHEN metodo_pago='tarjeta' THEN total ELSE 0 END) as tarjeta,
    SUM(CASE WHEN metodo_pago='transferencia' THEN total ELSE 0 END) as transferencia
    FROM orders WHERE DATE(fecha) = '$fecha'";
$resumen = mysqli_fetch_assoc(mysqli_query($conn, $sqlResumen));

// Listado de órdenes del día
$sqlOrdenes = "SELECT * FROM orders WHERE DATE(fecha) = '$fecha' ORDER BY fecha DESC";
$ordenes = mysqli_query($conn, $sqlOrdenes);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finanzas – Las 3 Escobas</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="ventas.css">
</head>
<body style="background:#f5f0e8; overflow:auto">

    <header class="admin-header">
        <h1>🧹 Las 3 Escobas — Finanzas</h1>
        <nav>
            <a href="./adminProducts.php">Productos</a>
            <a href="./ventas.php">Terminal</a>
            <a href="./db/logout.php">Salir</a>
        </nav>
    </header>

    <!-- Selector de fecha -->
    <div style="padding: 16px 24px; display:flex; align-items:center; gap:12px;">
        <label style="font-weight:600; color:#5a4a3a;">Fecha:</label>
        <input type="date" value="<?php echo $fecha ?>" onchange="window.location.href='?fecha='+this.value"
            style="padding:8px 12px; border:1px solid #ddd; border-radius:8px; font-size:14px;">
    </div>

    <!-- Tarjetas de resumen -->
    <div class="finanzas-grid">
        <div class="stat-card">
            <div class="stat-number">$<?php echo number_format($resumen['ingresos'] ?? 0, 2) ?></div>
            <div class="stat-label">Ingresos del día</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $resumen['total_ordenes'] ?? 0 ?></div>
            <div class="stat-label">Órdenes cerradas</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">$<?php echo number_format($resumen['promedio'] ?? 0, 2) ?></div>
            <div class="stat-label">Ticket promedio</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">$<?php echo number_format($resumen['efectivo'] ?? 0, 2) ?></div>
            <div class="stat-label">💵 Efectivo</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">$<?php echo number_format($resumen['tarjeta'] ?? 0, 2) ?></div>
            <div class="stat-label">💳 Tarjeta</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">$<?php echo number_format($resumen['transferencia'] ?? 0, 2) ?></div>
            <div class="stat-label">📱 Transferencia</div>
        </div>
    </div>

    <!-- Tabla de órdenes -->
    <h2 style="padding: 0 24px 12px; color:#2c1a0e;">Detalle de órdenes — <?php echo date('d/m/Y', strtotime($fecha)) ?></h2>
    <table class="orders-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Mesa / Cuenta</th>
                <th>Mesero</th>
                <th>Método</th>
                <th>Total</th>
                <th>Hora</th>
                <th>Ver</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($orden = mysqli_fetch_assoc($ordenes)): ?>
                <tr>
                    <td><?php echo $orden['id'] ?></td>
                    <td><?php echo htmlspecialchars($orden['mesa']) ?></td>
                    <td><?php echo htmlspecialchars($orden['username']) ?></td>
                    <td><?php echo ucfirst($orden['metodo_pago']) ?></td>
                    <td style="font-weight:700; color:#c67c2a;">$<?php echo number_format($orden['total'], 2) ?></td>
                    <td><?php echo date('H:i', strtotime($orden['fecha'])) ?></td>
                    <td>
                        <button onclick="verDetalle(<?php echo $orden['id'] ?>)" 
                                style="padding:4px 10px; border:1px solid #ddd; border-radius:6px; cursor:pointer; font-size:12px;">
                            Detalle
                        </button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Modal de detalle de orden -->
    <div id="modal-detalle" class="modal-overlay" style="display:none">
        <div class="modal-box" style="width:480px; max-height:80vh; overflow-y:auto">
            <h2>Detalle de orden</h2>
            <div id="detalle-content">Cargando...</div>
            <button onclick="document.getElementById('modal-detalle').style.display='none'"
                    style="margin-top:12px; padding:10px 20px; border:1px solid #ddd; border-radius:8px; cursor:pointer;">
                Cerrar
            </button>
        </div>
    </div>

    <div style="height: 40px;"></div>

    <script>
    function verDetalle(orderId) {
        document.getElementById('modal-detalle').style.display = 'flex';
        document.getElementById('detalle-content').innerHTML = 'Cargando...';

        fetch('./db/getOrderDetail.php?id=' + orderId)
            .then(r => r.json())
            .then(data => {
                if (!data.success) { 
                    document.getElementById('detalle-content').innerHTML = 'Error al cargar';
                    return;
                }
                let html = `<p style="color:#8a7060; margin-bottom:12px;">Mesa: <strong>${data.order.mesa}</strong> — ${data.order.fecha}</p>`;
                html += '<table style="width:100%; border-collapse:collapse">';
                html += '<tr style="background:#f5f0e8"><th style="padding:8px; text-align:left">Producto</th><th>Cant.</th><th>Precio</th><th>Subtotal</th></tr>';
                data.items.forEach(item => {
                    html += `<tr style="border-bottom:1px solid #f0ebe2">
                        <td style="padding:8px">${item.name}</td>
                        <td style="text-align:center">${item.qty}</td>
                        <td style="text-align:right">$${parseFloat(item.price).toFixed(2)}</td>
                        <td style="text-align:right; font-weight:700">$${parseFloat(item.subtotal).toFixed(2)}</td>
                    </tr>`;
                });
                html += `</table><div style="text-align:right; font-size:18px; font-weight:800; color:#c67c2a; margin-top:12px;">Total: $${parseFloat(data.order.total).toFixed(2)}</div>`;
                document.getElementById('detalle-content').innerHTML = html;
            });
    }
    </script>
</body>
</html>
