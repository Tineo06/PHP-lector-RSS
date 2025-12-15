<?php
// conexionRSS.php ya debe estar incluido o requerirse
require_once "conexionRSS.php";

// URL del RSS de El Mundo
$sXML = download("https://e00-elmundo.uecdn.es/elmundo/rss/portada.xml");
$oXML = new SimpleXMLElement($sXML);

// Asumimos que $link ya viene de index.php o lo requerimos
require_once "conexionBBDD.php";

if(!$link){
    // Error conexión
} else {
    // Lista de categorías para filtrar
    $categoria = ["Política","Deportes","Ciencia","España","Economía","Música","Cine","Europa","Justicia","Internacional"];
    
    foreach ($oXML->channel->item as $item){
        $categoriaFiltro = "";
        
        // Lógica de categorías
        for ($i=0; $i<count($item->category); $i++){ 
            for($j=0; $j<count($categoria); $j++){
                // stripos hace la búsqueda insensible a mayúsculas/minúsculas (opcional pero recomendado)
                if(stripos($item->category[$i], $categoria[$j]) !== false){
                    $categoriaFiltro = "[".$categoria[$j]."]".$categoriaFiltro;
                }
            } 
        }

        $fPubli = strtotime($item->pubDate);
        $new_fPubli = date('Y-m-d', $fPubli);

        // Extracción de contenido (El Mundo a veces no trae content:encoded, usamos fallback)
        $content = $item->children("content", true);
        $encoded = (string)$content->encoded; 
        if(empty($encoded)){
            $encoded = (string)$item->description;
        }
        
        // Escapar strings para evitar errores de sintaxis en SQL (PostgreSQL)
        $titulo = pg_escape_string($link, $item->title);
        $linkUrl = pg_escape_string($link, $item->link);
        $descripcion = pg_escape_string($link, $item->description);
        $catFiltro = pg_escape_string($link, $categoriaFiltro);
        $encodedSafe = pg_escape_string($link, $encoded);

        // --- CAMBIO IMPORTANTE: Tabla 'elmundo' ---
        // Comprobar duplicados en la tabla de El Mundo
        $sql = "SELECT link FROM elmundo WHERE link = '$linkUrl'";
        $result = pg_query($link, $sql);
        
        // Si no existe y tiene categoría válida
        if(pg_num_rows($result) == 0 && $categoriaFiltro <> ""){
             
             $sqlInsert = "INSERT INTO elmundo (titulo, link, descripcion, categoria, fpubli, contenido) 
                           VALUES ('$titulo', '$linkUrl', '$descripcion', '$catFiltro', '$new_fPubli', '$encodedSafe')";
             pg_query($link, $sqlInsert);
        } 
    }
}
?>