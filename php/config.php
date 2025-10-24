<?php
// Lee variables de entorno inyectadas por docker-compose
// $CONFIG = [
//   'db_host' => getenv('POSTGRES_HOST') ?: '',
//   'db_name' => getenv('POSTGRES_DB') ?: '',
//   'db_user' => getenv('POSTGRES_USER') ?: '',
//   'db_pass' => getenv('POSTGRES_PASSWORD') ?: '',
//   'app_name' => 'Catálogo',
// ];
$CONFIG = [
  'db_host' => getenv('DB_HOST') ?: '',
  'db_name' => getenv('DB_NAME') ?: '',
  'db_user' => getenv('DB_USER') ?: '',
  'db_pass' => getenv('DB_PASSWORD') ?: '',
  'app_name' => 'Catálogo',
];

// // Parsear la URL
// $databaseUrl = getenv('DATABASE_URL');
// $parts = parse_url($databaseUrl);

// // Extraer los componentes
// $host = $parts['host'];
// $port = $parts['port'] ?? 5432; // valor por defecto PostgreSQL
// $user = $parts['user'];
// $pass = $parts['pass'];
// $dbname = ltrim($parts['path'], '/');



