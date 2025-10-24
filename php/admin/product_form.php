<?php
require_once __DIR__ . '/middleware.php'; // Conexión y funciones comunes
$pdo = db();

$errors = [];
$success = false;

// Obtener categorías
$categories = $pdo->query("SELECT id_categoria, nombre FROM categoria ORDER BY nombre")->fetchAll();



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $marca = trim($_POST['marca'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $desc = trim($_POST['description'] ?? '');
    $descripcion_corta = trim($_POST['descripcion_corta'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);

    // --- Validación básica ---
    if ($name === '') {
        $errors[] = 'El nombre es obligatorio.';
    }
    if ($marca === '') {
        $errors[] = 'La marca es obligatoria.';
    }
    if ($price <= 0) {
        $errors[] = 'El precio debe ser mayor que 0.';
    }
    if (strlen($descripcion_corta) > 255) {
        $errors[] = 'La descripción corta no puede exceder los 255 caracteres.';
    }
    if (strlen($tags) > 255) {
        $errors[] = 'Los tags no pueden exceder los 255 caracteres.';
    }

    // --- Si no hay errores, insertar ---
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare('
                INSERT INTO producto 
                (nombre, marca, descripcion, precio, id_categoria, descripcion_corta, tags, codigo)
                VALUES (:n, :m, :d, :p, :c, :dc, :t, :cod)
            ');
            $stmt->execute([
                ':n' => $name,
                ':m' => $marca,
                ':d' => $desc,
                ':p' => $price,
                ':c' => $category_id ?: null,
                ':dc' => $descripcion_corta,
                ':t' => $tags,
                ':cod' => $marca//Aqui se esta haciendo una prueba.
            ]);
            $success = true;
            //PARA REDIRECCIONAR
            header('Location: products.php');
        } catch (PDOException $e) {
            $errors[] = "Error en la base de datos: " . $e->getMessage();
        }
    }
}
?>

<?php include __DIR__ . '/../partials/header.php'; ?>

<h1 class="mb-3">Nuevo producto</h1>

<?php if ($success): ?>
    <div class="alert alert-success">✅ Producto guardado correctamente.</div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>


<form method="post">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

    <div class="mb-3">
        <label class="form-label">Nombre</label>
        <input class="form-control" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Marca</label>
        <input class="form-control" name="marca" required value="<?= htmlspecialchars($_POST['marca'] ?? '') ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Precio</label>
        <input type="number" step="0.01" class="form-control" name="price" required value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Categoría</label>
        <select class="form-select" name="category_id">
            <option value="">-- Sin categoría --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id_categoria'] ?>" <?= ($_POST['category_id'] ?? '') == $cat['id_categoria'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Descripción corta</label>
        <input class="form-control" name="descripcion_corta" maxlength="255" value="<?= htmlspecialchars($_POST['descripcion_corta'] ?? '') ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Tags</label>
        <input class="form-control" name="tags" maxlength="255" placeholder="ejemplo: herramienta, eléctrica" value="<?= htmlspecialchars($_POST['tags'] ?? '') ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Descripción completa</label>
        <textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
    </div>

    <div class="d-grid">
        <button class="btn btn-primary">Guardar</button>
    </div>
</form>

<?php include __DIR__ . '/../partials/footer.php'; ?>
