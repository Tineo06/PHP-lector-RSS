<?php

require_once "conexionRSS.php";
require_once "conexionBBDD.php";   // usa Turso vía HTTP

$rssUrl = "https://api.allorigins.win/raw?url=" . urlencode("https://e00-elmundo.uecdn.es/elmundo/rss/espana.xml");
$xmlData = download($rssUrl);
$xml = simplexml_load_string($xmlData);

if (!$xml) {
    echo "Error leyendo RSS";
    exit;
}

$categoria = ["Política","Deportes","Ciencia","España","Economía","Música","Cine","Europa","Justicia"];
$categoriaFiltro = "";

foreach ($xml->channel->item as $item) {

    // Categorías
    foreach ($item->category as $cat) {
        if (in_array((string)$cat, $categoria)) {
            $categoriaFiltro .= "[" . $cat . "]";
        }
    }

    // Fecha
    $fPubli = strtotime($item->pubDate);
    $newDate = date("Y-m-d", $fPubli);

    // Descripción (media:description)
    $media = $item->children("media", true);
    $description = isset($media->description) ? (string)$media->description : "";

    // Evitar duplicados por link
    $exists = dbQuery("SELECT link FROM elmundo WHERE link = ?", [(string)$item->link]);

    $already = false;
    if (is_array($exists)) {
        $flat = json_encode($exists);
        if (preg_match('/"link"\s*:\s*"([^"]+)"/', $flat)) {
            $already = true;
        }
    }

    if (!$already && $categoriaFiltro !== "") {

        dbQuery(
            "INSERT INTO elmundo (titulo, link, descripcion, categoria, fecha, guid) VALUES (?, ?, ?, ?, ?, ?)",
            [
                (string)$item->title,
                (string)$item->link,
                $description,
                $categoriaFiltro,
                $newDate,
                (string)$item->guid
            ]
        );

    }

    $categoriaFiltro = "";
}

echo "Proceso completado";
