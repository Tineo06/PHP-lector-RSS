<?php
// api/conexionBBDD.php

function obtenerConexion() {
    // Estas variables las pone Vercel automáticamente
    $host = getenv('POSTGRES_HOST');
    $dbname = getenv('POSTGRES_DATABASE');
    $user = getenv('POSTGRES_USER');
    $password = getenv('POSTGRES_PASSWORD');

    // IMPORTANTE: Neon requiere sslmode=require
    $dsn = "pgsql:host=$host;port=5432;dbname=$dbname;user=$user;password=$password;sslmode=require";

    try {
        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        // En producción no mostramos el error real por seguridad
        // error_log("Error BD: " . $e->getMessage());
        return null;
    }
}
?>