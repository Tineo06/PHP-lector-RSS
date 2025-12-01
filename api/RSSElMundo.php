<?php

require_once "conexionRSS.php";
require_once "conexionBBDD.php";   // usa Turso vía HTTP

// URL RSS original
$rssUrlOriginal = "https://e00-elmundo.uecdn.es/elmundo/rss/espana.xml";

// Proxy HTTPS
$rssUrl = "https://api.allorigins.win/raw?url=" . urlencode($rssUrlOriginal);

// --- DESCARGA ---
$xmlData = download($rssUrl);

if (!$xmlData || strlen($xmlData) < 20) {
    die("Error cargando RSS El Mundo (respuesta vacía)");
}

// --- LIMPIAR HTML ANTES DE PARSEAR XML ---
$xmlData = html_entity_decode($xmlData, ENT_QUOTES | ENT_XML1, 'UTF-8');
$xmlData = preg_replace('/&([a-zA-Z]+);/', '', $xmlData);  // elimina entidades no definidas (&bull;, &nbsp, etc)
$xmlData = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $xmlData); // elimina scripts
$xmlData = preg_replace('/<\/?span[^>]*>/', '', $xmlData); // elimina spans
$xmlData = preg_replace('/<\/?div[^>]*>/', '', $xmlData);  // elimina divs

// --- PARSEAR ---
$xml = simplexml_load_string($xmlData);

if (!$xml) {
    die("Error interpretando XML El Mundo (XML no válido)");
}

// Categorías a filtrar
$categoria = ["Política","Deportes","Ciencia","España","Economía","Música","Cine","Europa","Justicia"];

foreach ($xml->channel->item as $item) {

    $categoriaFiltro = "";

    // --- FILTRAR CATEGORÍAS ---
    foreach ($item->category as $cat) {
        if (in_array((string)$cat, $categoria)) {
            $categoriaFiltro .= "[" . $cat . "]";
        }
    }

    // --- FECHA ---
    $fPubli = strtotime($item->pubDate);
    $newDate = date("Y-m-d", $fPubli);

    // --- DESCRIPCIÓN ---
    $media = $item->children("media", true);
    $description = isset($media->description) ? (string)$media->description : "";
    
    // Escapar contenido para la base de datos
    $titulo = mysqli_real_escape_string($link, $item->title);
    $linkURL = mysqli_real_escape_string($link, $item->link);
    $descripcion = mysqli_real_escape_string($link, $description);
    $categoriaDB = mysqli_real_escape_string($link, $categoriaFiltro);
    $guidDB = mysqli_real_escape_string($link, $item->guid);

    // --- COMPROBAR DUPLICADO ---
    $sqlCheck = "SELECT id FROM elmundo WHERE link = '$linkURL' LIMIT 1";
    $resCheck = mysqli_query($link, $sqlCheck);

    if (mysqli_num_rows($resCheck) == 0 && $categoriaFiltro !== "") {

        $sqlInsert = "
            INSERT INTO elmundo (titulo, link, descripcion, categoria, fecha, guid)
            VALUES ('$titulo', '$linkURL', '$descripcion', '$categoriaDB', '$newDate', '$guidDB')
        ";

        mysqli_query($link, $sqlInsert);
    }

}

echo "RSS de El Mundo procesado correctamente";

?>
