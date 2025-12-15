<?php
// Usaremos variables de entorno de Vercel/Neon
$host = getenv('PGHOST');
$db   = getenv('PGDATABASE');
$user = getenv('PGUSER');
$pass = getenv('PGPASSWORD');

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
    // En producción no mostrar el error detallado, pero para debug ayuda
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>