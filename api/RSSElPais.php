<?php
// api/RSSElPais.php

require_once "conexionRSS.php";
require_once "conexionBBDD.php";

// Intentamos descargar
$sXML = download("http://ep00.epimg.net/rss/elpais/portada.xml");

// --- PROTECCIÓN ---
// Si falla la descarga (porque nos bloquearon o no hay internet), 
// paramos este script aquí, pero NO rompemos la página index.php
if ($sXML === false || empty($sXML)) { return; } 

try {
    libxml_use_internal_errors(true);
    $oXML = new SimpleXMLElement($sXML);
} catch (Exception $e) { return; }

$pdo = obtenerConexion();
if (!$pdo) { return; }

// ... (Aquí sigue tu código de siempre del bucle foreach, no hace falta cambiarlo) ...
// Asegúrate de que el resto del archivo sigue abajo
$categoria = ["Política","Deportes","Ciencia","España","Economía","Música","Cine","Europa","Justicia"];

foreach ($oXML->channel->item as $item) {
    // ... TU LOGICA DE SIEMPRE DE CATEGORIAS ...
    $categoriaFiltro = "";
    if (isset($item->category)) {
        for ($i = 0; $i < count($item->category); $i++) {
            $catActual = (string)$item->category[$i];
            if (in_array($catActual, $categoria)) {
                $categoriaFiltro = "[" . $catActual . "]" . $categoriaFiltro;
            }
        }
    }

    $titulo = (string)$item->title;
    $linkNoticia = (string)$item->link;
    $descripcion = isset($item->description) ? (string)$item->description : "";
    $fPubli = strtotime($item->pubDate);
    $new_fPubli = ($fPubli) ? date('Y-m-d', $fPubli) : date('Y-m-d');
    
    $content = $item->children("content", true);
    $encoded = isset($content->encoded) ? (string)$content->encoded : "";

    // Comprobar y guardar
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM elpais WHERE link = :link");
    $stmt->execute([':link' => $linkNoticia]);
    
    if ($stmt->fetchColumn() == 0 && $categoriaFiltro != "") {
        try {
            $sql = "INSERT INTO elpais (titulo, link, descripcion, categoria, fecha, contenido) VALUES (:t, :l, :d, :c, :f, :cont)";
            $stmtInsert = $pdo->prepare($sql);
            $stmtInsert->execute([
                ':t' => $titulo, ':l' => $linkNoticia, ':d' => $descripcion, 
                ':c' => $categoriaFiltro, ':f' => $new_fPubli, ':cont' => $encoded
            ]);
        } catch (Exception $e) { }
    }
}
?>