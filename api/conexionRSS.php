<?php
// api/conexionRSS.php

function download($ruta){
    $ch = curl_init();
    
    // Configuración básica
    curl_setopt($ch, CURLOPT_URL, $ruta);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    // 1. IMPORTANTE: Manejar compresión (GZIP)
    // Esto evita el error de "String could not be parsed as XML" si llega comprimido
    curl_setopt($ch, CURLOPT_ENCODING, ''); 

    // 2. Seguir redirecciones (http -> https)
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    // 3. Ignorar problemas de SSL en Vercel
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    // 4. Cabeceras para parecer un navegador REAL (Evita el 403 Forbidden)
    $headers = [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language: es-ES,es;q=0.9,en;q=0.8',
        'Connection: keep-alive'
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    // 5. Timeouts para no colgar el servidor
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $salida = curl_exec($ch);
    
    // Si hay error en CURL o el servidor devuelve error (403/404/500)
    if(curl_errno($ch) || curl_getinfo($ch, CURLINFO_HTTP_CODE) >= 400){
        curl_close($ch);
        return false;
    }
    
    // En PHP 8+ no hace falta curl_close, pero lo dejamos por si acaso.
    curl_close($ch);
    
    return $salida;
}
?>