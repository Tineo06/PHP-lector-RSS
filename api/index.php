<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Noticias</title>
        <style>
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; color: #333; }
        </style>
    </head>
    <body>
        <form action="" method="GET">
            <fieldset> 
                <legend>FILTRO</legend>
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
                <input type="text" name="buscar" value="<?php echo isset($_GET['buscar']) ? $_GET['buscar'] : ''; ?>">
                
                <input type="submit" name="filtrar" value="Filtrar">
            </fieldset>
        </form>

        <?php
        require_once "conexionBBDD.php"; // Esto nos da la variable $pdo

        // IMPORTANTE: En Vercel, ejecutar el scraping en cada visita (require RSSElPais...) 
        // hará que la web sea muy lenta y pueda dar timeout (504).
        // Lo ideal es comentar estas líneas y ejecutarlas manualmente o con un Cron Job.
        // Por ahora, las dejo comentadas para que la web cargue rápido.
        // include "RSSElPais.php";
        // include "RSSElMundo.php";

        $tabla = "elpais"; // Valor por defecto
        if (isset($_GET['periodicos'])) {
            $inputPeriodico = strtolower($_GET['periodicos']);
            if ($inputPeriodico === 'elmundo') {
                $tabla = 'elmundo';
            }
        }

        // Construcción dinámica de la consulta usando PDO y parámetros
        $sql = "SELECT * FROM $tabla WHERE 1=1";
        $params = [];

        if (!empty($_GET['categoria'])) {
            // Postgres usa ILIKE para case-insensitive, LIKE es case-sensitive
            $sql .= " AND categoria ILIKE :categoria";
            $params[':categoria'] = '%' . $_GET['categoria'] . '%';
        }

        if (!empty($_GET['fecha'])) {
            $sql .= " AND fPubli = :fecha";
            $params[':fecha'] = $_GET['fecha'];
        }

        if (!empty($_GET['buscar'])) {
            $sql .= " AND descripcion ILIKE :buscar";
            $params[':buscar'] = '%' . $_GET['buscar'] . '%';
        }

        $sql .= " ORDER BY fPubli DESC";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            echo "<table>";
            echo "<tr><th>TITULO</th><th>CONTENIDO</th><th>DESCRIPCIÓN</th><th>CATEGORÍA</th><th>ENLACE</th><th>FECHA</th></tr>";
            
            while ($row = $stmt->fetch()) {
                $fechaF = date("d-M-Y", strtotime($row['fPubli']));
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['titulo']) . "</td>";
                // strip_tags limpia el HTML del contenido para que no rompa la tabla
                echo "<td>" . substr(strip_tags($row['contenido']), 0, 100) . "...</td>"; 
                echo "<td>" . htmlspecialchars($row['descripcion']) . "</td>";
                echo "<td>" . htmlspecialchars($row['categoria']) . "</td>";
                echo "<td><a href='" . htmlspecialchars($row['link']) . "' target='_blank'>Ir a noticia</a></td>";
                echo "<td>" . $fechaF . "</td>";
                echo "</tr>";
            }
            echo "</table>";

        } catch (PDOException $e) {
            echo "Error en la consulta: " . $e->getMessage();
        }
        ?>
    </body>
</html>