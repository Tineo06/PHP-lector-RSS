<?php
require_once "conexionRSS.php";
require_once "turso_execute.php"; // Aquí defines la función turso_execute()

// URL RSS El País
$rssUrlOriginal = "http://ep00.epimg.net/rss/elpais/portada.xml";
$rssUrl = "https://api.allorigins.win/raw?url=" . urlencode($rssUrlOriginal);

// --- DESCARGA ---
$xmlData = download($rssUrl);
if (!$xmlData || strlen($xmlData) < 20) {
    die("Error cargando RSS El País (respuesta vacía)");
}

// --- LIMPIAR ENTIDADES Y HTML ---
$xmlData = html_entity_decode($xmlData, ENT_QUOTES | ENT_XML1, 'UTF-8');
$xmlData = preg_replace('/&[a-z]+;/i', '', $xmlData); 
$xmlData = preg_replace('/&(?!#?[0-9]+;)/', '&amp;', $xmlData); 
$xmlData = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $xmlData);
$xmlData = preg_replace('/<\/?span[^>]*>/', '', $xmlData);
$xmlData = preg_replace('/<\/?div[^>]*>/', '', $xmlData);

// --- PARSEAR XML ---
libxml_use_internal_errors(true);
$xml = simplexml_load_string($xmlData);
if (!$xml || !isset($xml->channel->item)) {
    die("Error interpretando XML El País o no hay items");
}

// --- CATEGORÍAS ---
$categoria = ["Política","Deportes","Ciencia","España","Economía","Música","Cine","Europa","Justicia"];

foreach ($xml->channel->item as $item) {
    $categoriaFiltro = "";
    if (isset($item->category)) {
        foreach ($item->category as $cat) {
            if (in_array((string)$cat, $categoria)) {
                $categoriaFiltro .= "[" . $cat . "]";
            }
        }
    }

    if ($categoriaFiltro == "") continue;

    $fPubli = strtotime($item->pubDate ?? "");
    $newDate = $fPubli ? date("Y-m-d", $fPubli) : date("Y-m-d");

    $content = $item->children("content", true);
    $description = $content->encoded ?? "";

    $titulo = (string)$item->title;
    $linkURL = (string)$item->link;
    $descripcionDB = $description;
    $categoriaDB = $categoriaFiltro;
    $guidDB = (string)($item->guid ?? "");

    // --- Evitar duplicados en Turso ---
    $check = turso_execute("SELECT id FROM elpais WHERE link = ?", [$linkURL]);
    if (empty($check['results'][0])) {
        turso_execute(
            "INSERT INTO elpais (titulo, link, descripcion, categorias, fecha, contenido) VALUES (?, ?, ?, ?, ?, ?)",
            [$titulo, $linkURL, $descripcionDB, $categoriaDB, $newDate, $descripcionDB]
        );
    }
}

echo "RSS de El País procesado correctamente";
?>
