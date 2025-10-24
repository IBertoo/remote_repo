<?php
require_once __DIR__ . '/middleware.php';
$pdo = db();
$search = trim($_GET['search'] ?? '');
$uploadDir = __DIR__ . '/../';




// Borrar producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $productId = (int)$_POST['delete_id'];

    // 1️⃣ Obtener las imágenes del producto
    $stmt = $pdo->prepare('SELECT nombre_img FROM imagenes_producto WHERE id_producto = :id');
    $stmt->execute([':id' => $productId]);
    $imagenes = $stmt->fetchAll(PDO::FETCH_COLUMN);
// 1️⃣ Eliminar order_items asociados
$stmt = $pdo->prepare('DELETE FROM detalle_pedido WHERE id_producto = :id');
$stmt->execute([':id' => $productId]);
    // 2️⃣ Eliminar los archivos físicos
    foreach ($imagenes as $image) {


        $filePath = $uploadDir . ltrim($image, '/');
        if (is_file($filePath)) {
            unlink($filePath);
        }

        $info = pathinfo($image);
            // Crear la nueva ruta con prefijo "medium_"
            $image2 = $info['dirname'] . '/medium_' . $info['filename'] . '.' . $info['extension'];

            $filePathMedium = $uploadDir . ltrim($image2, '/');
            if (is_file($filePathMedium)) {
                unlink($filePathMedium); // eliminar archivo físico
            }


    }

    // 3️⃣ Borrar los registros de imágenes
    $stmt = $pdo->prepare('DELETE FROM imagenes_producto WHERE id_producto = :id');
    $stmt->execute([':id' => $productId]);

    // 4️⃣ Borrar el producto
    $stmt = $pdo->prepare('DELETE FROM producto WHERE id_producto = :id');
    $stmt->execute([':id' => $productId]);

    // 5️⃣ Redirigir
    header('Location: /admin/products.php');
    exit;
}




// Paginación
$perPage = 15;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// Consulta para contar el total de productos
$where = $search ? 'WHERE p.nombre LIKE :search' : '';
$params = $search ? [':search' => "%$search%"] : [];
$stmt = $pdo->prepare("SELECT COUNT(*) FROM producto p $where");
$stmt->execute($params);
$total = $stmt->fetchColumn();
$pages = ceil($total / $perPage);

// Consulta principal
$query = "
    SELECT 
        p.*, 
        c.nombre AS category_name, 
        COALESCE(STRING_AGG(pi.nombre_img, ', '), '') AS images
    FROM producto p
    LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
    LEFT JOIN imagenes_producto pi ON p.id_producto = pi.id_producto
    $where
    GROUP BY p.id_producto, c.nombre
    ORDER BY p.fecha_creacion DESC, p.id_producto DESC
    LIMIT :limit OFFSET :offset
";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':limit', (int) $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
if ($search) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);


include __DIR__ . '/../partials/header.php';
?>
<h1 class="mb-3">Productos</h1>
<a href="/admin/product_form.php" class="btn btn-success mb-3">+ Nuevo producto</a>
<form method="get" class="mb-3">
    <div class="input-group">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Buscar productos...">
        <button class="btn btn-outline-secondary">Buscar</button>
    </div>
</form>
<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Precio</th>
            <th>Categoría</th>
            <th>Imágenes</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $p): ?>
            <tr>
                <td><?= $p['id_producto'] ?></td>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td>$<?= number_format((float)$p['precio'], 2) ?></td>
                <td><?= htmlspecialchars($p['category_name'] ?? 'Sin categoría') ?></td>
                <td>
                    <?php
                    $rutaImagen="";
                    $images = !empty($p['images']) ? explode(',', $p['images']) : [];
                    if (!empty($images)):
                    ?>
                        <div class="d-flex flex-wrap gap-2">


                            <?php foreach ($images as $image): 
                                $imgCamino = '/uploads/small/' . trim($image);
                                ?>
                                <img src="<?= htmlspecialchars($imgCamino) ?>" alt="img" style="width:60px;height:auto;">.

                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <span>Sin imágenes</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="/admin/agregar_imagen.php?id=<?= $p['id_producto'] ?>" class="btn btn-sm btn-warning">ActualizarFoto</a>
                    <a href="/admin/product_formEditar.php?id=<?= $p['id_producto'] ?>" class="btn btn-sm btn-primary">Editar</a>
                    

                    <form method="post" style="display:inline" onsubmit="return confirm('¿Eliminar este producto?');">
                        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                        <input type="hidden" name="delete_id" value="<?= $p['id_producto'] ?>">
                        <button class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Paginador -->
<nav>
    <ul class="pagination">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>

<?php include __DIR__ . '/../partials/footer.php'; ?>