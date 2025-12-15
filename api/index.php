<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Noticias</title>
        <style>
            body { font-family: sans-serif; padding: 20px; }
            .filtros { background: #f4f4f4; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #333; color: white; }
            input[type="submit"] { background-color: #0070f3; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        </style>
    </head>
    <body>

        <?php
        // 1. INTENTAMOS ACTUALIZAR (Silenciosamente)
        // Esto se ejecutará cada vez que cargues la página.
        // Si hay error 403, los archivos RSS se detienen solos y el código sigue hacia abajo.
        require_once "conexionBBDD.php"; 
        
        // Usamos 'include' en vez de 'require' para que si el archivo falla fatalmente, no tumbe la web
        include "RSSElPais.php";
        include "RSSElMundo.php";
        ?>

        <form action="index.php" method="GET" class="filtros">
            <label>Periódico:</label>
            <select name="periodicos">
                <option value="elpais" <?php if(isset($_GET['periodicos']) && $_GET['periodicos'] == 'elpais') echo 'selected'; ?>>El Pais</option>
                <option value="elmundo" <?php if(isset($_GET['periodicos']) && $_GET['periodicos'] == 'elmundo') echo 'selected'; ?>>El Mundo</option>      
            </select> 

            <label>Categoría:</label>
            <select name="categoria">
                <option value="">Todas</option>
                <option value="Política">Política</option>
                <option value="Deportes">Deportes</option>
                <option value="Ciencia">Ciencia</option>
                <option value="España">España</option>
                <option value="Economía">Economía</option>
                <option value="Música">Música</option>
                <option value="Cine">Cine</option>
                <option value="Europa">Europa</option>
                <option value="Justicia">Justicia</option>                
            </select>
            
            <label>Palabra:</label>
            <input type="text" name="buscar" value="<?php echo isset($_GET['buscar']) ? $_GET['buscar'] : ''; ?>">
            
            <input type="submit" value="Filtrar y Actualizar">
        </form>
        
        <?php
        // 3. MOSTRAR RESULTADOS (Siempre funciona, haya RSS o no)
        $pdo = obtenerConexion();
        
        if ($pdo) {
            $tabla = (isset($_GET['periodicos']) && $_GET['periodicos'] == 'elmundo') ? "elmundo" : "elpais";
            
            $sql = "SELECT * FROM $tabla WHERE 1=1";
            $params = [];

            if (!empty($_GET['categoria'])) {
                $sql .= " AND categoria LIKE :cat";
                $params[':cat'] = "%" . $_GET['categoria'] . "%";
            }
            if (!empty($_GET['fecha'])) { // Si usas fecha
                $sql .= " AND fecha = :fecha";
                $params[':fecha'] = $_GET['fecha'];
            }
            if (!empty($_GET['buscar'])) {
                $sql .= " AND descripcion LIKE :buscar";
                $params[':buscar'] = "%" . $_GET['buscar'] . "%";
            }

            $sql .= " ORDER BY fecha DESC LIMIT 50";

            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                echo "<table>
                        <tr><th>Título</th><th>Descripción</th><th>Categoría</th><th>Fecha</th><th>Link</th></tr>";

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $tit = $row['titulo'] ?? '';
                    $desc = $row['descripcion'] ?? '';
                    $cat = $row['categoria'] ?? '';
                    $fecha = $row['fecha'] ?? '';
                    $link = $row['link'] ?? '#';

                    echo "<tr>
                            <td>$tit</td>
                            <td><small>" . substr($desc, 0, 100) . "...</small></td>
                            <td>$cat</td>
                            <td>$fecha</td>
                            <td><a href='$link' target='_blank'>Leer</a></td>
                          </tr>";
                }
                echo "</table>";

            } catch (PDOException $e) {
                echo "Error BD: " . $e->getMessage();
            }
        }
        ?>
    </body>
</html>