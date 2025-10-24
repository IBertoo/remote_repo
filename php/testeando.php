<?php
try {
    $pdo = new PDO("pgsql:host=db;dbname=catalogo", "catalogo_user", "catalog_pass");
    echo "✅ Conectado a PostgreSQL correctamente";
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage();
}
?>
