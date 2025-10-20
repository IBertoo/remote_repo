<?php
$host = getenv('POSTGRES_HOST');
$dbname = getenv('POSTGRES_DB');
$user = getenv('POSTGRES_USER');
$password = getenv('POSTGRES_PASSWORD');


echo $dbname . $user . $password. '...................';
try {
    $dsn = "pgsql:host=$host;port=5432;dbname=$dbname;";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Crear tabla si no existe
    $pdo->exec("CREATE TABLE IF NOT EXISTS saludos (
        id SERIAL PRIMARY KEY,
        mensaje VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Insertar un saludo
    $pdo->exec("INSERT INTO saludos (mensaje) VALUES ('¡Hola desde PHP y PostgreSQL!')");

    // Obtener el último saludo
    $stmt = $pdo->query("SELECT mensaje, created_at FROM saludos ORDER BY created_at DESC LIMIT 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<h1>" . htmlspecialchars($result['mensaje']) . "</h1>";
    echo "<p>Guardado en: " . htmlspecialchars($result['created_at']) . "</p>";

} catch (PDOException $e) {
    echo "Error de conexión: " . htmlspecialchars($e->getMessage());
}
?>
