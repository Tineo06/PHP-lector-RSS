<?php

require_once "conexionRSS.php";
require_once "conexionBBDD.php";

// URL RSS El Mundo
$rssUrlOriginal = "https://e00-elmundo.uecdn.es/elmundo/rss/espana.xml";
$rssUrl = "https://api.allorigins.win/raw?url=" . urlencode($rssUrlOriginal);

// --- DESCARGA ---
$xmlData = download($rssUrl);
if (!$xmlData || strlen($xmlData) < 20) {
    die("Error cargando RSS El Mundo (respuesta vacía)");
}

// --- LIMPIAR ENTIDADES Y HTML ---
$xmlData = html_entity_decode($xmlData, ENT_QUOTES | ENT_XML1, 'UTF-8');
$xmlData = preg_replace('/&[a-z]+;/i', '', $xmlData); // eliminar entidades como &bull;
$xmlData = preg_replace('/&(?!#?[0-9]+;)/', '&amp;', $xmlData); // reemplazar & problemáticos
$xmlData = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $xmlData);
$xmlData = preg_replace('/<\/?span[^>]*>/', '', $xmlData);
$xmlData = preg_replace('/<\/?div[^>]*>/', '', $xmlData);

// --- PARSEAR XML ---
libxml_use_internal_errors(true);
$xml = simplexml_load_string($xmlData);
if (!$xml) {
    die("Error interpretando XML El Mundo");
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

    if ($categoriaFiltro == "") continue;

    $fPubli = strtotime($item->pubDate);
    $newDate = date("Y-m-d", $fPubli);

    $media = $item->children("media", true);
    $description = isset($media->description) ? (string)$media->description : "";

    $titulo = mysqli_real_escape_string($link, (string)$item->title);
    $linkURL = mysqli_real_escape_string($link, (string)$item->link);
    $descripcionDB = mysqli_real_escape_string($link, $description);
    $categoriaDB = mysqli_real_escape_string($link, $categoriaFiltro);
    $guidDB = mysqli_real_escape_string($link, (string)($item->guid ?? ""));

    $sqlCheck = "SELECT id FROM elmundo WHERE link = '$linkURL' LIMIT 1";
    $resCheck = mysqli_query($link, $sqlCheck);
    if (mysqli_num_rows($resCheck) == 0) {
        $sqlInsert = "
            INSERT INTO elmundo (titulo, link, descripcion, categoria, fecha, guid)
            VALUES ('$titulo', '$linkURL', '$descripcionDB', '$categoriaDB', '$newDate', '$guidDB')
        ";
        mysqli_query($link, $sqlInsert);
    }
}

echo "RSS de El Mundo procesado correctamente";

?>
