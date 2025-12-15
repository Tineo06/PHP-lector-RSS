<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Noticias</title>
        <style>
            /* Fuente estándar y fondo gris claro */
            body {
                font-family: Arial, Helvetica, sans-serif;
                background-color: #f0f0f0;
                margin: 20px;
                text-align: center; /* Centrar contenido */
            }

            /* Estilo del contenedor del formulario */
            form {
                background-color: #fff;
                border: 1px solid #ccc;
                padding: 20px;
                display: inline-block; /* Para que se ajuste al contenido */
                width: 90%;
                max-width: 900px;
                text-align: left;
            }

            fieldset {
                border: 1px solid #ddd;
                padding: 15px;
                margin: 0;
            }

            legend {
                font-weight: bold;
                color: #333;
                padding: 0 5px;
            }

            label {
                display: inline-block;
                margin-top: 10px;
                margin-right: 5px;
                font-weight: bold;
                font-size: 0.9em;
            }

            /* Inputs simples sin efectos */
            select, input[type="text"], input[type="date"] {
                padding: 6px;
                border: 1px solid #999;
                margin-right: 15px;
            }

            /* Botón plano */
            input[type="submit"] {
                background-color: #333;
                color: #fff;
                border: none;
                padding: 8px 15px;
                cursor: pointer;
                font-size: 0.9em;
                margin-top: 10px;
            }

            input[type="submit"]:hover {
                background-color: #000;
            }

            /* Tabla estilo clásico y limpio */
            table {
                width: 100%;
                max-width: 1200px;
                margin: 20px auto;
                border-collapse: collapse; /* Elimina espacios entre celdas */
                background-color: #fff;
                border: 1px solid #ccc;
            }

            th {
                background-color: #444; /* Cabecera oscura */
                color: #fff;
                padding: 10px;
                text-align: left;
                font-size: 0.9em;
                border: 1px solid #444;
            }

            td {
                padding: 8px;
                border: 1px solid #ddd; /* Bordes finos grises */
                font-size: 0.9em;
                color: #333;
                vertical-align: top;
            }

            /* Filas alternas para mejor lectura */
            tr:nth-child(even) {
                background-color: #f9f9f9;
            }

            /* Enlace simple */
            a {
                color: #0066cc;
                text-decoration: none;
            }
            
            a:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <form action="/" method="GET">
            <fieldset> 
                <legend>FILTRAR NOTICIAS</legend>
                
                <label>Periódico:</label>
                <select name="periodicos">
                    <option value="elpais">El País</option>
                    <option value="elmundo">El Mundo</option>      
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
                <input type="date" name="fecha">
                
                <br> <label>Buscar texto:</label>
                <input type="text" name="buscar">
                
                <input type="submit" name="filtrar" value="APLICAR FILTRO">
            </fieldset>
        </form>
        
        <?php
        require_once "conexionRSS.php"; 
        require_once "conexionBBDD.php"; 
        
        @include_once "RSSElPais.php";
        @include_once "RSSElMundo.php";
        
        function filtros($sql, $link){
             $result = pg_query($link, $sql);
             
             if (!$result) {
                 echo "<p style='color:red;'>Error en la consulta.</p>";
                 return;
             }

             while ($arrayFiltro = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
                    echo"<tr>";              
                        // NOTA: Cambié los <th> a <td> aquí para que el estilo funcione bien
                        echo "<td><b>".$arrayFiltro['titulo']."</b></td>";
                        echo "<td>".substr($arrayFiltro['contenido'], 0, 100)."...</td>";
                        echo "<td>".substr($arrayFiltro['descripcion'], 0, 100)."...</td>";                      
                        echo "<td>".$arrayFiltro['categoria']."</td>";                       
                        echo "<td><a href='".$arrayFiltro['link']."'>Ir a noticia</a></td>";                              
                        
                        $fecha = date_create($arrayFiltro['fpubli']); 
                        if($fecha){
                            $fechaConversion = date_format($fecha,'d/m/Y');
                            echo "<td>".$fechaConversion."</td>";
                        } else {
                            echo "<td></td>";
                        }
                    echo"</tr>";  
             }
        }
        
        if(!$link){
            echo "Conexión fallida";
        } else {
            // Estructura de tabla limpia
            echo "<table>";
            echo "<thead>";
            echo "<tr>
                    <th width='20%'>TITULO</th>
                    <th width='25%'>CONTENIDO</th>
                    <th width='25%'>DESCRIPCIÓN</th>
                    <th width='10%'>CATEGORÍA</th>
                    <th width='10%'>ENLACE</th>
                    <th width='10%'>FECHA</th>
                  </tr>";
            echo "</thead>";
            echo "<tbody>";

            if(isset($_GET['filtrar'])){ 
                $periodicos = str_replace(' ','', $_GET['periodicos']);
                $periodicosMin = strtolower($periodicos);
                
                $cat = isset($_GET['categoria']) ? pg_escape_string($link, $_GET['categoria']) : '';
                $f = isset($_GET['fecha']) ? pg_escape_string($link, $_GET['fecha']) : '';
                $palabra = isset($_GET['buscar']) ? pg_escape_string($link, $_GET['buscar']) : '';
                 
                $queryParts = [];
                if ($cat != "") $queryParts[] = "categoria LIKE '%$cat%'";
                if ($f != "") $queryParts[] = "fpubli = '$f'"; 
                if ($palabra != "") $queryParts[] = "descripcion LIKE '%$palabra%'";

                // Validación simple de tabla
                if($periodicosMin !== 'elmundo' && $periodicosMin !== 'elpais') {
                    $periodicosMin = 'elpais';
                }

                $sql = "SELECT * FROM " . pg_escape_identifier($link, $periodicosMin);
                
                if (count($queryParts) > 0) {
                    $sql .= " WHERE " . implode(' AND ', $queryParts);
                }
                
                $sql .= " ORDER BY fpubli DESC"; 
                
                filtros($sql, $link);
                
            } else {
                $sql = "SELECT * FROM elpais ORDER BY fpubli DESC";
                filtros($sql, $link);      
            }
            echo "</tbody>";
            echo"</table>";   
        }
        ?>
    </body>
</html>