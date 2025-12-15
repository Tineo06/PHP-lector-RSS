<?php
// Usamos variables de entorno de Vercel
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$password = getenv('DB_PASS');
$dbname = getenv('DB_NAME');
$port = "5432";

// Cadena de conexión para PostgreSQL (Neon)
$connection_string = "host={$host} port={$port} dbname={$dbname} user={$user} password={$password} sslmode=require";

$link = pg_connect($connection_string);

if (!$link) {
    echo "Error: No se pudo conectar a la base de datos de Neon.";
    exit;
}

// En Postgres no hace falta 'SET NAMES utf8' normalmente, pero si fuera necesario:
pg_set_client_encoding($link, "UTF8");
?>