<?php
// 1. Cargar funciones de descarga y conexión
require_once "conexionRSS.php";
require_once "conexionBBDD.php";

// 2. Descargar RSS de El Mundo
$sXML = download("https://e00-elmundo.uecdn.es/elmundo/rss/portada.xml");
$oXML = new SimpleXMLElement($sXML);

// 3. Verificar conexión a BBDD (variable $link viene de conexionBBDD.php)
if(!$link){
    echo "Error: No hay conexión a la base de datos.";
} else {
    // Lista de palabras clave a buscar
    $categoria = ["Política","Deportes","Ciencia","España","Economía","Música","Cine","Europa","Justicia","Internacional"];
    
    foreach ($oXML->channel->item as $item){
        $categoriaFiltro = "";
        
        // --- NUEVA LÓGICA DE FILTRADO (BUSCA EN TODO) ---
        // Juntamos todo el texto disponible de la noticia
        $textoTags = implode(" ", (array)$item->category);
        $textoCompleto = $item->title . " " . $item->description . " " . $textoTags;
        
        // Buscamos cada palabra clave en el texto completo
        foreach($categoria as $palabra){
            // stripos busca sin importar mayúsculas/minúsculas
            if(stripos($textoCompleto, $palabra) !== false){
                // Solo añadimos la etiqueta si no está ya puesta
                if(strpos($categoriaFiltro, "[".$palabra."]") === false){
                    $categoriaFiltro .= "[".$palabra."]";
                }
            }
        }
        // ------------------------------------------------

        // Preparar fecha
        $fPubli = strtotime($item->pubDate);
        $new_fPubli = date('Y-m-d', $fPubli);

        // Preparar contenido (El Mundo a veces no trae 'encoded', usamos fallback)
        $content = $item->children("content", true);
        $encoded = (string)$content->encoded; 
        if(empty($encoded)){
            $encoded = (string)$item->description;
        }
        
        // Limpiar/Escapar datos para PostgreSQL
        $titulo = pg_escape_string($link, $item->title);
        $linkUrl = pg_escape_string($link, $item->link);
        $descripcion = pg_escape_string($link, $item->description);
        $catFiltro = pg_escape_string($link, $categoriaFiltro);
        $encodedSafe = pg_escape_string($link, $encoded);

        // --- INSERTAR EN BBDD ---
        // 1. Comprobar si ya existe
        $sql = "SELECT link FROM elmundo WHERE link = '$linkUrl'";
        $result = pg_query($link, $sql);
        
        // 2. Si no existe (0 filas) Y hemos encontrado alguna categoría
        if(pg_num_rows($result) == 0 && $categoriaFiltro != ""){
             
             $sqlInsert = "INSERT INTO elmundo (titulo, link, descripcion, categoria, fpubli, contenido) 
                           VALUES ('$titulo', '$linkUrl', '$descripcion', '$catFiltro', '$new_fPubli', '$encodedSafe')";
             
             $insertResult = pg_query($link, $sqlInsert);
             
             // Opcional: Ver si hubo error al insertar
             if(!$insertResult) {
                 echo "Error al insertar: " . pg_last_error($link) . "<br>";
             }
        } 
    }
    echo "Proceso terminado. Noticias de El Mundo actualizadas.";
}
?>