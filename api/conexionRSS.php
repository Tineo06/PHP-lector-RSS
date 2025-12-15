<?php

function download($ruta){
    $ch = curl_init();
    
    // Configuración básica
    curl_setopt($ch, CURLOPT_URL, $ruta);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, false);

    // --- MEJORAS PARA VERCEL ---

    // 1. IMPORTANTE: Seguir redirecciones (Si El Pais te manda de http a https, curl lo sigue)
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    // 2. VITAL: "Disfrazarse" de navegador (User-Agent)
    // Sin esto, muchos servidores rechazan la conexión pensando que eres un bot malicioso.
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36");

    // 3. Opcional: A veces Vercel tiene problemas validando certificados SSL antiguos
    // Solo descomenta la siguiente linea si te da error de "SSL certificate problem"
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $salida = curl_exec($ch);
    
    // Comprobar si hubo error en la descarga
    if(curl_errno($ch)){
        // Puedes descomentar esto para ver errores en los logs de Vercel
        // error_log('Error cURL: ' . curl_error($ch));
        return false;
    }
    
    curl_close($ch);
    return $salida;
}
?>