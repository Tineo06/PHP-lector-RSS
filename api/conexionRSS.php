<?php
// api/conexionRSS.php

function download($ruta){
    // Iniciamos cURL (devuelve un Objeto en PHP 8+)
    $ch = curl_init();
    
    // Configuración básica
    curl_setopt($ch, CURLOPT_URL, $ruta);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    // 1. GZIP: Vital para descomprimir si el periódico lo envía comprimido
    curl_setopt($ch, CURLOPT_ENCODING, ''); 

    // 2. Redirecciones y SSL
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    // 3. User-Agent REAL (Esto es lo que evita el bloqueo 403)
    $headers = [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Connection: keep-alive'
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    // 4. Timeout rápido (3 segundos)
    // Si el periódico tarda más de 3s, cortamos para no bloquear tu web
    curl_setopt($ch, CURLOPT_TIMEOUT, 3); 

    // Ejecutamos
    $salida = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Si hay error en la red o el código HTTP es de error (400, 403, 404, 500...)
    if(curl_errno($ch) || $httpCode >= 400){
        // No cerramos nada manualmente, PHP 8 lo hace solo.
        return false; 
    }
    
    // NOTA: Hemos quitado curl_close($ch); porque en PHP 8.0+
    // el objeto se cierra automáticamente al terminar la función.
    
    return $salida;
}
?>