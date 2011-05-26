<?php

if( !(isset($_SESSION['userID']) && isset($_SESSION['userMode'])) || ( $_SESSION['userMode'] == "USER" )){
		die (" <h1>You dont belong here :P</h1> Teddy guardara tu IP y monitoreara tus actividades de ahora en adelante. ");
	}
?>


<div class="post">
	<div class="navcenter">
		<a href="../index.php">teddy home</a>&nbsp;&nbsp;&nbsp;
		<a href="test.php">estado</a>&nbsp;&nbsp;&nbsp;
		<a href="problemas.php">problemas</a>&nbsp;&nbsp;&nbsp;
		<a href="soluciones.php">soluciones</a>&nbsp;&nbsp;&nbsp;
		<a href="inbox.php">mensajes</a>&nbsp;&nbsp;&nbsp;
		<a href="runs.php">ejecuciones</a>&nbsp;&nbsp;&nbsp;
		<a href="usuarios.php">usuarios</a>&nbsp;&nbsp;&nbsp;
		<a href="log.php">log</a>&nbsp;&nbsp;&nbsp;
		<a href="config.php">configuracion</a>&nbsp;&nbsp;&nbsp;
	</div>
</div>
