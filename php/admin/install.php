<?php
//require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../config.php';
csrf_validate();

$pdo = db();
$count = (int)$pdo->query('SELECT COUNT(*) FROM usuario')->fetchColumn();
if ($count > 20) {
  header('Location: /login.php');
  exit;
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $u = trim($_POST['username'] ?? '');
  $p = $_POST['password'] ?? '';
  $r = $_POST['rol'] ?? '';
  if ($u !== '' && $p !== ''&& $r !== '') {
    $hash = password_hash($p, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO usuario (nombre, password_hash, rol) VALUES (:u, :h, :r)');
    $stmt->execute([':u' => $u, ':h' => $hash, ':r' => $r]);
    $msg = 'Usuario creado. Ya puedes iniciar sesión.';
  } else {
    $msg = 'Completa usuario y contraseña.';
  }
}
include __DIR__ . '/../partials/header.php';?>
<div class="row justify-content-center">
  <div class="col-sm-10 col-md-6 col-lg-5">
    <h1 class="mb-3">Instalación: crear admin</h1>
    <?php if ($msg): ?><div class="alert alert-info"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
          <form method="post">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <div class="mb-3">
              <label class="form-label">Usuario</label>
              <input class="form-control" name="username" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Contraseña</label>
              <input type="password" class="form-control" name="password" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Rol(admin,editor,usuario,visitante)</label>
              <input class="form-control" name="rol" required>
            </div>
            <div class="d-grid">
              <button class="btn btn-success">Crear administrador</button>
            </div>
              </form>
      </div>
    </div>
<?php
include __DIR__ . '/../partials/footer.php';?>


