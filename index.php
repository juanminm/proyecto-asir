<?php
include 'inc/functions.php';
sys_session_start();


?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Proyecto ASIR autenticatión</title>
	    <link media="all" href="css/style.css" rel="stylesheet" type="text/css"></link>
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <script src="js/jquery-2.1.4.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
    </head>
    <body>
    <div class="container">
        <div id="wrapper">
            <div id="header">
                <div id="title">
                    Proyecto ASIR de autenticación
                </div>
            </div>
            <nav class="bar">
                <div class="header-buttons"><a href="index.php">Inicio</a></div>
           </nav>
            <div id="content">
            <?php
            // controlador.php se encargara de mostrar el 'contenido' correspondiente
                include('./controller.php');
            ?>
            </div>
            <div id="footer">
				<div class="pull-left">ProyectoASIR.com</div>
                <div class="pull-right">© 2016 Juan Miguel Navarro Martínex</div>
            </div>
        </div>
    </div>
    </body>
</html>
