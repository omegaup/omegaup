<?php 

	session_start(); 
	include_once("config.php");
	include_once("includes/db_con.php");
	
date_default_timezone_set('America/Mexico_City');

function endsWith( $str, $sub ) {
	return ( substr( $str, strlen( $str ) - strlen( $sub ) ) == $sub );
}

?>
<html>
<head>
		<link rel="stylesheet" type="text/css" href="css/teddy_style.css" />
		<title>Teddy Online Judge - Ver Codigo Fuente</title>
			<script src="js/jquery.min.js"></script>
			<script src="js/jquery-ui.custom.min.js"></script>
		<link type="text/css" rel="stylesheet" href="css/SyntaxHighlighter.css">
		<script language="javascript" src="js/shCore.js"></script>
		<script language="javascript" src="js/shBrushCSharp.js"></script>
		<script language="javascript" src="js/shBrushJava.js"></script>
		<script language="javascript" src="js/shBrushCpp.js"></script>
		<script language="javascript" src="js/shBrushPython.js"></script>
		<script language="javascript" src="js/shBrushXml.js"></script>
</head>
<body>

<div class="wrapper">
	<div class="header">
		<h1>teddy online judge</h1>
		<h2>teddy es un oso de peluche</h2>
	</div>

	<?php include_once("includes/menu.php"); ?>
	
	<?php include_once("includes/session_mananger.php"); ?>	

	<div class="post" style="background:white;">

	<h2>Revisar un codigo fuente</h2>

<?php



	function mostrarCodigo( $lenguaje, $execID , $row){

		$file  = "../codigos/" . $execID  ;

		switch($lenguaje){
			case "JAVA": $file .= ".java"; $sintaxcolor = "java"; break;
			case "C": $file .= ".c"; $sintaxcolor = "c"; break;
			case "C++": $file .= ".cpp"; $sintaxcolor = "cpp"; break;
			case "C#": $file .= ".cs"; $sintaxcolor = "csharp"; break;
			case "Python": $file .= ".py"; $sintaxcolor = "py"; break;
			case "Perl": $file .= ".pl"; $sintaxcolor = "py"; break;
			default : $file .= ".java"; $sintaxcolor = "java";
		}

		$lines = file($file);
			$codigo = "";
			$lineas_num = "";
			foreach( $lines as $line_num => $line ){
			$codigo .= htmlspecialchars( $line );
			$lineas_num .= ($line_num + 1) . "<br>";
		}

		if($_SESSION['userMode'] == "OWNER"){
			?>
			<div class="post">
				<div style="font-size: 14px;">Admin Options</div>
				<a href="rerun.php?execID=<?php echo $execID ?>"><h3>Re-Judge !</h3></a>
				<a href=""><h3>Enviar un mensaje a este usuario</h3></a>
			</div>
			<?php
		}
		?>
		<div class="post">
			<div align=center >
				<table border='0' style="font-size: 14px;" > 
				<thead> <tr >
					<th width='12%'>execID</th> 
					<th width='12%'>Usuario</th> 
					<th width='12%'>Lenguaje</th> 
					<th width='12%'>Resultado</th> 
					<th width='10%'>Tiempo</th> 
					<th width='14%'>Fecha</th>
					</tr> 
				</thead> 
				<tbody>
				<?php
						$nick = $row['userID'];
			        	echo "<TR style=\"background:#e7e7e7;\">";
						$cuando = date("F j, Y h:i:s A", strtotime($row['fecha']));
						echo "<TD align='center' >". $row['execID'] ."</TD>";
						echo "<TD align='center' ><a href='runs.php?user=". $row['userID']  ."'>". $nick   ."</a> </TD>";
						echo "<TD align='center' >". $row['LANG']   ."</TD>";
						echo "<TD align='center' >". $row['status']   ."</TD>";
						echo "<TD align='center' ><b>". $row['tiempo'] / 1000  ."</b> Segundos </TD>";
						echo "<TD align='center' >". $cuando   ." </TD>";
						echo "</TR>";

				?>		
				</tbody>
				</table>
			</div>
			&nbsp;
		</div>
		
		<?php
		
		echo "<textarea name=\"code\" class=\"$sintaxcolor\" cols=\"60\" rows=\"10\">{$codigo}</textarea>";
	}


	// --- revisar login ---
	function revisarLogin(){
		global $enlace;
		$asdf =  mysql_real_escape_string($_REQUEST["execID"]);
		$consulta = "select * from Ejecucion where BINARY ( execID = '{$asdf}' )";
		$resultado = mysql_query($consulta) or die('Algo anda mal: ' . mysql_error());
	
		if(mysql_num_rows($resultado) != 1){
			echo "<b>Este codigo no existe</b>";
			return;
		}

		$row = mysql_fetch_array($resultado);

		if(!isset($_SESSION['userID'])){
			?> <div align='center'> Inicia sesion con la barra de arriba para comprobar que este codigo es tuyo. </div> <?php
			return;
		}

		if( ($row['userID'] == $_SESSION['userID']) || ($_SESSION['userMode'] == "OWNER") ){
			//este codigo es tuyo o eres OWNER
			mostrarCodigo($row['LANG'], $_REQUEST["execID"] , $row);
	
		}else{
			
			
			//no puedes ver codigos que estan mal
			if($row['status'] != "OK"){
				?><div style="font-size: 16px;"> <img src="img/12.png">No puedes ver codigos que no estan aceptados aunque cumplas con los requisitos.</div><?php
				return;
			}
			
			//no puedes ver codigos que son parte de algun concurso
			if($row['Concurso'] != "-1"){
				?><div style="font-size: 16px;"> <img src="img/12.png">No puedes ver codigos que pertenecen a un concurso aunque cumplas con los requisitos.</div><?php
				return;
			}
			
			//este codigo no es tuyo, pero vamos a ver si ya lo resolviste con mejor tiempo y que no sea parte de un concurso
			$consulta = "select * from Ejecucion where probID = '". $row['probID'] ."' AND userID = '". $_SESSION['userID'] ."' AND tiempo < " . $row['tiempo'] . " AND status = 'OK' ;";
			$resultado2 = mysql_query($consulta) or die('Algo anda mal: ' . mysql_error());
			$nr = mysql_num_rows($resultado2);
			
			if($nr >= 1){
				//ok, te lo voy a mostrar...
				?><div style="font-size: 16px;"> <img src="img/49.png">Este codigo no es tuyo, pero lo puedes ver porque ya lo resolviste con un mejor tiempo.</div><?php
				mostrarCodigo($row['LANG'], $_REQUEST["execID"] , $row );
			}else{
				//no cumples con los requisitos
				?> 	
					<div align='center'> 
						<h2>Holly molly</h2> 
						<br>
						<div style="font-size: 16px;"> <img src="img/12.png">Estas intentado ver un codigo que no es tuyo. Para poder verlo tienes que resolver este problema y tener un mejor tiempo que el codigo que quieres ver.</div>
					</div> 
				<?php
			}
			

		}

	}


	// --- conectarse a la bd ---
	include_once("includes/db_con.php");


	revisarLogin();

	// --- cerrar conexion ---
	if( isset($resultado))
		 mysql_free_result($resultado);
	if( isset($enlace))
		mysql_close($enlace);
?>

	
	</div>





	<?php include_once("includes/footer.php"); ?>

</div>


<script language="javascript">
window.onload = function () {

    dp.SyntaxHighlighter.ClipboardSwf = 'flash/clipboard.swf';
    dp.SyntaxHighlighter.HighlightAll('code');
}

</script>
<?php include("includes/ga.php"); ?>
</body>
</html>

