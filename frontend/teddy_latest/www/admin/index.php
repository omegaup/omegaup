<?php


	session_start();

	include_once("../config.php");
	include_once("../includes/db_con.php");		

	
	date_default_timezone_set('America/Mexico_City');


?>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="../css/teddy_style.css" />
		<title>Teddy Online Judge - Admin</title>
			<script src="../js/jquery.min.js"></script>
			<script src="../js/jquery-ui.custom.min.js"></script>
		
	</head>
<body>

<div class="wrapper">
	<div class="header">
		<h1>teddy online judge</h1>
		<h2>teddy es un oso de peluche</h2>
	</div>

	<?php include_once("../includes/admin.menu.php"); ?>
	<?php //include_once("../includes/session_mananger.php"); ?>	



	<?php include_once("../includes/footer.php"); ?>

</div>

<?php include("../includes/ga.php"); ?>
</body>
</html>

<?php
	/*if( isset($resultado) )
		mysql_free_result($resultado);*/
		
	if( isset($enlace))
		mysql_close($enlace);
?>

