<?php

require_once "conexionRSS.php";
require_once "conexionBBDD.php"; // Esto nos da la variable $pdo

// Descarga del Feed
$sXML = download("https://e00-elmundo.uecdn.es/elmundo/rss/espana.xml");
$oXML = new SimpleXMLElement($sXML);

// Lista de categorías para filtrar
$categoriaLista = ["Política","Deportes","Ciencia","España","Economía","Música","Cine","Europa","Justicia"];

foreach ($oXML->channel->item as $item){ 

    // 1. Extracción de datos específicos de El Mundo
    // El Mundo usa namespaces para la descripción (media:description)
    $media = $item->children("media", true);
    $description = (string)$media->description; 
    
    // Si la descripción de media está vacía, intentamos coger la estándar por seguridad
    if (empty($description)) {
        $description = (string)$item->description;
    }

    // 2. Lógica de Filtrado de Categorías
    $categoriaFiltro = "";
    foreach ($item->category as $catXML) {
        // Comparamos cada categoría del XML con nuestra lista blanca
        if (in_array((string)$catXML, $categoriaLista)) {
            $categoriaFiltro .= "[" . $catXML . "]";
        }
    }

    // 3. Formateo de fechas y datos básicos
    $fPubli = strtotime($item->pubDate);
    $new_fPubli = date('Y-m-d', $fPubli);
    
    $titulo = (string)$item->title;
    $linkNoticia = (string)$item->link;
    // En tu código original guardabas el GUID en la columna 'contenido'
    $guid = (string)$item->guid; 

    // -------------------------------------------------------
    // CONSULTA PDO (Compatible con PostgreSQL y MySQL)
    // -------------------------------------------------------

    // A. Comprobar si ya existe el link (Optimizado)
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM elmundo WHERE link = :link");
    $stmtCheck->execute([':link' => $linkNoticia]);
    $existe = $stmtCheck->fetchColumn();

    // B. Insertar si no existe y tiene categorías válidas
    if ($existe == 0 && $categoriaFiltro != "") {
        
        // Usamos sentencias preparadas para seguridad y compatibilidad
        $sql = "INSERT INTO elmundo (titulo, link, descripcion, categoria, fPubli, contenido) 
                VALUES (:titulo, :link, :desc, :cat, :fecha, :cont)";
        
        $stmtInsert = $pdo->prepare($sql);
        
        try {
            $stmtInsert->execute([
                ':titulo' => $titulo,
                ':link'   => $linkNoticia,
                ':desc'   => $description,
                ':cat'    => $categoriaFiltro,
                ':fecha'  => $new_fPubli,
                ':cont'   => $guid // Mantenemos tu lógica original de guardar el GUID en contenido
            ]);
        } catch (PDOException $e) {
            // Silenciamos errores de inserción (ej. duplicados concurrentes)
            // error_log("Error insertando noticia El Mundo: " . $e->getMessage());
        }
    }
}
?>