<?php
/**
* @modulo      Desarrollo de aplicaciones Web Entorno Sevidor
* @Tema        Desarrollo de aplicaciones Web en PHP
* @Unidad      4
* @Ejemplo     Tienda web: login.php
*/


function conectar(){
    $db_host = 'localhost';  //  hostname Por defecto: localhost 192.168.0.250 en red
    $db_name = 'ejtienda';  //  databasename
    $db_user = 'admintienda';  //  username
    $user_pw = 'admintienda';  //  password
    try {
        $con= new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $user_pw);

        //$con = new PDO('mysql:host='.$db_host.'; dbname='.$db_name., $db_user, $user_pw);
        $con->exec("set names utf8");
    } catch (PDOException $e) { //Se capturan los mensajes de error
        die("Error: " . $e->getMessage()); 
    }
    return $con;
}

// Comprobamos si ya se ha enviado el formulario
if (isset($_POST['enviar'])) {
    //Obtenemos los datos enviados por POST y los volcamos a variables
    $usuario = $_POST['usuario'];
    $password = $_POST['password']; 

    //Si el usuario o la contraseña esta vacío, mandamos un mensaje
    if (empty($usuario) || empty($password)) {
        $error = "Debes introducir un nombre de usuario y una contraseña";
    } else {
        //Conectamos a la base de datos para comenzar las comprobaciones del usuario
        $con = conectar();
        //var_dump($con);
        
        //Creamos la consulta parametrizada para comprobar el usuario y las credenciales, volcadas a la variable
        $sql = "SELECT usuario FROM usuarios WHERE usuario=:login AND contrasena=:contrasena";
        //preparamos la consulta
        $resultado = $con->prepare($sql);
        //Parametros de la consulta
        $resultado->bindParam(":login", $usuario);
        $resultado->bindparam(":contrasena", $password);
        //Ejecutamos la consulta
        $resultado->execute();

        //Volcamos los resultados en un array
        $fila = $resultado->fetch();
        
        //Si el numero de filas es mayor que nulo, es que existe ese usuario
        if ($fila != null) {
            //Iniciamos la sesion
            session_start();
            //Creamos la variable de usuario con el nombre del usuario
            $_SESSION['usuario']=$usuario;
            //Redireccionamos a la página que nos interesa
            if (!isset($_COOKIE['cesta'])){
                header("Location: productos.php"); 
            }

           // header("Location: productos.php");                    
        }
        else {
            // Si las credenciales no son válidas, se vuelven a pedir
            $error = "Usuario o contraseña no válidos!";
        }
        unset($resultado);
        unset($dwes);    
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <title>Ejemplo Tema 4: Login Tienda Web</title>
        <link href="tienda.css" rel="stylesheet" type="text/css">
    </head>

    <body>
    



        <?php
        if (isset( $_SESSION['usuario'])){

            if (isset($_COOKIE['cesta'])){

                //mostramos los productos comprados y vamos guardando las familias de los productos comprados

                $arrayFamilias=array();
                $comprados=array();
                print ('<br class="divisor" />');
                print_r("Has comprado recientemente los productos:</br>");
                foreach ($_COOKIE['cesta'] as $comprado => $producto){
                       
                    echo $comprado."</br>";
                    array_push($comprados,$comprado);
                    
                    $sql_familia = "SELECT familia FROM producto WHERE cod=:comprado";
                    $result = $con->prepare($sql_familia);
                    $result->bindParam(":comprado", $comprado);
                    $result->execute();
                    $rs = $result->fetchAll(PDO::FETCH_ASSOC);
                
                    array_push($arrayFamilias,$rs[0]['familia']);
                }
                

                //miramos cuantos productos tenemos en la tienda
                $sql_todos_productos = "SELECT COUNT(*) as total_productos FROM `producto`";
                $sth=$con->prepare($sql_todos_productos);
                $sth->execute();
                $rs2 = $sth->fetchAll(PDO::FETCH_ASSOC);
            

                //consultamos los productos recomendados segun las familias de productos almacenada en $arrayFamilias
                $sql_recomendados=  "SELECT cod, nombre_corto FROM `producto` as p WHERE ";
                $c=0;
                while ($c < count($arrayFamilias)){
                    if ($c==0){
                        $sql_recomendados.= "p.familia='".$arrayFamilias[$c]."'";
                    }else{
                        $sql_recomendados.= " OR p.familia='".$arrayFamilias[$c]."'";
                    }
                    $c++;
                }

                $sth=$con->prepare($sql_recomendados);
                $sth->execute();
                $rs1 = $sth->fetchAll(PDO::FETCH_ASSOC);

                //IMPORTANTE!!!!
                //SI ha comprado TODOS nuestros productos no tiene logica recomendarle productos comprados

                if (count($rs1)!=$rs2[0]['total_productos']){
                    echo "<p>Puede que también estés interesado en:</p>";
                    foreach ($rs1 as $key => $value) {
                        if (!in_array($value['cod'], $comprados)) {
                            echo "<b>".$value['nombre_corto']."</b></br>";
                        }
                    }
                }
               
                ?>
                <br class="divisor" />
                <a href="productos.php" >Iniciar compra</a> 
                <?php   
            }
     




        }else{
        ?>
        <div id='login'>
            <form action='login.php' method='post'>
                <fieldset >
                    <legend>Login</legend>
                    <div><span class='error'><?php echo (isset($error)? $error: ""); ?></span></div>
                    <div class='campo'>
                        <label for='usuario' >Usuario:</label><br/>
                        <input type='text' name='usuario' id='usuario' maxlength="50" /><br/>
                    </div>
                    <div class='campo'>
                        <label for='password' >Contraseña:</label><br/>
                        <input type='password' name='password' id='password' maxlength="50" /><br/>
                    </div>

                    <div class='campo'>
                        <input type='submit' name='enviar' value='Enviar' />
                    </div>
                </fieldset>
            </form>
        </div>
        <?php
        }
        ?>

    </body>
</html>
