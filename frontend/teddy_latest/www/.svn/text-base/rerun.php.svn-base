<?php 
	session_start(); 

	if( !(isset($_SESSION['userID']) && isset($_SESSION['userMode'])) || ( $_SESSION['userMode'] == "USER" ))
		die (" <h1>You dont belong here :P</h1> Teddy guardara tu IP y monitoreara tus actividades de ahora en adelante. ");

	include_once("config.php");
	include_once("includes/db_con.php");
	

?>
<?php

function endsWith( $str, $sub ) {
	return ( substr( $str, strlen( $str ) - strlen( $sub ) ) == $sub );
}

?>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="css/teddy_style.css" />
		<title>Teddy Online Judge - Admin - ReJudge</title>
			<script src="js/jquery.min.js"></script>
			<script src="js/jquery-ui.custom.min.js"></script>
	</head>
<body>

<div class="wrapper">
	<div class="header">
		<h1>teddy online judge</h1>
		<h2>teddy es un oso de peluche</h2>
	</div>

	<?php include_once("includes/menu.php"); ?>
	<?php include_once("includes/session_mananger.php"); ?>	

	<div class="post_blanco" >
	<h2>Re-Judge</h2><br><br>
	
<?php

		if(isset($_REQUEST["execID"])){

			$execID = $_REQUEST["execID"];
			$res = array();

			$juez_par = "../bin/reRun " . $execID ;
				
			exec($juez_par, $res);

			//imprimir la salida
			foreach ($res as $value) {
				echo "$value<br />\n"; 
			}
		}else{
			echo "Debes mandar un execID como argumento";
		}

	?>

	</div>



	<?php include_once("includes/footer.php"); ?>

</div>
</body>
</html>

