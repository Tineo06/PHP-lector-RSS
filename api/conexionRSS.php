<?php
// api/conexionRSS.php

function download($ruta){
    $ch = curl_init();
    
    // Configuración básica
    curl_setopt($ch, CURLOPT_URL, $ruta);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, false);

    // --- MEJORAS PARA EVITAR BLOQUEOS (403) ---

    // 1. Seguir redirecciones
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    // 2. "Disfrazarse" de navegador (User-Agent) para que no te bloqueen
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36");

    // 3. Tiempo de espera máximo (10 segundos) para no colgar la web
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $salida = curl_exec($ch);
    
    // Comprobar errores antes de terminar
    if(curl_errno($ch)){
        // Si hay error, devolvemos false (puedes descomentar el error_log para depurar)
        // error_log('Error cURL: ' . curl_error($ch));
        return false;
    }
    
    // NOTA: En PHP 8.0+, curl_close() ya no es necesario, el objeto se cierra solo.
    // Lo hemos quitado para evitar el aviso de "deprecated".
    
    return $salida;
}
?>