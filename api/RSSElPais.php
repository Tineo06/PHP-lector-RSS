<?php
require_once "conexionRSS.php";
require_once "conexionBBDD.php"; // Nos da $pdo

$sXML = download("http://ep00.epimg.net/rss/elpais/portada.xml");
$oXML = new SimpleXMLElement($sXML);

$categoriaLista = ["Política","Deportes","Ciencia","España","Economía","Música","Cine","Europa","Justicia"];

foreach ($oXML->channel->item as $item){
    
    $categoriaFiltro = "";
    // Lógica de categorías (simplificada para PDO)
    foreach ($item->category as $catXML) {
        if (in_array((string)$catXML, $categoriaLista)) {
            $categoriaFiltro .= "[" . $catXML . "]";
        }
    }

    $fPubli = strtotime($item->pubDate);
    $new_fPubli = date('Y-m-d', $fPubli);
    $content = $item->children("content", true);
    $encoded = (string)$content->encoded;
    $linkNoticia = (string)$item->link;
    $titulo = (string)$item->title;
    $desc = (string)$item->description;

    // 1. Verificar si existe (usando PDO)
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM elpais WHERE link = :link");
    $stmtCheck->execute([':link' => $linkNoticia]);
    $existe = $stmtCheck->fetchColumn();

    if ($existe == 0 && $categoriaFiltro != "") {
        // 2. Insertar si no existe
        $sql = "INSERT INTO elpais (titulo, link, descripcion, categoria, fPubli, contenido) VALUES (:titulo, :link, :desc, :cat, :fecha, :cont)";
        $stmtInsert = $pdo->prepare($sql);
        try {
            $stmtInsert->execute([
                ':titulo' => $titulo,
                ':link' => $linkNoticia,
                ':desc' => $desc,
                ':cat' => $categoriaFiltro,
                ':fecha' => $new_fPubli,
                ':cont' => $encoded
            ]);
        } catch (PDOException $e) {
            // Ignorar errores de duplicados si ocurren
        }
    }
}
?>