<?php
// conexionRSS.php ya debe estar incluido o requerirse
require_once "conexionRSS.php";

$sXML = download("https://feeds.elpais.com/mrss-s/pages/ep/site/elpais.com/portada");
$oXML = new SimpleXMLElement($sXML);

// Asumimos que $link ya viene de index.php o lo requerimos
require_once "conexionBBDD.php";

if(!$link){
    // Error conexión
} else {
    $categoria = ["Política","Deportes","Ciencia","España","Economía","Música","Cine","Europa","Justicia"];
    
    foreach ($oXML->channel->item as $item){
        $categoriaFiltro = "";
        
        // Lógica de categorías (se mantiene igual)
        for ($i=0; $i<count($item->category); $i++){ 
            for($j=0; $j<count($categoria); $j++){
                if($item->category[$i] == $categoria[$j]){
                    $categoriaFiltro = "[".$categoria[$j]."]".$categoriaFiltro;
                }
            } 
        }

        $fPubli = strtotime($item->pubDate);
        $new_fPubli = date('Y-m-d', $fPubli);

        $content = $item->children("content", true);
        $encoded = (string)$content->encoded; 
        
        // Escapar strings para evitar errores de sintaxis en SQL
        $titulo = pg_escape_string($link, $item->title);
        $linkUrl = pg_escape_string($link, $item->link);
        $descripcion = pg_escape_string($link, $item->description);
        $catFiltro = pg_escape_string($link, $categoriaFiltro);
        $encodedSafe = pg_escape_string($link, $encoded);

        // Comprobar duplicados
        $sql = "SELECT link FROM elpais WHERE link = '$linkUrl'";
        $result = pg_query($link, $sql);
        
        // Si pg_num_rows es 0, significa que no existe
        if(pg_num_rows($result) == 0 && $categoriaFiltro <> ""){
             // EN POSTGRES, OMITIMOS EL ID SI ES SERIAL (AUTO INCREMENT)
             // Asumiendo que la tabla tiene columnas (titulo, link, descripcion, categoria, fpubli, contenido)
             $sqlInsert = "INSERT INTO elpais (titulo, link, descripcion, categoria, fpubli, contenido) 
                           VALUES ('$titulo', '$linkUrl', '$descripcion', '$catFiltro', '$new_fPubli', '$encodedSafe')";
             pg_query($link, $sqlInsert);
        } 
    }
}
?>
