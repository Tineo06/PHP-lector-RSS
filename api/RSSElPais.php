<?php
// 1. Cargas
require_once "conexionRSS.php";
require_once "conexionBBDD.php";

// 2. Descargar RSS de El País
$sXML = download("http://ep00.epimg.net/rss/elpais/portada.xml");
$oXML = new SimpleXMLElement($sXML);

// 3. Verificar conexión
if(!$link){
    echo "Error: No hay conexión a la base de datos.";
} else {
    // Palabras clave
    $categoria = ["Política","Deportes","Ciencia","España","Economía","Música","Cine","Europa","Justicia"];
    
    foreach ($oXML->channel->item as $item){
        $categoriaFiltro = "";
        
        // --- FILTRADO INTELIGENTE (Busca en todo el texto) ---
        $textoTags = implode(" ", (array)$item->category);
        // Concatenamos Título + Descripción + Categorías
        $textoCompleto = $item->title . " " . $item->description . " " . $textoTags;
        
        foreach($categoria as $palabra){
            // Si la palabra clave aparece en cualquier parte del texto...
            if(stripos($textoCompleto, $palabra) !== false){
                if(strpos($categoriaFiltro, "[".$palabra."]") === false){
                    $categoriaFiltro .= "[".$palabra."]";
                }
            }
        }
        // -----------------------------------------------------

        $fPubli = strtotime($item->pubDate);
        $new_fPubli = date('Y-m-d', $fPubli);

        // Contenido de El País (suele venir bien en content:encoded)
        $content = $item->children("content", true);
        $encoded = (string)$content->encoded;
        if(empty($encoded)){
            $encoded = (string)$item->description;
        }

        // Escapar datos
        $titulo = pg_escape_string($link, $item->title);
        $linkUrl = pg_escape_string($link, $item->link);
        $descripcion = pg_escape_string($link, $item->description);
        $catFiltro = pg_escape_string($link, $categoriaFiltro);
        $encodedSafe = pg_escape_string($link, $encoded);

        // --- TABLA 'elpais' ---
        // 1. Comprobar duplicados
        $sql = "SELECT link FROM elpais WHERE link = '$linkUrl'";
        $result = pg_query($link, $sql);
        
        // 2. Insertar si es nuevo y tiene categoría
        if(pg_num_rows($result) == 0 && $categoriaFiltro != ""){
             
             $sqlInsert = "INSERT INTO elpais (titulo, link, descripcion, categoria, fpubli, contenido) 
                           VALUES ('$titulo', '$linkUrl', '$descripcion', '$catFiltro', '$new_fPubli', '$encodedSafe')";
             
             pg_query($link, $sqlInsert);
        } 
    }
    echo "Proceso terminado. Noticias de El País actualizadas.";
}
?>