<?php

$link = false; 

$dbUrl = getenv('DB_POSTGRES_URL'); 

// --- CAMBIO INICIO: Soporte para Localhost ---
if (empty($dbUrl)) {
    // Si la variable de entorno está vacía, usamos una manual.
    // FORMATO: postgres://usuario:contraseña@servidor:puerto/nombre_base_datos
    // CAMBIA ESTO por tus credenciales de PostgreSQL:
    $dbUrl = "postgres://postgres:root@localhost:5432/periodico";
}
// --- CAMBIO FIN ---

if (!empty($dbUrl)) {
    
    try {
        $url = parse_url($dbUrl);
        
        $host = $url['host'] ?? '';
        $port = $url['port'] ?? 5432;
        $user = $url['user'] ?? '';
        $pass = $url['pass'] ?? '';
        $path = ltrim($url['path'] ?? '/neondb', '/');

        $dsn = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s;sslmode=require",
            $host,
            $port,
            $path,
            $user,
            $pass
        );

        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $pdo->exec("SET NAMES 'utf8'");

        $link = $pdo; 
        
    } catch (PDOException $e) {
        $link = false;
        // Opcional: Descomenta esto para ver el error exacto de conexión si sigue fallando
        // echo "Error de conexión: " . $e->getMessage();
    }
}
?>