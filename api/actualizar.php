<?php
// api/actualizar.php

// 1. Mostrar errores para saber qué pasa
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Actualizando Noticias...</h1>";

// 2. Incluir los archivos que descargan
// Usamos try-catch para que si uno falla (403), el otro siga funcionando
try {
    require_once "RSSElPais.php";
    echo "<p>✅ El País procesado correctamente.</p>";
} catch (Exception $e) {
    echo "<p>❌ Error en El País: " . $e->getMessage() . "</p>";
}

try {
    require_once "RSSElMundo.php";
    echo "<p>✅ El Mundo procesado correctamente.</p>";
} catch (Exception $e) {
    echo "<p>❌ Error en El Mundo: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<a href='index.php'><button style='padding:10px; cursor:pointer;'>⬅ Volver a las Noticias</button></a>";
?>