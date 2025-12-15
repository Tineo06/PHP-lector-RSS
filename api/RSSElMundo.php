<?php
// api/RSSElMundo.php

require_once "conexionRSS.php";
require_once "conexionBBDD.php";

$url = "https://e00-elmundo.uecdn.es/elmundo/rss/espana.xml";
$sXML = download($url);

if ($sXML === false || empty($sXML)) { return; }

try {
    libxml_use_internal_errors(true);
    $oXML = new SimpleXMLElement($sXML);
} catch (Exception $e) { return; }

$pdo = obtenerConexion();
if (!$pdo) { return; }

$categoria = ["Política","Deportes","Ciencia","España","Economía","Música","Cine","Europa","Justicia"];

foreach ($oXML->channel->item as $item) {
    // 1. Categorías
    $categoriaFiltro = "";
    if (isset($item->category)) {
        for ($i = 0; $i < count($item->category); $i++) {
            $catActual = (string)$item->category[$i];
            if (in_array($catActual, $categoria)) {
                $categoriaFiltro = "[" . $catActual . "]" . $categoriaFiltro;
            }
        }
    }

    // 2. Datos
    $titulo = (string)$item->title;
    $linkNoticia = (string)$item->link;
    $guid = (string)$item->guid;
    $fPubli = strtotime($item->pubDate);
    $new_fPubli = ($fPubli) ? date('Y-m-d', $fPubli) : date('Y-m-d');

    // Descripción (El Mundo a veces usa media:description)
    $media = $item->children("media", true);
    $descripcion = isset($media->description) ? (string)$media->description : (string)$item->description;

    // 3. Comprobar y Guardar
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM elmundo WHERE link = :link");
    $stmt->execute([':link' => $linkNoticia]);
    
    if ($stmt->fetchColumn() == 0 && $categoriaFiltro != "") {
        try {
            $sql = "INSERT INTO elmundo (titulo, link, descripcion, categoria, fecha, guid) VALUES (:t, :l, :d, :c, :f, :g)";
            $stmtInsert = $pdo->prepare($sql);
            $stmtInsert->execute([
                ':t' => $titulo,
                ':l' => $linkNoticia,
                ':d' => $descripcion,
                ':c' => $categoriaFiltro,
                ':f' => $new_fPubli,
                ':g' => $guid
            ]);
        } catch (Exception $e) {
            // Error silencioso
        }
    }
}
?>