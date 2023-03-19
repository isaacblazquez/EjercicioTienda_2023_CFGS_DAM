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
        $con = new PDO('mysql:host='.$db_host.'; dbname='.$db_name, $db_user, $user_pw);
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
            header("Location: productos.php");                    
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
    </body>
</html>
