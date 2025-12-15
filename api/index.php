<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Noticias</title>
        <style>
            /* Reset básico y tipografía moderna */
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background-color: #f4f4f9;
                color: #333;
                margin: 0;
                padding: 20px;
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            /* Estilo del formulario (tipo tarjeta) */
            form {
                background: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                max-width: 1000px;
                width: 100%;
                margin-bottom: 30px;
                box-sizing: border-box;
            }

            fieldset {
                border: 1px solid #ddd;
                border-radius: 5px;
                padding: 15px 20px;
                display: flex;
                flex-wrap: wrap;
                gap: 15px;
                align-items: center;
            }

            legend {
                font-weight: bold;
                color: #6a1b9a; /* Violeta oscuro */
                padding: 0 10px;
                text-transform: uppercase;
                letter-spacing: 1px;
                font-size: 0.9rem;
            }

            label {
                font-weight: 600;
                font-size: 0.9rem;
                color: #555;
            }

            /* Inputs y Selects estilizados */
            select, input[type="date"], input[type="text"] {
                padding: 8px 12px;
                border: 1px solid #ccc;
                border-radius: 4px;
                outline: none;
                transition: border-color 0.3s;
            }

            select:focus, input:focus {
                border-color: #6a1b9a;
            }

            /* Botón de filtrar */
            input[type="submit"] {
                background-color: #6a1b9a;
                color: white;
                border: none;
                padding: 8px 20px;
                border-radius: 4px;
                cursor: pointer;
                font-weight: bold;
                text-transform: uppercase;
                transition: background 0.3s ease;
                margin-left: auto; /* Empuja el botón a la derecha si hay espacio */
            }

            input[type="submit"]:hover {
                background-color: #4a148c;
            }

            /* Estilos de la Tabla */
            table {
                width: 100%;
                max-width: 1200px;
                border-collapse: collapse;
                background-color: white;
                box-shadow: 0 4px 6px rgba(0,0,0,0.05);
                border-radius: 8px; /* Bordes redondeados en las esquinas de la tabla */
                overflow: hidden; /* Necesario para que el radius funcione en las esquinas */
                margin-top: 10px;
            }

            th {
                background-color: #6a1b9a;
                color: white;
                padding: 15px;
                text-align: left;
                font-size: 0.9rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            td {
                padding: 12px 15px;
                border-bottom: 1px solid #eee;
                font-size: 0.95rem;
                vertical-align: top;
                color: #444;
            }

            /* Efecto cebra y hover en filas */
            tr:nth-child(even) {
                background-color: #f9f9f9;
            }

            tr:hover {
                background-color: #f3e5f5; /* Un lila muy suave al pasar el ratón */
            }

            /* Estilo para el enlace */
            td a {
                color: #6a1b9a;
                text-decoration: none;
                font-weight: bold;
                border-bottom: 2px solid transparent;
                transition: border-color 0.3s;
            }

            td a:hover {
                border-bottom: 2px solid #6a1b9a;
            }

            /* Responsividad básica */
            @media (max-width: 768px) {
                fieldset {
                    flex-direction: column;
                    align-items: flex-start;
                }
                input[type="submit"] {
                    width: 100%;
                    margin-left: 0;
                }
            }
        </style>
    </head>
    <body>
        <form action="/" method="GET">
            <fieldset> 
                <legend>Filtro de Noticias</legend>
                
                <label>PERIÓDICO</label>
                <select name="periodicos">
                    <option value="elpais">El Pais</option>
                    <option value="elmundo">El Mundo</option>      
                </select> 
                
                <label>CATEGORÍA</label>
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

                <label>FECHA</label>
                <input type="date" name="fecha">
                
                <label>BUSCAR</label>
                <input type="text" name="buscar" placeholder="Palabra clave...">
                
                <input type="submit" name="filtrar" value="Filtrar Resultados">
            </fieldset>
        </form>
        
        <?php
        require_once "conexionRSS.php"; 
        require_once "conexionBBDD.php"; 
        
        // Uso @ para suprimir warnings si el archivo no existe en local, 
        // quítalo (@) cuando lo uses en producción.
        @include_once "RSSElPais.php";
        @include_once "RSSElMundo.php";
        
        function filtros($sql, $link){
             $result = pg_query($link, $sql);
             
             if (!$result) {
                 echo "<div style='color:red; text-align:center;'>Ocurrió un error en la consulta.</div>";
                 return;
             }

             // IMPORTANTE: He cambiado los <th> por <td> en el cuerpo de la tabla
             // para que el CSS funcione correctamente (th es para cabeceras, td para datos)
             while ($arrayFiltro = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
                    echo"<tr>";              
                        echo "<td><strong>".$arrayFiltro['titulo']."</strong></td>";
                        echo "<td>".substr($arrayFiltro['contenido'], 0, 100)."...</td>";
                        echo "<td>".substr($arrayFiltro['descripcion'], 0, 100)."...</td>";                      
                        echo "<td><span style='background:#eee; padding:2px 6px; border-radius:4px; font-size:0.8em;'>".$arrayFiltro['categoria']."</span></td>";                       
                        echo "<td><a href='".$arrayFiltro['link']."' target='_blank'>Leer más</a></td>";                              
                        
                        $fecha = date_create($arrayFiltro['fpubli']); 
                        if($fecha){
                            $fechaConversion = date_format($fecha,'d-M-Y');
                            echo "<td>".$fechaConversion."</td>";
                        } else {
                            echo "<td>-</td>";
                        }
                    echo"</tr>";  
             }
        }
        
        if(!$link){
            printf("Conexión fallida");
        } else {
            // Quitamos el estilo inline de la tabla para usar el CSS del <head>
            echo "<table>";
            echo "<thead>"; // Añadimos thead para semántica correcta
            echo "<tr>
                    <th style='width: 20%;'>TITULO</th>
                    <th style='width: 25%;'>CONTENIDO</th>
                    <th style='width: 25%;'>DESCRIPCIÓN</th>
                    <th style='width: 10%;'>CATEGORÍA</th>
                    <th style='width: 10%;'>ENLACE</th>
                    <th style='width: 10%;'>FECHA</th>
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

                // Protección básica: asegurar que la tabla seleccionada es válida
                $tablasPermitidas = ['elpais', 'elmundo'];
                if(!in_array($periodicosMin, $tablasPermitidas)) {
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
        }
        echo"</table>";   
        ?>
    </body>
</html>