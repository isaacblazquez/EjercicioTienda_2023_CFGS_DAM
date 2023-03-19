<?php
    // Recuperamos la información de la sesión
    session_start();

    foreach ($_SESSION['cesta'] as $codigo => $producto){
        $valor=$producto['cantidad'];
        setcookie("cesta[$codigo]",$valor,time()+3600);
    }

   
    unset($_SESSION['cesta']);
    die("Gracias por su compra.<br />Quiere <a href='productos.php'>comenzar de nuevo</a>?");
?>
