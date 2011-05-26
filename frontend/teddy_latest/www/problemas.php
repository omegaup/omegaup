<?php 
	session_start(); 
	include_once("config.php");
	include_once("includes/db_con.php");

?>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="css/teddy_style.css" />
		<title>Teddy Online Judge - Problem Set</title>
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
	
	<div class="post" style="background: white; border:1px solid #bbb;">
        
<div align="center">
	<h2>Problem-Set</h2>

	<?php

	$consulta = "select probID, titulo, vistas, aceptados, intentos from Problema where publico = 'SI' ";

	$solved = array();

	if(isset($_GET["userID"])){
		
		$query = "select distinct probID from Ejecucion where userID = '" . mysql_real_escape_string($_GET['userID']) . "' AND status = 'OK'";
		$resueltos = mysql_query($query) or die('Algo anda mal: ' . mysql_error());

		while($row = mysql_fetch_array($resueltos)){
			$solved[] = $row[0];
		}
	}
	

	
	if(isset($_GET["orden"])){
		$consulta .= (" ORDER BY " . mysql_real_escape_string($_GET["orden"])) ;
	}else{
		$consulta .= (" ORDER BY probID") ;
	}

	$resultado = mysql_query($consulta) or die('Algo anda mal: ' . mysql_error());

	if(isset($_GET["userID"])){

		echo "Hay un total de <b><span id='probs_left'></span></b> problemas no resueltos para <b>" . htmlentities( $_GET['userID'] ) . "</b>";
	}else{
		echo "Hay un total de <b>" . mysql_num_rows($resultado) . "</b> problemas<br>";
	}

	if(!isset($_GET["userID"]) && isset($_SESSION['userID'])){
		?> 
		<div align="center">
		</div>
		<?php
	}

	?>
	</div>
	<br>	<br>
	<div align="center">
	<table border='0'> 
	<thead> <tr >
		<th width='5%'>ID</th> 
		<th width='25%'>Titulo</th> 
		<th width='12%'><a href="problemas.php?orden=vistas">Vistas</a></th> 
		<th width='12%'><a href="problemas.php?orden=aceptados">Aceptados</a></th> 
		<th width='12%'><a href="problemas.php?orden=intentos">Intentos</a></th> 
		<th width='12%'>Radio</th>
		</tr> 
	</thead> 
	<tbody>
	<?php

	$flag = true;
	$left = 0;
    	while($row = mysql_fetch_array($resultado)){

		$ss = false;
		foreach ($solved as $probsolved) {
			if($row['probID'] == $probsolved)
				$ss = true;
		}

		if($ss)continue;

		if($row['intentos']!=0)
			$ratio = ($row['aceptados'] / $row['intentos'])*100;
		else
			$ratio = "0.0";

		$ratio = substr($ratio, 0, 6);

		if($flag){
	        	echo "<TR style=\"background:#e7e7e7;\">";
			$flag = false;
		}else{
	        	echo "<TR style=\"background:white;\">";
			$flag = true;
		}

		echo "<TD align='center' >". $row['probID'] ."</TD>";
		echo "<TD align='left' ><a href='verProblema.php?id=". $row['probID']  ."'>". $row['titulo']   ."</a> </TD>";
		echo "<TD align='center' >". $row['vistas']   ." </TD>";
		echo "<TD align='center' >". $row['aceptados']   ." </TD>";
		echo "<TD align='center' >". $row['intentos']   ." </TD>";
		printf("<TD align='center' >%2.2f%%</TD>", $ratio);
		echo "</TR>";
		$left++;
	}

	if(isset($_GET["userID"])){
		?>
			<script>document.getElementById("probs_left").innerHTML = "<?php echo $left; ?>";</script>
		<?php
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
