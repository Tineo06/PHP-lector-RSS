<?php
// api/conexionRSS.php

function download($ruta){
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $ruta);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    // 1. GZIP: Vital para descomprimir
    curl_setopt($ch, CURLOPT_ENCODING, ''); 

    // 2. Seguir redirecciones
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    // 3. EL DISFRAZ DEFINITIVO (Simulamos ser un usuario normal en Windows)
    $headers = [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language: es-ES,es;q=0.9,en;q=0.8',
        'Referer: https://www.google.com/',
        'Connection: keep-alive',
        'Upgrade-Insecure-Requests: 1'
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    // 4. Timeout de 5 segundos. Si tarda más, CORTAMOS para no bloquear tu web.
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); 

    $salida = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Si hay error de red o nos dan un 403, devolvemos FALSE
    if(curl_errno($ch) || $httpCode >= 400){
        // No mostramos el error para no romper la web, solo devolvemos false
        return false; 
    }
    
    return $salida;
}
?>