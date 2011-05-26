<?php 

	session_start(); 
	include_once("config.php");
	include_once("includes/db_con.php");
?>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="css/teddy_style.css" />
		<title>Teddy Online Judge - Runs</title>
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
	<?php

	include_once("includes/db_con.php");

	//encontrar todos los titulos y asignarselos a un array
	$query = mysql_query("select probID, titulo from Problema");
	$probset = array();
	while($foo = mysql_fetch_array($query)){
		//print( $foo["probID"] . " "  . $foo["titulo"] );
		$probset[ $foo["probID"] ] = $foo["titulo"];
	}


	$consulta = "SELECT `execID`, `userID`, `probID`, `status`, `tiempo`, `fecha`, `LANG`, `Concurso` FROM `Ejecucion` order by fecha desc LIMIT 100";

	if(isset($_GET["user"])){
		/*
		 * ESTADISTICAS DEL USUARIO
		 * */
		
		
		//concursos
     	$sql = 'SELECT DISTINCT concurso FROM  `Ejecucion` WHERE concurso > 0 AND userID =  "' . addslashes($_GET['user']) . '"';
		$resultado = mysql_query($sql) or die('Algo anda mal: ' . mysql_error());
		$ncontests = mysql_num_rows($resultado);
						
		//vamos a imprimir cosas del usuario
		$query = "select userID, nombre, solved, tried, escuela, ubicacion, twitter from Usuario where userID = '" . addslashes($_GET['user']) . "'";
		$resultado = mysql_query($query) or die('Algo anda mal: ' . mysql_error());
	
		if(mysql_num_rows($resultado) != 1){
			echo "<h2>Ups</h2>";
			echo "Este usuario no existe";
			return;
		}
		$row = mysql_fetch_array($resultado);

		echo "<table><tr><td width='400px'>";
		echo "		<h2>" . htmlentities(utf8_decode( $_GET['user'])) . "</h2>";
		echo "		<b>" .  htmlentities(utf8_decode( $row['nombre'])) . "</b><br>". htmlentities(utf8_decode($row['ubicacion'])) ." <b> - </b> ". htmlentities(utf8_decode($row['escuela'])) ;
		echo "</td><td>";		
		if( $row[3] != 0 ){
			$rat = ($row[2]/$row[3])*100;
			$rat = substr( $rat , 0 , 5 ) . "%";
		}else
			$rat = "0.0%";

		if(strlen($row[6]) > 0){
			$twitter = "<a href='http://twitter.com/{$row[6]}'>{$row[6]}</a>";
		}else{
			$twitter = "";
		}


		echo "		<table>";
		echo "		<tr><td width='100px'><b>Enviados</b></td><td width='100px'><b>Resueltos</b></td><td width='100px'><b>Radio</b></td><td><b>Concursos</b>&nbsp;&nbsp;</td><td><b>Twitter</b></td></tr>";
		echo "		<tr><td>{$row[3]}</td><td><b>{$row[2]}</b></td><td>{$rat}</td><td>{$ncontests}</td><td>{$twitter}</td></tr>";
		echo "		</table>";

		echo "</td></tr></table>";
		

		//problemas resultos
		
		echo "<div align=center><br><b>Problemas resueltos</b><br>";
		$query = "select distinct probID from Ejecucion where userID = '" . $_GET['user'] . "' AND status = 'OK' order by probID";
		$resultado = mysql_query($query) or die('Algo anda mal: ' . mysql_error());
		while($row = mysql_fetch_array($resultado)){
			echo "<a title='{$probset[$row['probID'] ]}' href=\"verProblema.php?id={$row['probID']}\">{$row['probID']}</a>&nbsp;";
		}

		//concursos
		
		echo "</div><br><br>";
		//dejar esta consulta en 
		$consulta = "SELECT `execID`, `userID`, `probID`, `status`, `tiempo`, `fecha`, `LANG`, `Concurso`  FROM `Ejecucion` where userID = '" . addslashes($_GET["user"]) . "' order by fecha desc LIMIT 150";
	}else{
			?>
			<div align="center">
			<h2>Run-Status</h2>

			Mostrando los ultimos 100 envios a Teddy. 
			<!-- 
			<div style="margin-left: 20px">
				<ul>
					<li>Click en un usuario para ver su perfil.</li>
					<li>Click en un execID para ver el codigo fuente.</li>
					<li>Mouseover en el ID del problema para ver que problema es.</li>
				</ul>
			</div>
			-->
			</div>
			<br/>
			<?php
	}

	$resultado = mysql_query($consulta);


	
	?>

	<div align="center" >
	<!-- <h2>Ultima actividad</h2> -->

	<table border='0' style="font-size: 14px;" > 
	<thead> <tr >
		<th width='12%'>execID</th> 
		<th width='12%'>Problema</th> 
		<th width='12%'>Usuario</th> 
		<th width='12%'>Lenguaje</th> 
		<th width='12%'>Resultado</th> 
		<th width='12%'>Tiempo</th> 
		<th width='12%'>Fecha</th>
		</tr> 
	</thead> 
	<tbody>
	<?php
	$flag = true;
    	while($row = mysql_fetch_array($resultado)){
		$color = "black";
		$ESTADO = $row['status'];
	
		

		$nick = htmlentities( $row['userID'] );

		//checar si hay una sesion y si si hay mostrar el usuario actual en cierto color
		$foobar = $row['execID'];
		$tooltip = "Ver este Codigo";
		if( $row["Concurso"] != -1 ){
			$foobar = "" . $row['execID'] . "*";
			$tooltip = "Este run fue parte de algun concurso online";
		}


		if($flag){
			echo "<TR style=\"background:#e7e7e7;\">";
			$flag = false;
		}else{
			echo "<TR style=\"background:white;\">";
			$flag = true;
		}



		echo "<TD align='center' ><a title='$tooltip' href='verCodigo.php?execID={$row['execID']}'>". $foobar  ."</a></TD>";
		echo "<TD align='center' ><a title='". $probset[ $row["probID"] ] ."' href='verProblema.php?id=". $row['probID']  ."'>". $row['probID']   ."</a> </TD>";
		echo "<TD align='center' ><a title='Ver perfil' href='runs.php?user=". $row['userID']  ."'>". $nick   ."</a> </TD>";
		echo "<TD align='center' >". $row['LANG']   ."</TD>";
		echo "<TD align='center' ><div style=\"color:".$color."\">". $ESTADO   ."</div> </TD>";
		printf("<TD align='center' >%1.3fs </TD>", $row['tiempo'] / 1000);
		echo "<TD align='center' >". $row['fecha']   ." </TD>";
		echo "</TR>";
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
