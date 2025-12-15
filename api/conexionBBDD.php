<?php
// conexionBBDD.php

// Obtenemos las credenciales de las Variables de Entorno de Vercel
$host = getenv('PGHOST');
$db   = getenv('PGDATABASE');
$user = getenv('PGUSER');
$pass = getenv('PGPASSWORD');

// Cadena de conexión para PostgreSQL
$dsn = "pgsql:host=$host;port=5432;dbname=$db;";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Creamos la conexión PDO
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Si falla, matamos el proceso y mostramos error (útil para debug ahora mismo)
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>