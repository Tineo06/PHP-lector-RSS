<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Noticias</title>
        <style>
            body { font-family: sans-serif; padding: 20px; background-color: #fce4ec; }
            .filtros { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 20px; border: 1px solid #E4CCE8;}
            table { width: 100%; border-collapse: collapse; background: white; }
            th, td { border: 1px solid #E4CCE8; padding: 12px; text-align: left; }
            th { background-color: #f3e5f5; color: #4a148c; }
            .btn { padding: 10px 15px; cursor: pointer; border: none; border-radius: 5px; font-weight: bold; }
            .btn-filtrar { background-color: #ba68c8; color: white; }
            .btn-actualizar { background-color: #80cbc4; color: white; text-decoration: none; display: inline-block; margin-bottom: 15px;}
            a { color: #ba68c8; text-decoration: none; }
        </style>
    </head>
    <body>

        <?php
        require_once "conexionBBDD.php"; 
        
        if (isset($_GET['actualizar'])) {
            echo "<p style='color: grey;'>⏳ Intentando descargar noticias...</p>";
            
            // Usamos include para que si fallan por bloqueo, el script siga vivo
            include "RSSElPais.php";
            include "RSSElMundo.php";
            
            echo "<p style='color: green;'>✅ Proceso finalizado. (Si no ves noticias nuevas, intenta en unos minutos).</p>";
        }
        ?>
        
        <a href="index.php?actualizar=1" class="btn btn-actualizar">⬇️ Descargar Nuevas Noticias</a>

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
            
            <label>Fecha:</label>
            <input type="date" name="fecha" value="<?php echo isset($_GET['fecha']) ? $_GET['fecha'] : ''; ?>">

            <label>Buscar:</label>
            <input type="text" name="buscar" placeholder="Palabra clave..." value="<?php echo isset($_GET['buscar']) ? $_GET['buscar'] : ''; ?>">
            
            <input type="submit" value="Filtrar Resultados" class="btn btn-filtrar">
        </form>
        
        <?php
        // PARTE 3: MOSTRAR TABLA (PostgreSQL)
        $pdo = obtenerConexion();
        
        if ($pdo) {
            // 1. Elegir tabla
            $tabla = (isset($_GET['periodicos']) && $_GET['periodicos'] == 'elmundo') ? "elmundo" : "elpais";
            
            // 2. SQL
            $sql = "SELECT * FROM $tabla WHERE 1=1";
            $params = [];

            if (!empty($_GET['categoria'])) {
                $sql .= " AND categoria ILIKE :cat";
                $params[':cat'] = "%" . $_GET['categoria'] . "%";
            }
            if (!empty($_GET['fecha'])) {
                $sql .= " AND fecha = :fecha";
                $params[':fecha'] = $_GET['fecha'];
            }
            if (!empty($_GET['buscar'])) {
                $sql .= " AND descripcion ILIKE :buscar";
                $params[':buscar'] = "%" . $_GET['buscar'] . "%";
            }

            $sql .= " ORDER BY fecha DESC LIMIT 50";

            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                echo "<table>
                        <tr>
                            <th>TÍTULO</th>
                            <th>DESCRIPCIÓN</th>
                            <th>CATEGORÍA</th>
                            <th>FECHA</th>
                            <th>ENLACE</th>
                        </tr>";

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $tit = $row['titulo'] ?? '';
                    $desc = $row['descripcion'] ?? '';
                    $cat = $row['categoria'] ?? '';
                    $fecha = $row['fecha'] ?? '';
                    $link = $row['link'] ?? '#';

                    echo "<tr>
                            <td><b>$tit</b></td>
                            <td>$desc</td>
                            <td>$cat</td>
                            <td>$fecha</td>
                            <td><a href='$link' target='_blank' style='color:#ba68c8'>Leer</a></td>
                          </tr>";
                }
                echo "</table>";

            } catch (PDOException $e) {
                echo "<p>Error BD: " . $e->getMessage() . "</p>";
            }
        }
        ?>
    </body>
</html>