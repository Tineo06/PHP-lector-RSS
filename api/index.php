<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Noticias</title>
    </head>
    <body>
        <form action="/" method="GET">
            <fieldset> 
                <legend>FILTRO</legend>
                <label>PERIODICO : </label>
                <select name="periodicos">
                    <option value="elpais">El Pais</option>
                    <option value="elmundo">El Mundo</option>      
                </select> 
                <label>CATEGORIA : </label>
                <select name="categoria">
                    <option value=""></option>
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
                <input type="date" name="fecha">
                <label style="margin-left: 5vw;">AMPLIAR FILTRO : </label>
                <input type="text" name="buscar">
                <input type="submit" name="filtrar" value="Filtrar">
            </fieldset>
        </form>
        
        <?php
        require_once "conexionRSS.php"; // Asegúrate de que este archivo existe y descarga bien
        // NOTA: Incluir estos archivos aquí hará que se descargue el XML CADA VEZ que cargues la página.
        // Esto puede ser lento. Idealmente, esto debería ir en un cron job, pero para este ejemplo lo dejamos.
        
        // Incluimos la conexión antes de los archivos de RSS porque ellos la usan
        require_once "conexionBBDD.php"; 
        
        // Para evitar errores si los RSS fallan, usamos include u ocultamos errores temporalmente
        include_once "RSSElPais.php";
        include_once "RSSElMundo.php";
        
        function filtros($sql, $link){
             // Cambio a pg_query
             $result = pg_query($link, $sql);
             
             if (!$result) {
                 echo "Ocurrió un error en la consulta.\n";
                 return;
             }

             // Cambio a pg_fetch_array
             while ($arrayFiltro = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
                   echo"<tr>";              
                        echo "<th style='border: 1px #E4CCE8 solid;'>".$arrayFiltro['titulo']."</th>";
                        // Postgres devuelve texto, aseguramos formato
                        echo "<th style='border: 1px #E4CCE8 solid;'>".substr($arrayFiltro['contenido'], 0, 100)."...</th>";
                        echo "<th style='border: 1px #E4CCE8 solid;'>".substr($arrayFiltro['descripcion'], 0, 100)."...</th>";                      
                        echo "<th style='border: 1px #E4CCE8 solid;'>".$arrayFiltro['categoria']."</th>";                       
                        echo "<th style='border: 1px #E4CCE8 solid;'><a href='".$arrayFiltro['link']."'>Link</a></th>";                              
                        
                        $fecha = date_create($arrayFiltro['fpubli']); // Postgres suele devolver columnas en minúsculas
                        if($fecha){
                            $fechaConversion = date_format($fecha,'d-M-Y');
                            echo "<th style='border: 1px #E4CCE8 solid;'>".$fechaConversion."</th>";
                        }
                   echo"</tr>";  
            }
        }
        
        if(!$link){
            printf("Conexión fallida");
        } else {
       
            echo"<br><table style='border: 5px #E4CCE8 solid; width:100%;'>";
            echo"<tr><th>TITULO</th><th>CONTENIDO</th><th>DESCRIPCIÓN</th><th>CATEGORÍA</th><th>ENLACE</th><th>FECHA</th></tr>";

            if(isset($_GET['filtrar'])){ // Usar $_GET es más limpio para formularios de búsqueda

                $periodicos = str_replace(' ','', $_GET['periodicos']);
                $periodicosMin = strtolower($periodicos);
                
                // Limpieza básica para evitar inyección SQL (pg_escape_string es vital)
                $cat = isset($_GET['categoria']) ? pg_escape_string($link, $_GET['categoria']) : '';
                $f = isset($_GET['fecha']) ? pg_escape_string($link, $_GET['fecha']) : '';
                $palabra = isset($_GET['buscar']) ? pg_escape_string($link, $_GET['buscar']) : '';
                 
                $queryParts = [];
                if ($cat != "") $queryParts[] = "categoria LIKE '%$cat%'";
                if ($f != "") $queryParts[] = "fpubli = '$f'"; // fpubli en minúscula para Postgres
                if ($palabra != "") $queryParts[] = "descripcion LIKE '%$palabra%'";

                $sql = "SELECT * FROM " . pg_escape_identifier($link, $periodicosMin);
                
                if (count($queryParts) > 0) {
                    $sql .= " WHERE " . implode(' AND ', $queryParts);
                }
                
                $sql .= " ORDER BY fpubli DESC"; // fpubli en minúscula
                
                filtros($sql, $link);
                
            } else {
                $sql = "SELECT * FROM elpais ORDER BY fpubli DESC";
                filtros($sql, $link);      
            }
        }
        echo"</table>";   
        ?>
    </body>
</html>