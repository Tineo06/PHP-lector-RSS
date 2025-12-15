<?php
        require_once __DIR__ . "/../conexionBBDD.php"; 
        
        function filtros($sql, $pdo){ 
            if ($pdo === false) {
                echo "<p style='color:red; text-align:center;'>Error de conexión.</p>";
                return;
            }
            try {
                $stmt = $pdo->query($sql);
                if ($stmt->rowCount() == 0) {
                    echo "<p style='text-align:center;'>No hay resultados.</p>";
                    return;
                }
                while ($arrayFiltro = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";              
                        echo "<td><b>".$arrayFiltro['titulo']."</b></td>";
                        echo "<td>". substr(strip_tags($arrayFiltro['contenido']), 0, 100) . "...</td>";
                        echo "<td>". substr(strip_tags($arrayFiltro['descripcion']), 0, 100) . "...</td>";                      
                        echo "<td>".$arrayFiltro['categoria']."</td>";                       
                        echo "<td><a href='".$arrayFiltro['link']."' target='_blank'>Leer</a></td>";                              
                        
                        $fechaTexto = $arrayFiltro['fPubli'];
                        $fechaConversion = $fechaTexto ? date_format(date_create($fechaTexto), 'd/m/Y') : "-";
                        
                        echo "<td>".$fechaConversion."</td>";
                    echo "</tr>";  
                }
            } catch (PDOException $e) {
                echo "<p>Error SQL: " . $e->getMessage() . "</p>";
            }
        }
        
        if($link !== false){ 
            echo "<table>";
            echo "<tr>
                    <th>TÍTULO</th>
                    <th>CONTENIDO</th>
                    <th>DESCRIPCIÓN</th>
                    <th>CATEGORÍA</th>
                    <th>ENLACE</th>
                    <th>FECHA</th>
                  </tr>";

            $tabla = "elpais";
            if (isset($_REQUEST['periodicos'])) {
                $p = strtolower(str_replace(' ', '', $_REQUEST['periodicos']));
                if ($p == "elmundo") $tabla = "elmundo";
            }

            $sql = "SELECT * FROM $tabla WHERE 1=1"; 

            if(isset($_REQUEST['filtrar'])){
                $cat = $_REQUEST['categoria'];
                $f = $_REQUEST['fecha'];
                $palabra = $_REQUEST['buscar'];

                if (!empty($cat)) $sql .= " AND categoria LIKE '%$cat%'";
                if (!empty($f)) $sql .= " AND \"fPubli\" = '$f'";
                if (!empty($palabra)) $sql .= " AND (descripcion LIKE '%$palabra%' OR titulo LIKE '%$palabra%')";
            }
            
            $sql .= " ORDER BY \"fPubli\" DESC LIMIT 50";
            filtros($sql, $link);
            echo "</table>";   
        } else {
            // --- CAMBIO: Mensaje de error visible si no hay conexión ---
            echo "<div style='text-align:center; margin-top:50px; color:red; border:1px solid red; padding:20px;'>";
            echo "<h3>⚠ Error de Conexión</h3>";
            echo "<p>No se ha podido conectar a la base de datos.</p>";
            echo "<p>Revisa tus credenciales en <b>conexionBBDD.php</b> o la variable de entorno en Vercel.</p>";
            echo "</div>";
        }
        ?>
    </body>
</html>