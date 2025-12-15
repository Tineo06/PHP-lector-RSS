<?php

require_once "conexionRSS.php"; // Asumo que esto tiene la función download()

// Descargar el XML
$sXML = download("https://e00-elmundo.uecdn.es/elmundo/rss/espana.xml");
$oXML = new SimpleXMLElement($sXML);

require_once "conexionBBDD.php";

// Obtenemos la conexión PDO (PostgreSQL)
$pdo = obtenerConexion();

// Definimos categorías
$categoria = ["Política", "Deportes", "Ciencia", "España", "Economía", "Música", "Cine", "Europa", "Justicia"];

foreach ($oXML->channel->item as $item) {
    
    // 1. Lógica de Categorías (Mantenemos tu lógica original)
    $categoriaFiltro = "";
    // Aseguramos que existan categorías antes de recorrerlas
    if (isset($item->category)) {
        for ($i = 0; $i < count($item->category); $i++) {
            $catActual = (string)$item->category[$i]; // Convertir a texto
            for ($j = 0; $j < count($categoria); $j++) {
                if ($catActual == $categoria[$j]) {
                    $categoriaFiltro = "[" . $categoria[$j] . "]" . $categoriaFiltro;
                }
            }
        }
    }

    // 2. Procesar Datos básicos
    $fPubli = strtotime($item->pubDate);
    $new_fPubli = date('Y-m-d', $fPubli);
    $linkNoticia = (string)$item->link;
    $titulo = (string)$item->title;
    $guid = (string)$item->guid;

    // Obtener descripción (Media RSS)
    $media = $item->children("media", true);
    $description = "";
    if (isset($media->description)) {
        $description = (string)$media->description;
    } else {
        // Fallback si no hay media description
        $description = (string)$item->description; 
    }

    // ---------------------------------------------------------
    // 3. COMPROBAR SI YA EXISTE (Versión PDO Optimizada)
    // ---------------------------------------------------------
    // En lugar de traer todo, preguntamos a la BD si este link ya existe
    $sqlCheck = "SELECT COUNT(*) FROM elmundo WHERE link = :link";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([':link' => $linkNoticia]);
    
    // Si el conteo es mayor que 0, ya existe
    $existe = $stmtCheck->fetchColumn() > 0;

    // ---------------------------------------------------------
    // 4. INSERTAR SI NO EXISTE
    // ---------------------------------------------------------
    if (!$existe && $categoriaFiltro != "") {
        
        // IMPORTANTE: En PostgreSQL, si la primera columna es ID autoincremental,
        // NO debes pasar '' (comillas vacías). Mejor especifica las columnas.
        // Ajusta los nombres de columnas (titulo, link, etc) a como los tengas en tu tabla Neon.
        
        $sqlInsert = "INSERT INTO elmundo (titulo, link, descripcion, categoria, fecha, guid) 
                      VALUES (:titulo, :link, :desc, :cat, :fecha, :guid)";
        
        try {
            $stmtInsert = $pdo->prepare($sqlInsert);
            $stmtInsert->execute([
                ':titulo' => $titulo,
                ':link' => $linkNoticia,
                ':desc' => $description,
                ':cat' => $categoriaFiltro,
                ':fecha' => $new_fPubli,
                ':guid' => $guid
            ]);
            
            // Opcional: echo "Noticia insertada: $titulo <br>";
            
        } catch (PDOException $e) {
            echo "Error al insertar: " . $e->getMessage() . "<br>";
        }
    }
    
    // Resetear filtro para la siguiente vuelta
    $categoriaFiltro = "";
}
?>