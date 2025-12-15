<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Noticias</title>
    </head>
    <body>
        <form action="index.php">
            <fieldset> 
                <legend>FILTRO</legend>
                <label>PERIODICO : </label>
                <select type="selector" name="periodicos">
                    <option name="elpais">El Pais</option>
                    <option name="elmundo">El Mundo</option>      
                </select> 
                <label>CATEGORIA : </label>
                <select type="selector" name="categoria" value="">
                    <option name=""></option>
                    <option name="Política">Política</option>
                    <option name="Deportes">Deportes</option>
                    <option name="Ciencia">Ciencia</option>
                    <option name="España">España</option>
                    <option name="Economía">Economía</option>
                    <option name="Música">Música</option>
                    <option name="Cine">Cine</option>
                    <option name="Europa">Europa</option>
                    <option name="Justicia">Justicia</option>                
                </select>
                <label>FECHA : </label>
                <input type="date" name="fecha" value=""></input>
                <label style="margin-left: 5vw;">AMPLIAR FILTRO (la descripción contenga la palabra) : </label>
                <input type="text" name="buscar" value=""></input>
                <input type="submit" name="filtrar" value="Filtrar">
            </fieldset>
        </form>
        
        <?php
        // 1. Incluimos archivos y conexión
        require_once "conexionBBDD.php"; 
        
        // Ejecutamos los scripts RSS para actualizar noticias al cargar (Opcional, puede ralentizar)
        require_once "RSSElPais.php";
        require_once "RSSElMundo.php";
        
        // 2. Obtenemos la conexión PDO (PostgreSQL)
        $pdo = obtenerConexion();
        
        // 3. Función FILTROS adaptada a PDO
        function filtros($sql, $pdo){
            try {
                // Preparamos la consulta (Seguro contra inyecciones básicas)
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                
                // Iteramos con fetch de PDO
                while ($arrayFiltro = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";              
                    echo "<th style='border: 1px #E4CCE8 solid;'>".$arrayFiltro['titulo']."</th>";
                    
                    // Comprobamos si existe 'contenido' (El Pais) o solo descripción
                    $contenido = isset($arrayFiltro['contenido']) ? $arrayFiltro['contenido'] : '';
                    echo "<th style='border: 1px #E4CCE8 solid;'>".$contenido."</th>";
                    
                    echo "<th style='border: 1px #E4CCE8 solid;'>".$arrayFiltro['descripcion']."</th>";                      
                    echo "<th style='border: 1px #E4CCE8 solid;'>".$arrayFiltro['categoria']."</th>";                       
                    echo "<th style='border: 1px #E4CCE8 solid;'>".$arrayFiltro['link']."</th>";                              
                    
                    // IMPORTANTE: En la base de datos de Neon la columna se llama 'fecha', no 'fPubli'
                    if (isset($arrayFiltro['fecha'])) {
                        $fecha = date_create($arrayFiltro['fecha']);
                        $fechaConversion = date_format($fecha,'d-M-Y');
                    } else {
                        $fechaConversion = "N/A";
                    }
                    
                    echo "<th style='border: 1px #E4CCE8 solid;'>".$fechaConversion."</th>";
                    echo "</tr>";  
                }
            } catch (PDOException $e) {
                echo "<tr><td colspan='6'>Error en la consulta: " . $e->getMessage() . "</td></tr>";
            }
        }
        
        // 4. Lógica principal
        if(!$pdo){
            printf("Conexión fallida a la base de datos.");
        } else {
       
            echo "<br><table style='border: 5px #E4CCE8 solid; width: 100%; text-align: left;'>";
            echo "<tr>
                    <th><p style='color: #66E9D9;'>TITULO</p></th>
                    <th><p style='color: #66E9D9;'>CONTENIDO</p></th>
                    <th><p style='color: #66E9D9;'>DESCRIPCIÓN</p></th>
                    <th><p style='color: #66E9D9;'>CATEGORÍA</p></th>
                    <th><p style='color: #66E9D9;'>ENLACE</p></th>
                    <th><p style='color: #66E9D9;'>FECHA</p></th>
                  </tr>";

            if(isset($_REQUEST['filtrar'])){

                $periodicos = str_replace(' ','',$_REQUEST['periodicos']);
                $periodicosMin = strtolower($periodicos);
                
                // Validación básica de seguridad para el nombre de la tabla
                if($periodicosMin !== 'elpais' && $periodicosMin !== 'elmundo') {
                    $periodicosMin = 'elpais';
                }

                $cat = $_REQUEST['categoria'];
                $f = $_REQUEST['fecha'];
                $palabra = $_REQUEST["buscar"];
                 
                // NOTA: He cambiado 'fPubli' por 'fecha' en todos los SQL para coincidir con tu tabla Neon
                
                // FILTRO PERIODICO (SOLO)
                if($cat=="" && $f=="" && $palabra==""){
                     $sql="SELECT * FROM ".$periodicosMin." ORDER BY fecha DESC";
                     filtros($sql, $pdo);
                }

                // FILTRO CATEGORIA
                if($cat!="" && $f=="" && $palabra==""){ 
                    $sql="SELECT * FROM ".$periodicosMin." WHERE categoria LIKE '%$cat%'";
                    filtros($sql, $pdo);
                }

                // FILTRO FECHA
                if($cat=="" && $f!="" && $palabra==""){
                   $sql="SELECT * FROM ".$periodicosMin." WHERE fecha='$f'";
                   filtros($sql, $pdo);
                }

                // FILTRO CATEGORIA Y FECHA
                if($cat!="" && $f!="" && $palabra==""){ 
                     $sql="SELECT * FROM ".$periodicosMin." WHERE categoria LIKE '%$cat%' AND fecha='$f'";
                     filtros($sql, $pdo);
                }

                // FILTRO TODO (CAT, FECHA, PALABRA)
                if($cat!="" && $f!="" && $palabra!=""){ 
                     $sql="SELECT * FROM ".$periodicosMin." WHERE descripcion LIKE '%$palabra%' AND categoria LIKE '%$cat%' AND fecha='$f'";
                     filtros($sql, $pdo);
                }  

                // FILTRO CATEGORIA Y PALABRA
                if($cat!="" && $f=="" && $palabra!=""){ 
                     $sql="SELECT * FROM ".$periodicosMin." WHERE descripcion LIKE '%$palabra%' AND categoria LIKE '%$cat%'";
                     filtros($sql, $pdo);
                } 

                // FILTRO FECHA Y PALABRA 
                if($cat=="" && $f!="" && $palabra!=""){ 
                     $sql="SELECT * FROM ".$periodicosMin." WHERE descripcion LIKE '%$palabra%' AND fecha='$f'";
                     filtros($sql, $pdo);
                }  

                // FILTRO PALABRA (SOLO)
                if($palabra!="" && $cat=="" && $f=="" ){ 
                     $sql="SELECT * FROM ".$periodicosMin." WHERE descripcion LIKE '%$palabra%'";
                     filtros($sql, $pdo);
                } 
                
            } else {
                // CARGA POR DEFECTO
                $sql="SELECT * FROM elpais ORDER BY fecha DESC";
                filtros($sql, $pdo);
            }
        }
          
        echo "</table>";   
        ?>
        
    </body>
</html>