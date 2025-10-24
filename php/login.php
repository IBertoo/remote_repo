<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';
csrf_validate();
$error = '';
//En esta posicion hay que redireccionar directamente, Si hay sesion.
if (!empty($_SESSION['user'])) {
  header('Location: /admin/');
  exit;
}


//Sin sesion, se verificar si se mando datos en el form.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //$secretKey =getenv('CAPTCHA_SECRET_KEY');
    $secretKey = getenv('CAPTCHA_SECRET_KEY');
    $captchaResponse = $_POST['g-recaptcha-response'] ?? '';

    if (empty($captchaResponse)) {
        echo "<p style='color:red;'>Por favor, confirma que no eres un robot.</p>";
        exit;
    }

    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$captchaResponse}");
    $responseData = json_decode($verify);

    if (!$responseData->success) {
        echo "<p style='color:red;'>Error al verificar el reCAPTCHA. Intenta nuevamente.</p>";
        exit;
    }
    //VALIDACION EN reCAPTCHA


  $u = trim($_POST['username'] ?? '');
  $p = $_POST['password'] ?? '';
  $stmt = db()->prepare('SELECT * FROM usuario WHERE nombre = :u LIMIT 1');
  $stmt->execute([':u' => $u]);
  $user = $stmt->fetch();
  if ($user && password_verify($p, $user['password_hash'])) {
    $_SESSION['user'] = ['id' => $user['id_usuario'], 'username' => $user['nombre']];

    header('Location: /admin/');
    exit;
  } else {
    $error = 'Credenciales inválidas';
  }
}



include __DIR__ . '/partials/header.php';
?>
<div class="row justify-content-center">
  <div class="col-sm-10 col-md-6 col-lg-4">
    <h1 class="mb-3">Ingresar</h1>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<!-- FORMULARIO -->
    <form method="post" id="loginForm">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <div class="mb-3">
        <label class="form-label">Usuario</label>
        <input class="form-control" name="username" id="username" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Contraseña</label>
        <input type="password" class="form-control" name="password" id="password" required>
      </div>

      <!-- SITIO DE RECAPTCHA -->
      <div class="mb-3">
        <div class="g-recaptcha" data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"></div>
      </div>

      <div class="d-grid">
        <button type="submit" class="btn btn-primary">Entrar</button>

      </div>
    </form>
  </div>
</div>
<script>
// ✅ Esperar que el DOM cargue completamente
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("loginForm");

    form.addEventListener("submit", function(event) {
        const response = grecaptcha.getResponse();

        if (response.length === 0) {
            event.preventDefault(); // ❌ Evita el envío
            alert("⚠️ Por favor, marca la casilla 'No soy un robot' antes de continuar.");
            return false;
        }

        return true; // ✅ Enviar formulario si está marcado
    });
});
</script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php include __DIR__ . '/partials/footer.php'; ?>




<?php
$secretKey = getenv('CAPTCHA_SECRET_KEY');
$captchaResponse = $_POST['g-recaptcha-response'] ?? '';

if (empty($captchaResponse)) {
    echo "<p style='color:red;'>Por favor, confirma que no eres un robot.</p>";
    exit;
}

$ch = curl_init("https://www.google.com/recaptcha/api/siteverify");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'secret' => $secretKey,
    'response' => $captchaResponse,
    'remoteip' => $_SERVER['REMOTE_ADDR']
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$responseData = json_decode($response, true);

echo "<pre>";
print_r($responseData);
echo "</pre>";
