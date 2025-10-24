<?php
require_once __DIR__ . '/db.php';
$pdo = db();

// Obtener ID del producto
$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: /index.php');
    exit;
}

// Incrementar visitas
$stmt = $pdo->prepare('UPDATE producto SET visitas = visitas + 1 WHERE id_producto = :id');
$stmt->execute([':id' => $id]);


// Obtener datos del producto
$stmt = $pdo->prepare(' 
SELECT p.*, c.nombre AS category_name, img.images FROM producto p
LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
LEFT JOIN (
    SELECT id_producto, STRING_AGG(nombre_img, \',\') AS images
    FROM imagenes_producto
    GROUP BY id_producto
) img ON p.id_producto = img.id_producto
WHERE p.id_producto = :id');
$stmt->execute([':id' => $id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: /index.php');
    exit;
}

include __DIR__ . '/partials/header.php';
?>


<div class="container my-4">
    <h1 class="mb-4"><?= htmlspecialchars($product['nombre']) ?></h1>
    <div class="row">
        <div class="col-md-7">
            <?php
            $images = !empty($product['images']) ? explode(',', $product['images']) : [];
            if (!empty($images)):
            ?>
                <div id="carousel-<?= $product['id_producto'] ?>" class="carousel slide" data-bs-ride="false">
                    <div class="carousel-indicators">
                        <?php foreach ($images as $index => $image): ?>
                            <button type="button" data-bs-target="#carousel-<?= $product['id_producto'] ?>" data-bs-slide-to="<?= $index ?>" class="<?= $index === 0 ? 'active' : '' ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= $index + 1 ?>"></button>
                        <?php endforeach; ?>
                    </div>
                    <div class="carousel-inner">
                        <?php foreach ($images as $index => $image): ?>
                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">

                            <!-- SE HACE LA MODIFICACION -->

                            <!-- Convertir: $image=/uploads/img.webp a $image2=/uploads/medium_img.webp -->
                            <!-- // Descomponer la ruta -->
                            <?php 
                            $imagen= '/uploads/medium/' . trim($image);
                            ?>

                                <img src="<?= htmlspecialchars($imagen) ?>" class="d-block w-100" alt="Producto <?= htmlspecialchars($product['nombre']) ?>" loading="lazy">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($images) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carousel-<?= $product['id_producto'] ?>" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Anterior</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carousel-<?= $product['id_producto'] ?>" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Siguiente</span>
                        </button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <img src="/img/default.webp" class="d-block w-100" alt="Producto" loading="lazy">
                
            <?php endif; ?>
        </div>
        <div class="col-md-5">
            <div class="card shadow-sm p-4">
                <div class="product-details">
                    <p><b>Marca:</b> <?= htmlspecialchars($product['marca'] ?? '') ?></p>
                    <p><b>Categoría:</b> <?= htmlspecialchars($product['nombre_categoria'] ?? 'Sin categoría') ?></p>
                    <p><b>Precio:</b> $<?= number_format((float)$product['precio'], 2) ?></p>
                    <p><b>Descripción corta:</b> <?= htmlspecialchars($product['descripcion_corta'] ?? '') ?></p>
                    <p><b>Descripción:</b> <?= nl2br(htmlspecialchars($product['descripcion'] ?? '')) ?></p>
                    <p><b>Tags:</b> <?= htmlspecialchars($product['tags'] ?? '') ?></p>
                    <p><b>Visitas:</b> <?= $product['visitas'] ?></p>
                    <p><b>Fecha de creación:</b> <?= date('d/m/Y H:i', strtotime($product['fecha_creacion'])) ?></p>
                    <!-- <p><b>Última actualización:</b> <?= date('d/m/Y H:i', strtotime($product['fecha_actualizacion'])) ?></p> -->
                    <button class="btn btn-primary mt-3 add-to-cart" data-id="<?= $product['id_producto'] ?>" data-name="<?= htmlspecialchars($product['nombre']) ?>" data-price="<?= $product['precio'] ?>">Añadir al carrito</button>
                    <a href="/index.php" class="btn btn-outline-secondary mt-3">Volver al catálogo</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.add-to-cart').forEach(button => {
    button.addEventListener('click', () => {
        // Agregar clase de animación
        button.classList.add('animate');
        setTimeout(() => {
            button.classList.remove('animate');
        }, 300); // Duración de la animación

        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');
        const price = parseFloat(button.getAttribute('data-price'));

        let cart = JSON.parse(localStorage.getItem('cart') || '{}');
        if (cart[id]) {
            cart[id].quantity += 1;
        } else {
            cart[id] = { id, name, price, quantity: 1 };
        }
        localStorage.setItem('cart', JSON.stringify(cart));
    });
});
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>