<?php
// api/RSSElPais.php

require_once "conexionRSS.php";
require_once "conexionBBDD.php";

$url = "https://feeds.elpais.com/mrss-s/pages/ep/site/elpais.com/portada";
$sXML = download($url);

// Si falla la descarga, no hacemos nada (evita errores fatales)
if ($sXML === false || empty($sXML)) { return; }

try {
    // Suprimir advertencias XML temporales
    libxml_use_internal_errors(true);
    $oXML = new SimpleXMLElement($sXML);
} catch (Exception $e) { return; }

$pdo = obtenerConexion();
if (!$pdo) { return; }

$categoria = ["Política","Deportes","Ciencia","España","Economía","Música","Cine","Europa","Justicia"];

foreach ($oXML->channel->item as $item) {
    // 1. Filtrar Categorías
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
    $descripcion = isset($item->description) ? (string)$item->description : "";
    $fPubli = strtotime($item->pubDate);
    $new_fPubli = ($fPubli) ? date('Y-m-d', $fPubli) : date('Y-m-d');
    
    // Content Encoded (Namespace)
    $content = $item->children("content", true);
    $encoded = isset($content->encoded) ? (string)$content->encoded : "";

    // 3. Comprobar si existe y guardar
    // Usamos PDO para comprobar duplicados
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM elpais WHERE link = :link");
    $stmt->execute([':link' => $linkNoticia]);
    
    // Solo insertamos si no existe y tiene categoría válida
    if ($stmt->fetchColumn() == 0 && $categoriaFiltro != "") {
        try {
            $sql = "INSERT INTO elpais (titulo, link, descripcion, categoria, fecha, contenido) VALUES (:t, :l, :d, :c, :f, :cont)";
            $stmtInsert = $pdo->prepare($sql);
            $stmtInsert->execute([
                ':t' => $titulo,
                ':l' => $linkNoticia,
                ':d' => $descripcion,
                ':c' => $categoriaFiltro,
                ':f' => $new_fPubli,
                ':cont' => $encoded
            ]);
        } catch (Exception $e) {
            // Error silencioso al insertar
        }
    }
}
?>
