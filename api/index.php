<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Noticias Vercel</title>
        <style>
            body { font-family: sans-serif; padding: 20px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #E4CCE8; padding: 8px; text-align: left; font-size: 14px; }
            th { background-color: #f2f2f2; color: #333; }
            .filtros { background: #f9f9f9; padding: 15px; border-radius: 8px; border: 1px solid #ddd; }
            label { font-weight: bold; margin-right: 5px; }
            .btn { cursor: pointer; padding: 5px 10px; border-radius: 4px; border: none; font-weight: bold; }
            .btn-filtrar { background-color: #ddd; color: black; }
            .btn-actualizar { background-color: #66E9D9; color: black; text-decoration: none; display: inline-block; margin-left: 15px;}
        </style>
    </head>
    <body>
        
        <form action="index.php" method="GET" class="filtros">
            <fieldset style="border:none;"> 
                <legend style="font-size: 1.2em; font-weight:bold;">FILTRO DE NOTICIAS</legend>
                
                <label>PERIÓDICO:</label>
                <select name="periodicos">
                    <option value="elpais" <?php if(isset($_GET['periodicos']) && $_GET['periodicos'] == 'elpais') echo 'selected'; ?>>El Pais</option>
                    <option value="elmundo" <?php if(isset($_GET['periodicos']) && $_GET['periodicos'] == 'elmundo') echo 'selected'; ?>>El Mundo</option>      
                </select> 
                
                <label>CATEGORÍA:</label>
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
                
                <label>FECHA:</label>
                <input type="date" name="fecha" value="<?php echo isset($_GET['fecha']) ? $_GET['fecha'] : ''; ?>">
                
                <label>BUSCAR:</label>
                <input type="text" name="buscar" placeholder="Palabra clave..." value="<?php echo isset($_GET['buscar']) ? $_GET['buscar'] : ''; ?>">
                
                <br><br>
                <input type="submit" name="filtrar" value="🔍 Filtrar" class="btn btn-filtrar">
                
                <a href="index.php?actualizar=1" class="btn btn-actualizar">🔄 Descargar Nuevas Noticias (RSS)</a>
            </fieldset>
        </form>
        
        <?php
        require_once "conexionBBDD.php"; 

        // ---------------------------------------------------------
        // 1. LÓGICA DE ACTUALIZACIÓN (Solo si pulsamos el botón verde)
        // ---------------------------------------------------------
        if (isset($_GET['actualizar']) && $_GET['actualizar'] == '1') {
            echo "<div style='background:#e6fffa; padding:10px; border:1px solid green; margin:10px 0;'>";
            echo "Conectando con los periódicos...<br>";
            
            // Intentamos cargar los scripts. Si fallan, no rompen toda la web.
            try {
                require_once "RSSElPais.php";
                echo "✅ El País actualizado.<br>";
            } catch (Exception $e) { echo "❌ Error El País: ".$e->getMessage()."<br>"; }

            try {
                require_once "RSSElMundo.php";
                echo "✅ El Mundo actualizado.<br>";
            } catch (Exception $e) { echo "❌ Error El Mundo: ".$e->getMessage()."<br>"; }
            
            echo "<b>Proceso finalizado.</b> <a href='index.php'>Volver a ver noticias</a>";
            echo "</div>";
        }

        // ---------------------------------------------------------
        // 2. LOGICA DE VISUALIZACIÓN (Leer base de datos)
        // ---------------------------------------------------------
        $pdo = obtenerConexion();

        if ($pdo) {
            // A. Determinar qué tabla leer
            $tabla = "elpais"; // Por defecto
            if (isset($_GET['periodicos']) && $_GET['periodicos'] == 'elmundo') {
                $tabla = "elmundo";
            }

            // B. Construir la consulta SQL dinámicamente
            $sql = "SELECT * FROM $tabla WHERE 1=1";
            $params = [];

            // Filtro Categoría
            if (!empty($_GET['categoria'])) {
                $sql .= " AND categoria LIKE :cat";
                $params[':cat'] = "%" . $_GET['categoria'] . "%";
            }

            // Filtro Fecha
            if (!empty($_GET['fecha'])) {
                $sql .= " AND fecha = :fecha";
                $params[':fecha'] = $_GET['fecha'];
            }

            // Filtro Buscar Palabra
            if (!empty($_GET['buscar'])) {
                $sql .= " AND descripcion LIKE :buscar";
                $params[':buscar'] = "%" . $_GET['buscar'] . "%";
            }

            $sql .= " ORDER BY fecha DESC LIMIT 50"; // Limitar a 50 para que no explote

            // C. Ejecutar y Mostrar
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                echo "<table>";
                echo "<tr>
                        <th>TÍTULO</th>
                        <th>CONTENIDO / DESC</th>
                        <th>CATEGORÍA</th>
                        <th>FECHA</th>
                        <th>ENLACE</th>
                      </tr>";

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Prevenir errores si alguna columna viene vacía
                    $titulo = $row['titulo'] ?? 'Sin título';
                    $desc = $row['descripcion'] ?? '';
                    $contenido = $row['contenido'] ?? ''; // El Mundo no tiene contenido, usará vacío
                    $cat = $row['categoria'] ?? '';
                    $link = $row['link'] ?? '#';
                    $fechaRaw = $row['fecha'] ?? null;
                    
                    // Formatear fecha
                    $fechaBonita = "N/A";
                    if($fechaRaw) {
                        $dateObj = date_create($fechaRaw);
                        $fechaBonita = date_format($dateObj, 'd-m-Y');
                    }

                    // Mostrar fila
                    echo "<tr>";
                    echo "<td><b>$titulo</b></td>";
                    echo "<td><small>".substr($desc, 0, 150)."...</small></td>"; // Cortar descripciones largas
                    echo "<td>$cat</td>";
                    echo "<td>$fechaBonita</td>";
                    echo "<td><a href='$link' target='_blank'>Leer</a></td>";
                    echo "</tr>";
                }
                echo "</table>";

            } catch (PDOException $e) {
                echo "<p style='color:red'>Error al leer la base de datos: " . $e->getMessage() . "</p>";
            }
        }
        ?>
    </body>
</html>