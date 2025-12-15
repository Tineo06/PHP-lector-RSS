<?php

require_once "conexionRSS.php"; // Tu función de descarga
require_once "conexionBBDD.php"; // La conexión a Neon

// 1. Descargar y procesar XML
$sXML = download("http://ep00.epimg.net/rss/elpais/portada.xml");
$oXML = new SimpleXMLElement($sXML);

// 2. Obtener conexión PDO
$pdo = obtenerConexion();

// Definición de categorías
$categoria = ["Política","Deportes","Ciencia","España","Economía","Música","Cine","Europa","Justicia"];

foreach ($oXML->channel->item as $item) {

    // --- A. LÓGICA DE FILTRADO DE CATEGORÍAS ---
    $categoriaFiltro = "";
    
    // Verificamos si hay categorías antes de iterar
    if (isset($item->category)) {
        for ($i = 0; $i < count($item->category); $i++) {
            $catActual = (string)$item->category[$i];
            
            for ($j = 0; $j < count($categoria); $j++) {
                if ($catActual == $categoria[$j]) {
                    $categoriaFiltro = "[" . $categoria[$j] . "]" . $categoriaFiltro;
                }
            }
        }
    }

    // --- B. PREPARAR DATOS ---
    $fPubli = strtotime($item->pubDate);
    $new_fPubli = date('Y-m-d', $fPubli);
    
    $titulo = (string)$item->title;
    $linkNoticia = (string)$item->link;
    $descripcion = (string)$item->description;

    // Manejo especial para "content:encoded" (El País suele usar namespaces)
    $content = $item->children("content", true);
    $encoded = "";
    if (isset($content->encoded)) {
        $encoded = (string)$content->encoded;
    }

    // --- C. COMPROBAR DUPLICADOS (Versión Optimizada PDO) ---
    // Consultamos solo si existe ESTE enlace concreto
    $sqlCheck = "SELECT COUNT(*) FROM elpais WHERE link = :link";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([':link' => $linkNoticia]);
    
    // Si devuelve más de 0, es que ya existe
    $existe = $stmtCheck->fetchColumn() > 0;

    // --- D. INSERTAR EN LA BASE DE DATOS ---
    if (!$existe && $categoriaFiltro != "") {
        
        // Ajusta los nombres de las columnas según tu tabla en Neon
        // Nota: NO pasamos el ID ('') para que Postgres use el autoincremental (SERIAL)
        $sqlInsert = "INSERT INTO elpais (titulo, link, descripcion, categoria, fecha, contenido) 
                      VALUES (:titulo, :link, :desc, :cat, :fecha, :content)";
        
        try {
            $stmtInsert = $pdo->prepare($sqlInsert);
            $stmtInsert->execute([
                ':titulo' => $titulo,
                ':link'   => $linkNoticia,
                ':desc'   => $descripcion,
                ':cat'    => $categoriaFiltro,
                ':fecha'  => $new_fPubli,
                ':content'=> $encoded // Asumo que guardas el 'encoded' en una columna llamada 'contenido' o similar
            ]);
            
        } catch (PDOException $e) {
            // Error silencioso o log
            // echo "Error: " . $e->getMessage();
        }
    }
}
?>