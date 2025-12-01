<?php

require_once "conexionRSS.php";

// URL original
$rssUrlOriginal = "http://ep00.epimg.net/rss/elpais/portada.xml";

// Proxy HTTPS
$rssUrl = "https://api.allorigins.win/raw?url=" . urlencode($rssUrlOriginal);

// --- DESCARGA ---
$xmlData = download($rssUrl);

if (!$xmlData || strlen($xmlData) < 20) {
    die("Error cargando RSS El País (respuesta vacía)");
}

// --- LIMPIAR HTML ANTES DE PARSEAR XML ---
$xmlData = html_entity_decode($xmlData, ENT_QUOTES | ENT_XML1, 'UTF-8');
$xmlData = preg_replace('/&([a-zA-Z]+);/', '', $xmlData);  // elimina entidades no definidas (&bull;, &nbsp;, etc)
$xmlData = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $xmlData); // elimina scripts
$xmlData = preg_replace('/<\/?span[^>]*>/', '', $xmlData); // elimina spans
$xmlData = preg_replace('/<\/?div[^>]*>/', '', $xmlData);  // elimina divs

// --- PARSEAR ---
$oXML = simplexml_load_string($xmlData);

if (!$oXML) {
    die("Error interpretando XML El País (XML no válido)");
}

require_once "conexionBBDD.php";

if(mysqli_connect_error()){
    die("Conexión a la BBDD ha fallado");
}

$contador = 0;

$categoria = ["Política","Deportes","Ciencia","España","Economía","Música","Cine","Europa","Justicia"];

foreach ($oXML->channel->item as $item){

    $categoriaFiltro = "";

    // Categorías
    foreach ($item->category as $cat) {
        if (in_array((string)$cat, $categoria)) {
            $categoriaFiltro .= "[" . $cat . "]";
        }
    }

    // Fecha
    $fPubli = strtotime($item->pubDate);
    $new_fPubli = date('Y-m-d', $fPubli);

    // Contenido completo
    $content = $item->children("content", true);
    $encoded = $content->encoded ?? "";
    
    // Limpiar contenido HTML que NO es apto para XML
    $encoded = mysqli_real_escape_string($link, $encoded);

    // Comprobar duplicado
    $linkURL = mysqli_real_escape_string($link, $item->link);
    $sql = "SELECT id FROM elpais WHERE link = '$linkURL' LIMIT 1";
    $result = mysqli_query($link, $sql);

    if (mysqli_num_rows($result) == 0 && $categoriaFiltro != "") {

        $titulo = mysqli_real_escape_string($link, $item->title);
        $descripcion = mysqli_real_escape_string($link, $item->description);

        $sqlInsert = "
            INSERT INTO elpais (titulo, link, descripcion, categorias, fecha, contenido)
            VALUES ('$titulo', '$linkURL', '$descripcion', '$categoriaFiltro', '$new_fPubli', '$encoded')
        ";

        mysqli_query($link, $sqlInsert);
    }

}

echo "RSS de El País procesado correctamente";

?>
