<?php
// Función para obtener parámetros de conexión
function getDbParams() {
    if ($dbUrl = getenv('DATABASE_URL')) {
        // Entorno de Render
        $url = parse_url($dbUrl);
        return [
            'host' => $url['host'],
            'port' => $url['port'] ?? 5432,
            'dbname' => ltrim($url['path'], '/'),
            'user' => $url['user'],
            'password' => $url['pass']
        ];
    } else {
        // Entorno local (Docker Compose)
        return [
            'host' => getenv('POSTGRES_HOST') ?: 'db',
            'port' => 5432,
            'dbname' => getenv('POSTGRES_DB') ?: 'app_db',
            'user' => getenv('POSTGRES_USER') ?: 'app_user',
            'password' => getenv('POSTGRES_PASSWORD') ?: 'app_password'
        ];
    }
}

try {
    $params = getDbParams();
    $dsn = "pgsql:host={$params['host']};port={$params['port']};dbname={$params['dbname']};";
    $pdo = new PDO($dsn, $params['user'], $params['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

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
    echo "<p><em>Desplegado en " . (getenv('DATABASE_URL') ? 'Render.com' : 'Local') . "</em></p>";

} catch (PDOException $e) {
    echo "Error de conexión: " . htmlspecialchars($e->getMessage());
}
?>
