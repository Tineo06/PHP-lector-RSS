<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Noticias</title>
        <style>
            body { font-family: sans-serif; padding: 20px; background-color: #fce4ec; }
            form { background: #fff; padding: 20px; border-radius: 10px; border: 1px solid #E4CCE8; margin-bottom: 20px;}
            table { width: 100%; border-collapse: collapse; background: white; margin-top: 10px;}
            th, td { border: 1px solid #E4CCE8; padding: 10px; text-align: left; }
            th { background-color: #f3e5f5; color: #4a148c; }
            input[type="submit"] { background-color: #ba68c8; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 5px;}
            input[type="submit"]:hover { background-color: #9c27b0; }
        </style>
    </head>
    <body>

        <form action="index.php" method="GET">
            <fieldset style="border:none;"> 
                <legend style="color: #4a148c; font-weight:bold;">FILTRO</legend>
                
                <label>PERIODICO : </label>
                <select name="periodicos">
                    <option value="elpais" <?php if(isset($_GET['periodicos']) && $_GET['periodicos'] == 'elpais') echo 'selected'; ?>>El Pais</option>
                    <option value="elmundo" <?php if(isset($_GET['periodicos']) && $_GET['periodicos'] == 'elmundo') echo 'selected'; ?>>El Mundo</option>      
                </select> 
                
                <label>CATEGORIA : </label>
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
                
                <label>FECHA : </label>
                <input type="date" name="fecha" value="<?php echo isset($_GET['fecha']) ? $_GET['fecha'] : ''; ?>">
                
                <label>BUSCAR : </label>
                <input type="text" name="buscar" placeholder="Palabra clave..." value="<?php echo isset($_GET['buscar']) ? $_GET['buscar'] : ''; ?>">
                
                <input type="submit" name="filtrar" value="Filtrar">
            </fieldset>
        </form>
        
        <?php
        require_once "conexionBBDD.php"; 
        
        // 1. Conexión a Neon (PostgreSQL)
        $pdo = obtenerConexion();
        
        if(!$pdo){
            echo "<p style='color:red'>Error de conexión a la base de datos.</p>";
        } else {
       
            echo "<table>";
            echo "<tr>
                    <th>TITULO</th>
                    <th>CONTENIDO</th>
                    <th>DESCRIPCIÓN</th>
                    <th>CATEGORÍA</th>
                    <th>ENLACE</th>
                    <th>FECHA</th>
                  </tr>";

            // 2. Determinar qué tabla leer
            $tabla = "elpais";
            if(isset($_GET['periodicos'])){
                $periodicos = str_replace(' ','', $_GET['periodicos']);
                if(strtolower($periodicos) == 'elmundo') {
                    $tabla = 'elmundo';
                }
            }

            // 3. Construcción dinámica de la consulta (Más limpia y segura)
            $sql = "SELECT * FROM $tabla WHERE 1=1";
            $params = [];

            // Filtro Categoría
            if(!empty($_GET['categoria'])){
                $sql .= " AND categoria ILIKE :cat"; // ILIKE ignora mayúsculas/minúsculas en Postgres
                $params[':cat'] = "%" . $_GET['categoria'] . "%";
            }

            // Filtro Fecha
            if(!empty($_GET['fecha'])){
                $sql .= " AND fecha = :fecha";
                $params[':fecha'] = $_GET['fecha'];
            }

            // Filtro Buscar
            if(!empty($_GET['buscar'])){
                $sql .= " AND descripcion ILIKE :buscar";
                $params[':buscar'] = "%" . $_GET['buscar'] . "%";
            }

            // Ordenar por fecha descendente
            $sql .= " ORDER BY fecha DESC LIMIT 50";

            // 4. Ejecutar consulta
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Prevenir errores si algún campo está vacío
                    $titulo = $fila['titulo'] ?? '';
                    $contenido = $fila['contenido'] ?? ''; // El Mundo no suele tener esto
                    $descripcion = $fila['descripcion'] ?? '';
                    $categoria = $fila['categoria'] ?? '';
                    $link = $fila['link'] ?? '#';
                    $fechaRaw = $fila['fecha'] ?? null;
                    
                    // Formato de fecha
                    $fechaBonita = "N/A";
                    if ($fechaRaw) {
                        $dateObj = date_create($fechaRaw);
                        $fechaBonita = date_format($dateObj, 'd-M-Y');
                    }

                    echo "<tr>";              
                    echo "<td><b>$titulo</b></td>";
                    echo "<td><small>" . substr($contenido, 0, 100) . "...</small></td>";
                    echo "<td>$descripcion</td>";                      
                    echo "<td>$categoria</td>";                       
                    echo "<td><a href='$link' target='_blank' style='color:#ba68c8'>Leer más</a></td>";                              
                    echo "<td>$fechaBonita</td>";
                    echo "</tr>";  
                }
            } catch (PDOException $e) {
                echo "<tr><td colspan='6'>Error leyendo datos: " . $e->getMessage() . "</td></tr>";
            }

            echo "</table>";   
        }
        ?>
        
    </body>
</html>