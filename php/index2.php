<?php
// En Render, usa DATABASE_URL para PostgreSQL
if (getenv('DATABASE_URL')) {
    $dbUrl = parse_url(getenv('DATABASE_URL'));
    $host = $dbUrl['host'];
    $port = $dbUrl['port'];
    $dbname = ltrim($dbUrl['path'], '/');
    $user = $dbUrl['user'];
    $password = $dbUrl['pass'];
} else {
    // Fallback para desarrollo local
    $host = getenv('POSTGRES_HOST') ?: 'db';
    $port = 5432;
    $dbname = getenv('POSTGRES_DB') ?: 'app_db';
    $user = getenv('POSTGRES_USER') ?: 'app_user';
    $password = getenv('POSTGRES_PASSWORD') ?: 'app_password';
}

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Crear tabla si no existe
    $pdo->exec("CREATE TABLE IF NOT EXISTS saludos (
        id SERIAL PRIMARY KEY,
        mensaje VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Insertar un saludo
    $pdo->exec("INSERT INTO saludos (mensaje) VALUES ('¡Hola desde PHP y PostgreSQL en Render.com!')");

    // Obtener el último saludo
    $stmt = $pdo->query("SELECT mensaje, created_at FROM saludos ORDER BY created_at DESC LIMIT 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<h1>" . htmlspecialchars($result['mensaje']) . "</h1>";
    echo "<p>Guardado en: " . htmlspecialchars($result['created_at']) . "</p>";
    echo "<p><em>Desplegado en Render.com</em></p>";

} catch (PDOException $e) {
    echo "Error de conexión: " . htmlspecialchars($e->getMessage());
}
?>
