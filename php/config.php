<?php
// Lee variables de entorno inyectadas por docker-compose
$CONFIG = [
  'db_host' => getenv('POSTGRES_HOST') ?: '',
  'db_name' => getenv('POSTGRES_DB') ?: '',
  'db_user' => getenv('POSTGRES_USER') ?: '',
  'db_pass' => getenv('POSTGRES_PASSWORD') ?: '',
  'app_name' => 'Catálogo',
];