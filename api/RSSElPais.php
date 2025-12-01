<?php

require_once "conexionRSS.php";
require_once "conexionBBDD.php";  // usa Turso

$rssUrl = "https://api.allorigins.win/raw?url=" . urlencode("http://ep00.epimg.net/rss/elpais/portada.xml");
$xmlData = download($rssUrl);
$xml = simplexml_load_string($xmlData);

if (!$xml) {
    echo "Error cargando RSS El País";
    exit;
}

$categoria = ["Política","Deportes","Ciencia","España","Economía","Música","Cine","Europa","Justicia"];
$categoriaFiltro = "";

foreach ($xml->channel->item as $item) {

    // Buscar categorías
    foreach ($item->category as $cat) {
        if (in_array((string)$cat, $categoria)) {
            $categoriaFiltro .= "[" . $cat . "]";
        }
    }

    // Fecha formateada
    $fPubli = strtotime($item->pubDate);
    $newDate = date("Y-m-d", $fPubli);

    // contenido
    $content = $item->children("content", true);
    $encoded = isset($content->encoded) ? (string)$content->encoded : "";

    // Evitar duplicados
    $exists = dbQuery(
        "SELECT link FROM elpais WHERE link = ?",
        [(string)$item->link]
    );

    $already = false;
    if (is_array($exists)) {
        // buscamos si aparece el link
        $flat = json_encode($exists);
        if (strpos($flat, (string)$item->link) !== false) {
            $already = true;
        }
    }

    // Insertar si no existe
    if (!$already && $categoriaFiltro !== "") {

        dbQuery(
            "INSERT INTO elpais (titulo, link, descripcion, categoria, fecha, encoded)
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                (string)$item->title,
                (string)$item->link,
                (string)$item->description,
                $categoriaFiltro,
                $newDate,
                $encoded
            ]
        );
    }

    // Reset categorías
    $categoriaFiltro = "";
}

echo "El País actualizado correctamente.";
