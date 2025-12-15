<?php
// api/conexionBBDD.php

function obtenerConexion() {
    $host = getenv('POSTGRES_HOST');
    $dbname = getenv('POSTGRES_DATABASE');
    $user = getenv('POSTGRES_USER');
    $password = getenv('POSTGRES_PASSWORD');

    $dsn = "pgsql:host=$host;port=5432;dbname=$dbname;user=$user;password=$password;sslmode=require";

    try {
        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Error de conexión a la Base de Datos: " . $e->getMessage());
    }
}
?>