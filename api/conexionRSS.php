<?php
// api/conexionRSS.php

function download($ruta){
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $ruta);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, false);
    
    // Seguir redirecciones y simular ser un navegador real
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36");
    
    // Aumentar tiempo de espera por si la red va lenta
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $salida = curl_exec($ch);
    
    if(curl_errno($ch)){
        return false;
    }
    
    curl_close($ch);
    return $salida;
}
?>