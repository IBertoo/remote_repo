<?php

require_once __DIR__ . '/middleware.php';

$pdo = db();

// Manejar actualización de pedidos

// Obtener todos los pedidos
$stmt = $pdo->query("
    SELECT 
        o.*,
        STRING_AGG(
            p.nombre || '|' || oi.cantidad || '|' || p.precio,
            ','
        ) AS items
    FROM pedido o
    LEFT JOIN detalle_pedido oi ON o.id_pedido = oi.id_pedido
    LEFT JOIN producto p ON oi.id_producto = p.id_producto
    GROUP BY o.id_pedido
    ORDER BY o.fecha_pedido DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../partials/header.php';
?>
<style>
    .table th, .table td {
        vertical-align: middle;
    }
    .quantity-input {
        width: 60px;
        display: inline-block;
    }
    .order-details {
        margin-top: 10px;
    }
</style>

<div class="container my-4">
    <h1 class="mb-4">Mis Pedidos</h1>
    <?php if (empty($orders)): ?>
        <p>No hay pedidos registrados.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="card shadow-sm mb-3">
                <div class="card-header">
                    <h5>Pedido #<?= htmlspecialchars($order['id_pedido']) ?> (<?= date('d/m/Y H:i', strtotime($order['fecha_pedido'])) ?>)</h5>
                    <p><strong>Estado:</strong> <?= $order['estado'] === 'pendiente' ? 'Pendiente' : 'Completado' ?></p>
                </div>
                <div class="card-body">
                    <div class="order-details">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unitario</th>
                                    <th>Total</th>
                                    <?php if ($order['estado'] === 'pendiente'): ?>
                                        <th>Acciones</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody id="order-items-<?= htmlspecialchars($order['id_pedido']) ?>">
                                <?php
                                $items = !empty($order['items']) ? explode(',', $order['items']) : [];
                                $total = 0;
                                foreach ($items as $item) {
                                    list($name, $quantity, $price) = explode('|', $item);
                                    $subtotal = $quantity * $price;
                                    $total += $subtotal;
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($name) ?></td>
                                        <td>
                                            <?php if ($order['estado'] === 'pendiente'): ?>
                                                <input type="number" class="form-control quantity-input" data-order-id="<?= htmlspecialchars($order['id_pedido']) ?>" data-product-name="<?= htmlspecialchars($name) ?>" value="<?= $quantity ?>" min="1">
                                            <?php else: ?>
                                                <?= $quantity ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>$<?= number_format($price, 2) ?></td>
                                        <td>$<?= number_format($subtotal, 2) ?></td>
                                        <?php if ($order['estado'] === 'pendiente'): ?>
                                            <td>
                                                <button class="btn btn-danger btn-sm remove-order-item" data-order-id="<?= htmlspecialchars($order['id_pedido']) ?>" data-product-name="<?= htmlspecialchars($name) ?>">Eliminar</button>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php } ?>
                                <tr>
                                    <td colspan="<?= $order['estado'] === 'pendiente' ? 4 : 3 ?>"><strong>Total</strong></td>
                                    <td><strong>$<?= number_format($total, 2) ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                        <?php if ($order['estado'] === 'pendiente'): ?>
                            <form class="update-order-form" method="post">
                                <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id_pedido']) ?>">
                                <input type="hidden" name="items_data" id="items-data-<?= htmlspecialchars($order['id_pedido']) ?>">
                                <button type="submit" name="update_order" class="btn btn-primary">Actualizar Pedido</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <a href="/index.php" class="btn btn-outline-secondary">Volver al catálogo</a>
</div>

<script>
function updateOrderItems(orderId) {
    const tbody = document.getElementById(`order-items-${orderId}`);
    const itemsDataInput = document.getElementById(`items-data-${orderId}`);
    const items = [];
    
    tbody.querySelectorAll('tr').forEach(row => {
        const name = row.querySelector('.quantity-input')?.getAttribute('data-product-name');
        const quantity = parseInt(row.querySelector('.quantity-input')?.value || 0);
        const price = parseFloat(row.cells[2].textContent.replace('$', '')) || 0;
        const productId = row.querySelector('.remove-order-item')?.getAttribute('data-product-id');
        if (name && quantity > 0) {
            items.push({ product_id: productId || name, name, quantity, price });
        }
    });

    itemsDataInput.value = JSON.stringify(items);
}

document.addEventListener('input', (e) => {
    if (e.target.classList.contains('quantity-input')) {
        const orderId = e.target.getAttribute('data-order-id');
        updateOrderItems(orderId);
    }
});

document.addEventListener('click', (e) => {
    if (e.target.classList.contains('remove-order-item')) {
        const orderId = e.target.getAttribute('data-order-id');
        const row = e.target.closest('tr');
        row.remove();
        updateOrderItems(orderId);
    }
});

// Inicializar datos de items para cada formulario
document.querySelectorAll('.update-order-form').forEach(form => {
    const orderId = form.querySelector('input[name="order_id"]').value;
    updateOrderItems(orderId);
});
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>