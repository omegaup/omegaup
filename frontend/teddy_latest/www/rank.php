<?php 
	session_start(); 
	include_once("config.php");
	include_once("includes/db_con.php");

?>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="css/teddy_style.css" />
		<title>Teddy Online Judge - Ranking</title>
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

	<div class="post_blanco">
	<h2>Ranking de Teddy</h2>        
	<?php

	include_once("includes/db_con.php");


	//vamos a imprimir cosas del usuario
	$query = "select userID, escuela, solved, tried, ubicacion from Usuario order by solved desc, tried";

	$resultado = mysql_query($query) or die('Algo anda mal: ' . mysql_error());
	echo "<b> ". mysql_num_rows($resultado) . "</b> usuarios<br>";
	?>

	<div align="center" >
	<table border='0' style="font-size: 14px;" > 
	<thead> <tr >
		<th width='5%'>Rank</th> 
		<th width='5%'>Usuario</th> 
		<th width='15%'>Ubicacion</th> 
		<th width='15%'>Escuela</th> 
		<th width='5%'>Resueltos</th> 
		<th width='5%'>Envios</th> 
		<th width='5%'>Radio</th> 
		</tr> 
	</thead> 
	<tbody>
	<?php
	$rank = 1;
	$flag = true;
    	while($row = mysql_fetch_array($resultado)){

		$nick = $row['userID'];

		if( $row['solved'] != 0 )
			$ratio = substr( ($row['solved'] / $row['tried'])*100 , 0, 5);
		else
			$ratio = 0.0;

		//checar si hay una sesion y si si hay mostrar el usuario actual en cierto color
		if(isset($_SESSION['userID']) &&  $_SESSION['userID'] == $row['userID'] ){
	        echo "<TR style=\"background:#566D7E; color:white;\">";
			$flag = !$flag;
		}else{ 
			if($flag){
				echo "<TR style=\"background:#e7e7e7;\">";
				$flag = false;
			}else{
				echo "<TR style=\"background:white;\">";
				$flag = true;
			}
		}

		echo "<TD align='center' >". $rank ."</TD>";
		
		if(isset($_SESSION['userID']) &&  $_SESSION['userID'] == $row['userID'] ){
			echo "<TD align='center' ><a style=\"color:white;\" href='runs.php?user=". htmlentities($row['userID'])  ."'>". $nick   ."</a> </TD>";
		}else{
			echo "<TD align='center' ><a href='runs.php?user=". htmlentities($row['userID'])  ."'>". $nick   ."</a> </TD>";
		}
		echo "<TD align='center' >".  htmlentities(utf8_decode($row['ubicacion'])) ." </TD>";
		echo "<TD align='center' >".  htmlentities(utf8_decode($row['escuela'])) ." </TD>";
		echo "<TD align='center' >". $row['solved']  ." </TD>";
		echo "<TD align='center' >". $row['tried']   ." </TD>";
		//echo "<TD align='center' > {$ratio}% </TD>";
		printf("<TD align='center' > %2.2f%% </TD>", $ratio);


		echo "</TR>";
		$rank++;
	}
	?>		
	</tbody>
	</table>
	</div>
	</div>



	<?php include_once("includes/footer.php"); ?>

</div>
<?php include("includes/ga.php"); ?>
</body>
</html>

<?php
	if( isset($resultado))
		 mysql_free_result($resultado);

	if( isset($enlace))
		mysql_close($enlace);
?>
