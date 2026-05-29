<?php
require "./db/db.php";
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ./login.php?error=Inicia sesión");
    return;
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];

$sql = "SELECT * FROM products ORDER BY category, name";
$result = mysqli_query($conn, $sql);
$products = [];
while ($p = mysqli_fetch_assoc($result)) {
    $products[] = $p;
}

// Agrupar por categoría
$categories = [];
foreach ($products as $p) {
    $cat = $p['category'] ?? 'Otros';
    $categories[$cat][] = $p;
}
$categoryNames = array_keys($categories);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal de ventas – Las 3 Escobas</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="ventas.css">
</head>
<body class="ventas-body">

<div class="ventas-layout">

    <!-- Sidebar de categorías -->
    <aside class="cat-sidebar">
        <div class="brand-logo">🧹</div>
        <?php foreach ($categoryNames as $cat): ?>
            <button class="cat-btn" onclick="showCategory('<?php echo htmlspecialchars($cat) ?>')">
                <?php echo htmlspecialchars($cat) ?>
            </button>
        <?php endforeach; ?>
        <div class="sidebar-footer">
            <?php if ($role == 'admin'): ?>
                <a href="./adminProducts.php" class="sidebar-link">Admin</a>
                <a href="./finanzas.php" class="sidebar-link">Finanzas</a>
            <?php endif; ?>
            <a href="./db/logout.php" class="sidebar-link">Salir</a>
        </div>
    </aside>

    <!-- Grid de productos -->
    <main class="products-grid-area">
        <div class="ventas-topbar">
            <span class="server-name">👤 <?php echo htmlspecialchars($username) ?></span>
            <span id="active-category" class="active-cat-label"><?php echo htmlspecialchars($categoryNames[0] ?? '') ?></span>
            <span class="time-display" id="clock"></span>
        </div>

        <?php foreach ($categories as $cat => $prods): ?>
            <div class="product-panel" id="panel-<?php echo htmlspecialchars($cat) ?>" style="display:none">
                <div class="products-grid">
                    <?php foreach ($prods as $p): ?>
                        <div class="product-card" onclick="addToOrder(<?php echo $p['id'] ?>, '<?php echo htmlspecialchars($p['name']) ?>', <?php echo $p['price'] ?>, '<?php echo htmlspecialchars($p['image']) ?>')">
                            <img src="./images/<?php echo htmlspecialchars($p['image']) ?>" alt="<?php echo htmlspecialchars($p['name']) ?>">
                            <p class="card-name"><?php echo htmlspecialchars($p['name']) ?></p>
                            <p class="card-price">$<?php echo number_format($p['price'], 2) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </main>

    <!-- Panel de ticket/orden actual -->
    <aside class="ticket-panel">
        <div class="ticket-header">
            <span>🧾 Orden actual</span>
            <span id="ticket-number" class="ticket-num">#---</span>
        </div>

        <div class="ticket-items" id="ticket-items">
            <p class="ticket-empty">Agrega productos tocando las tarjetas</p>
        </div>

        <div class="ticket-totals">
            <div class="totals-row">
                <span>Subtotal</span>
                <span id="subtotal">$0.00</span>
            </div>
            <div class="totals-row total-final">
                <span>Total</span>
                <span id="total">$0.00</span>
            </div>
        </div>

        <div class="ticket-actions">
            <button class="btn-hold" onclick="clearOrder()">🗑 Limpiar</button>
            <button class="btn-bill" onclick="printBill()">🖨 Imprimir</button>
        </div>
        <button class="btn-payment" onclick="submitOrder()">💳 COBRAR</button>
    </aside>

</div>

<!-- Modal de pago -->
<div id="modal-pago" class="modal-overlay" style="display:none">
    <div class="modal-box">
        <h2>Confirmar cobro</h2>
        <p>Total a cobrar: <strong id="modal-total"></strong></p>
        <label>Mesa / Número de cuenta</label>
        <input type="text" id="mesa-num" placeholder="Ej: Mesa 5, Barra 2">
        <label>Método de pago</label>
        <select id="metodo-pago">
            <option value="efectivo">Efectivo</option>
            <option value="tarjeta">Tarjeta</option>
            <option value="transferencia">Transferencia</option>
        </select>
        <div class="modal-actions">
            <button onclick="cancelPago()">Cancelar</button>
            <button class="btn-payment" onclick="confirmPago()">Confirmar venta</button>
        </div>
    </div>
</div>

<script>
// Estado de la orden
let order = [];
let ticketCounter = Math.floor(Math.random() * 900) + 100;

// Reloj
function updateClock() {
    const now = new Date();
    document.getElementById('clock').textContent =
        now.getHours().toString().padStart(2,'0') + ':' +
        now.getMinutes().toString().padStart(2,'0');
}
setInterval(updateClock, 1000);
updateClock();

// Mostrar categoría
const firstCat = <?php echo json_encode($categoryNames[0] ?? '') ?>;
showCategory(firstCat);

function showCategory(cat) {
    document.querySelectorAll('.product-panel').forEach(p => p.style.display = 'none');
    document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));

    const panel = document.getElementById('panel-' + cat);
    if (panel) panel.style.display = 'block';

    document.querySelectorAll('.cat-btn').forEach(b => {
        if (b.textContent.trim() === cat) b.classList.add('active');
    });
    document.getElementById('active-category').textContent = cat;
}

// Agregar producto a la orden
function addToOrder(id, name, price, image) {
    const existing = order.find(i => i.id === id);
    if (existing) {
        existing.qty++;
    } else {
        order.push({ id, name, price, image, qty: 1 });
    }
    renderTicket();
}

function changeQty(id, delta) {
    const item = order.find(i => i.id === id);
    if (!item) return;
    item.qty += delta;
    if (item.qty <= 0) order = order.filter(i => i.id !== id);
    renderTicket();
}

function renderTicket() {
    const container = document.getElementById('ticket-items');
    document.getElementById('ticket-number').textContent = '#' + ticketCounter;

    if (order.length === 0) {
        container.innerHTML = '<p class="ticket-empty">Agrega productos tocando las tarjetas</p>';
        document.getElementById('subtotal').textContent = '$0.00';
        document.getElementById('total').textContent = '$0.00';
        return;
    }

    let html = '';
    let subtotal = 0;
    order.forEach(item => {
        const lineTotal = item.price * item.qty;
        subtotal += lineTotal;
        html += `
            <div class="ticket-item">
                <img src="./images/${item.image}" alt="${item.name}">
                <div class="ticket-item-info">
                    <span class="ticket-item-name">${item.name}</span>
                    <span class="ticket-item-price">$${item.price.toFixed(2)}</span>
                </div>
                <div class="ticket-qty-ctrl">
                    <button onclick="changeQty(${item.id}, -1)">−</button>
                    <span>${item.qty}</span>
                    <button onclick="changeQty(${item.id}, 1)">+</button>
                </div>
                <span class="ticket-line-total">$${lineTotal.toFixed(2)}</span>
            </div>`;
    });

    container.innerHTML = html;
    document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('total').textContent = '$' + subtotal.toFixed(2);
}

function clearOrder() {
    if (order.length === 0) return;
    if (confirm('¿Limpiar la orden actual?')) {
        order = [];
        renderTicket();
    }
}

function printBill() {
    if (order.length === 0) { alert('No hay productos en la orden.'); return; }
    window.print();
}

function submitOrder() {
    if (order.length === 0) { alert('Agrega al menos un producto.'); return; }
    const total = order.reduce((s, i) => s + i.price * i.qty, 0);
    document.getElementById('modal-total').textContent = '$' + total.toFixed(2);
    document.getElementById('modal-pago').style.display = 'flex';
}

function cancelPago() {
    document.getElementById('modal-pago').style.display = 'none';
}

function confirmPago() {
    const mesa = document.getElementById('mesa-num').value || 'Sin mesa';
    const metodo = document.getElementById('metodo-pago').value;
    const total = order.reduce((s, i) => s + i.price * i.qty, 0);
    const items = order.map(i => i.id + ':' + i.qty).join(',');

    // Enviar venta al servidor
    fetch('./db/saveOrder.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `mesa=${encodeURIComponent(mesa)}&metodo=${metodo}&total=${total}&items=${encodeURIComponent(items)}&username=<?php echo urlencode($username) ?>`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('✅ Venta registrada correctamente. Ticket #' + ticketCounter);
            order = [];
            ticketCounter++;
            renderTicket();
            cancelPago();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(() => alert('Error de conexión con el servidor'));
}
</script>
</body>
</html>
