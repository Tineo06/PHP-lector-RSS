<?php

require_once "conexionRSS.php";
require_once "conexionBBDD.php";

// URL RSS El País
$rssUrlOriginal = "http://ep00.epimg.net/rss/elpais/portada.xml";
$rssUrl = "https://api.allorigins.win/raw?url=" . urlencode($rssUrlOriginal);

// --- DESCARGA ---
$xmlData = download($rssUrl);
if (!$xmlData || strlen($xmlData) < 20) {
    die("Error cargando RSS El País (respuesta vacía)");
}

// --- LIMPIAR HTML Y ENTIDADES ---
$xmlData = html_entity_decode($xmlData, ENT_QUOTES | ENT_XML1, 'UTF-8');
$xmlData = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $xmlData);
$xmlData = preg_replace('/<\/?span[^>]*>/', '', $xmlData);
$xmlData = preg_replace('/<\/?div[^>]*>/', '', $xmlData);
// Reemplazar & problemáticos
$xmlData = preg_replace('/&(?!#?[a-z0-9]+;)/i', '&amp;', $xmlData);

// --- PARSEAR XML ---
libxml_use_internal_errors(true);
$xml = simplexml_load_string($xmlData);
if (!$xml) {
    echo "Error interpretando XML El País:\n";
    foreach (libxml_get_errors() as $error) {
        echo $error->message . "\n";
    }
    exit;
}

// --- CATEGORÍAS ---
$categoria = ["Política","Deportes","Ciencia","España","Economía","Música","Cine","Europa","Justicia"];

foreach ($xml->channel->item as $item) {
    $categoriaFiltro = "";
    foreach ($item->category as $cat) {
        if (in_array((string)$cat, $categoria)) {
            $categoriaFiltro .= "[" . $cat . "]";
        }
    }

    $fPubli = strtotime($item->pubDate);
    $newDate = date("Y-m-d", $fPubli);

    $content = $item->children("content", true);
    $description = $content->encoded ?? "";

    // Escapar para la base de datos
    $titulo = mysqli_real_escape_string($link, (string)$item->title);
    $linkURL = mysqli_real_escape_string($link, (string)$item->link);
    $descripcionDB = mysqli_real_escape_string($link, $description);
    $categoriaDB = mysqli_real_escape_string($link, $categoriaFiltro);
    $guidDB = mysqli_real_escape_string($link, (string)($item->guid ?? ""));

    // Evitar duplicados
    $sqlCheck = "SELECT id FROM elpais WHERE link = '$linkURL' LIMIT 1";
    $resCheck = mysqli_query($link, $sqlCheck);

    if (mysqli_num_rows($resCheck) == 0 && $categoriaFiltro !== "") {
        $sqlInsert = "
            INSERT INTO elpais (titulo, link, descripcion, categorias, fecha, contenido)
            VALUES ('$titulo', '$linkURL', '$descripcionDB', '$categoriaDB', '$newDate', '$descripcionDB')
        ";
        mysqli_query($link, $sqlInsert);
    }
}

echo "RSS de El País procesado correctamente";

?>
