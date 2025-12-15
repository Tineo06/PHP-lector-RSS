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
            .btn { padding: 8px 15px; cursor: pointer; border: none; border-radius: 4px; }
            .btn-blue { background-color: #0070f3; color: white; }
            .btn-green { background-color: #10b981; color: white; text-decoration: none; display:inline-block; }
        </style>
    </head>
    <body>
        
        <div style="margin-bottom: 20px;">
            <a href="actualizar.php" class="btn btn-green">🔄 Descargar Nuevas Noticias</a>
            <small>(Usa esto solo si la lista está vacía)</small>
        </div>

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
            
            <input type="submit" value="Filtrar" class="btn btn-blue">
        </form>
        
        <?php
        require_once "conexionBBDD.php"; 
        
        // 3. Conexión y Consulta
        $pdo = obtenerConexion();
        
        if ($pdo) {
            // Decidir tabla
            $tabla = (isset($_GET['periodicos']) && $_GET['periodicos'] == 'elmundo') ? "elmundo" : "elpais";
            
            // Construir SQL seguro
            $sql = "SELECT * FROM $tabla WHERE 1=1";
            $params = [];

            if (!empty($_GET['categoria'])) {
                $sql .= " AND categoria LIKE :cat";
                $params[':cat'] = "%" . $_GET['categoria'] . "%";
            }
            
            if (!empty($_GET['buscar'])) {
                $sql .= " AND descripcion LIKE :buscar";
                $params[':buscar'] = "%" . $_GET['buscar'] . "%";
            }

            $sql .= " ORDER BY id DESC LIMIT 50"; // Ordenar por ID para ver las últimas

            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                echo "<table>
                        <tr>
                            <th>Título</th>
                            <th>Descripción</th>
                            <th>Categoría</th>
                            <th>Link</th>
                        </tr>";

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Prevenir errores si faltan campos
                    $tit = $row['titulo'] ?? 'Sin título';
                    $desc = $row['descripcion'] ?? '';
                    $cat = $row['categoria'] ?? '';
                    $link = $row['link'] ?? '#';

                    echo "<tr>
                            <td><b>$tit</b></td>
                            <td><small>" . substr($desc, 0, 150) . "...</small></td>
                            <td>$cat</td>
                            <td><a href='$link' target='_blank'>Leer</a></td>
                          </tr>";
                }
                echo "</table>";

            } catch (PDOException $e) {
                echo "<p style='color:red'>Error al leer datos: " . $e->getMessage() . "</p>";
            }
        }
        ?>
    </body>
</html>