<?php 
	session_start();

	include_once("config.php");
	include_once("includes/db_con.php");	

	date_default_timezone_set('America/Mexico_City');

//mysql_real_escape_string($_GET["orden"])
?>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="css/teddy_style.css" />
		<title>Teddy Online Judge - Ver Problema</title>
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
	
	<div class="post">
       	<?php
	
	include_once("includes/db_con.php");

	$consulta = "select titulo, problema, tiempoLimite, aceptados, intentos from Problema where probID = '" . addslashes($_GET["id"]) . "';";
	$resultado = mysql_query($consulta) or die('Algo anda mal: ' . mysql_error());
	$row = mysql_fetch_array($resultado);

	if(mysql_num_rows($resultado) == 1){
		$tiempo = $row['tiempoLimite'] / 1000;

		echo "<h2>" . $_GET["id"] . ". " . $row['titulo'] ."</h2>";
		echo "<p>Limite de tiempo : <b>" . $tiempo . "</b> seg. &nbsp;&nbsp;";
		echo "Total runs : <b>" . $row['intentos'] . "</b>&nbsp;&nbsp;";
		echo "Aceptados : <b>" . $row['aceptados'] . "</b></p> ";
	
		echo  $row['problema'] ;
	
		$consulta = "UPDATE Problema SET vistas = (vistas + 1) WHERE probID = \"".mysql_real_escape_string($_GET["id"])."\" LIMIT 1 ";	
		mysql_query($consulta) or die('Algo anda mal: ' . mysql_error());
	
	?>
	<?php
	if(!isset($_REQUEST['cid'])){
		//si no es concurso
	?>
		<div align="center">
			<form action="enviar.php" method="get">
				<input type="hidden" name="pid" value="<?php echo $_GET['id']; ?>">
				<input type="submit" value="enviar solucion">
			</form>
		</div>
	<?php
	}else{
		//si es concurso
		?>
		<div align="center" >
			Enviar problema para el concurso
		<form action="contest_rank.php?cid=<?php echo $_REQUEST['cid']; ?>" method="POST" enctype="multipart/form-data">
			<br>
			<table border=0>
				 <tr><td  style="text-align: right">Codigo fuente&nbsp;&nbsp;</td><td><input name="userfile" type="file"></td></tr>
				 <tr><td></td><td><input type="submit" value="Enviar Solucion"></td></tr>
			</table>
		    <input type="hidden" name="ENVIADO" value="SI">
		    <input type="hidden" name="prob" value="<?php echo $_REQUEST['id']; ?>">
		    <input type="hidden" name="cid" value="<?php echo $_REQUEST['cid']; ?>">

		</form> 
		</div>
		<?php
	}
	?>
	<?php
		// <-- php 
		}else{
			echo "<div align='center'><h2>El problema " . $_GET["id"] . " no existe.</h2></div>";
		}
		//<-- php
	?>
</div>

<?php
	if(!isset($_REQUEST['cid'])){
?>
	<div class="post" style="background: white; border:1px solid #bbb;">
		<?php
		// mejores tiempos !
		$consulta = "SELECT DISTINCT  `userID` ,  `execID` ,  `status` , MIN(  `tiempo` ) as 'tiempo' , fecha,  `LANG` FROM  `Ejecucion` WHERE (	probID =  ". mysql_real_escape_string($_GET["id"]) ."	AND STATUS =  'OK'	)	GROUP BY  `userID`	 order by tiempo asc LIMIT 5";
		$resultado = mysql_query($consulta) or die('Algo anda mal: ' . mysql_error());
		?>

		<div align="center" >
		<h3>Top 5 tiempos para este problema</h3><br>

		<table border='0' style="font-size: 14px;" > 
		<thead> <tr >
			<th width='12%'>execID</th> 
			<th width='12%'>Usuario</th> 
			<th width='12%'>Lenguaje</th> 

			<th width='12%'>Tiempo</th> 
			<th width='12%'>Fecha</th>
			</tr> 
		</thead> 
		<tbody>
		<?php
		$flag = true;
	    	while($row = mysql_fetch_array($resultado)){

				$nick = $row['userID'];


				if($flag){
		        	echo "<TR style=\"background:#e7e7e7;\">";
					$flag = false;
				}else{
		        	echo "<TR style=\"background:white;\">";
					$flag = true;
				}

				$cuando = date("F j, Y", strtotime($row['fecha']));
				echo "<TD align='center' ><a href='verCodigo.php?execID={$row['execID']}'>". $row['execID'] ."</a></TD>";
				echo "<TD align='center' ><a href='runs.php?user=". $row['userID']  ."'>". $nick   ."</a> </TD>";
				echo "<TD align='center' >". $row['LANG']   ."</TD>";
				echo "<TD align='center' ><b>". $row['tiempo'] / 1000  ."</b> Segundos </TD>";
				echo "<TD align='center' >". $cuando   ." </TD>";
				echo "</TR>";
		}
		?>		
		</tbody>
		</table>
		</div>
	</div>
	<?php
	
	}
	?>


	<?php include_once("includes/footer.php"); ?>

</div>
<?php include("includes/ga.php"); ?>
</body>
</html>

<?php
	if( isset($resultado) )
		 mysql_free_result($resultado);

	if( isset($enlace))
		mysql_close($enlace);
?>
