<?php 
	session_start(); 
	include_once("config.php");
	include_once("includes/db_con.php");

	
function endsWith( $str, $sub ) {
	return ( substr( $str, strlen( $str ) - strlen( $sub ) ) == $sub );
}





/***** ****************************
	startup
 ***** ****************************/
date_default_timezone_set('America/Mexico_City');
$c = isset( $_REQUEST['cid'] ) ? mysql_real_escape_string( $_REQUEST['cid'] ) : 0;
$q = "SELECT * from Concurso where CID = ". (int)$c .";";

$resultado = mysql_query($q) or die('Algo anda mal !: ' . mysql_error());

$CONTEST = NULL;
$STATUS = null;
$CDATA = null;

if(mysql_num_rows($resultado) == 1) {
	
	//este concurso existe
	$CONTEST = $_REQUEST['cid'] ;

	//revisar si, es pasado, actual, o en el futuro
	$row = mysql_fetch_array($resultado);
	
	// cdata contiene los datos de este concurso que trae el sql
	$CDATA = $row;	
	
	if( (time() > strtotime($row["Inicio"])) && ( time() < strtotime($row["Final"]) ) ){
		// activo
		$STATUS = "NOW";
	}
	
	if( (time() > strtotime($row["Final"])) ){
		// ya termino
		$STATUS = "PAST";		
	}
	
	if( time() < strtotime($row["Inicio"]) ){
		// activo
		$STATUS = "FUTURE";
	}
		
}else{
	//este concurso no existe
	
}


/***** ****************************
	CABECERA
 ***** ****************************/
function start(){
	global $CONTEST;
	global $STATUS;
	global $CDATA;	
	
	if($CONTEST == NULL) {
		echo  "<div align='center'><h2>Este concurso no es valido.</h2></div>" ;
		return;
	}

	?>
	<div align=center>
		
	<div><h2><?php echo $CDATA["Titulo"]; ?></h2></div>
	<div><?php echo $CDATA["Descripcion"]; ?></div>
	
	<table border='0' cellspacing="5" style="font-size: 14px;" > 
	<thead>
		<tr align=center>
		<th >Organizador</th>
		<?php
		if($STATUS == "NOW" || $STATUS == "PAST"){
			echo "<th >Problemas</th>";
		}
		?>
		<th >Inicia</th> 
		<th >Termina</th> 		
		</tr> 
	</thead> 
	<tbody >
		<tr align=center style="background-color: #e7e7e7">
			<td><?php echo $CDATA["Owner"]; ?></td>
			
			<?php
			// Si ya comenzo o esta en el pasado
			if($STATUS == "NOW" || $STATUS == "PAST"){
				echo "<td>";
				$probs = explode(' ', $CDATA["Problemas"]);
				for ($i=0; $i< sizeof( $probs ); $i++) {
					echo "<a target='_blank' href='verProblema.php?id=". $probs[$i]  ."&cid=". $_REQUEST['cid'] ."'>". $probs[$i] ."</a>&nbsp;";
				}		
				echo "</td>";			
			}
			?>

			<td><?php echo $CDATA["Inicio"]; ?></td>
			<td><?php echo $CDATA["Final"]; ?></td>
		</tr>
	</tbody>
	</table>
	</div>
	<?php

	
}



/***** ****************************
	IMPRIMIR FORMA DE ENVIO
 ***** ****************************/
function imprimirForma(){
	
	global $row;
	?>
	<div align="center" >
	<form action="contest_rank.php?cid=<?php echo $_REQUEST['cid']; ?>" method="POST" enctype="multipart/form-data">
		<br>
		<table border=0>
			 <tr><td  style="text-align: right">Codigo fuente&nbsp;&nbsp;</td><td><input name="userfile" type="file"></td></tr>
			
			 <tr><td style="text-align: right">Problema&nbsp;&nbsp;</td><td>
			 	<select name="prob">	
				<?php

				$probs = explode(' ', $row["Problemas"]);
				for ($i=0; $i< sizeof( $probs ); $i++) {
					echo "<option value=". $probs[$i] .">". $probs[$i] ."</option>"; //"<a href='verProblema.php?id=". $probs[$i]  ."'>". $probs[$i] ."</a>&nbsp;";
				}

				?>
				</select>
			 </td></tr>
			
			 <tr><td></td><td><input type="submit" value="Enviar Solucion"></td></tr>
		</table>
	    <input type="hidden" name="ENVIADO" value="SI">
	    <input type="hidden" name="cid" value="<?php echo $_REQUEST['cid']; ?>">
	    
	</form> 
	</div>
	<?php
}




/***** ****************************
	ENVIAR PROBLEMA
 ***** ****************************/
function enviando(){
		global $CDATA;	
		
		
		//tomo el valor de un elemento de tipo texto del formulario
		$usuario 		= $_SESSION	["userID"];
		$prob    		= $_POST["prob"];
		$CONCURSO_ID 	= $_REQUEST['cid'];


		//revisar que su ultimo envio sea mayor a 5 minutos
		
		//revisar que este problema exista para este concurso
		$PROBLEMAS = explode(' ', $CDATA["Problemas"]);						
		
		$found = false;
		for ($i=0; $i< sizeof( $PROBLEMAS ); $i++) {
			if($prob ==$PROBLEMAS[$i]) $found = true;
		}
		
		if(!$found){
			echo "<br><div align='center'><b>Ups, este problema no es parte de este concurso.</b><br><br></div>";
			imprimirForma();
			return;
		}
		
		
		
		//revisar si existe este problema
		$consulta = "select probID , titulo from Problema where BINARY ( probID = '{$prob}' ) ";
		$resultado = mysql_query($consulta) or die('Algo anda mal: ' . mysql_error());

		//si este problema no existe, salir
		if(mysql_num_rows($resultado) != 1) {
			echo "<br><div align='center'><b>Ups, este problema no existe.</b><br>Vuelve a intentar. Recuerda que el id es el numero que acompa&ntilde;a a cada problema.<br><br></div>";
			imprimirForma();
			return;
		}
		
		
		$row = mysql_fetch_array( $resultado );
		$TITULO = $row["titulo"];

		//datos del archivo
		$nombre_archivo = $_FILES['userfile']['name'];
		$tipo = $_FILES['userfile']['type'];
		$fname = $_FILES['userfile']['name'];

		//revisar que no existan espacios en blacno en el nombre del archivo
		$fname = strtr($fname, " ", "0");
		$fname = strtr($fname, "_", "0");
		$fname = strtr($fname, "'", "0");

		//compruebo si las características del archivo son las que deseo
		//si (no es text/x-java) y (no termina con .java) tons no es java		
		if ( !(endsWith($fname, ".java") || endsWith($fname, ".c") || endsWith($fname, ".cpp")|| endsWith($fname, ".py") || endsWith($fname, ".pl")) ) {
    			echo "<br><br><div align='center'><h2>Error :-(</h2>Debes subir un archivo que contenga un codigo fuente valio y que termine en alguna de las extensiones que <b>teddy</b> soporta.<br>";
			echo "Tipo no permitido: <b>". $tipo . "</b> para <b>". $_FILES['userfile']['name'] ."</b></div><br>";

			imprimirForma();

			return;
		}
		
		
		
		//insertar userID, probID, remoteIP
		mysql_query ( "INSERT INTO Ejecucion (`userID` , `probID` , `remoteIP`, `Concurso`) VALUES ('{$usuario}', {$prob}, '" . $_SERVER['REMOTE_ADDR']. "', " . $_REQUEST['cid'] . "); " ) or die('Algo anda mal: ' . mysql_error());
		$resultado = mysql_query ( "SELECT `execID` FROM `Ejecucion` order by `fecha` desc limit 1;" ) or die('Algo anda mal: ' . mysql_error());
		$row = mysql_fetch_array ( $resultado );

		$execID = $row["execID"];

		//mover el archio a donde debe de estar
		if (move_uploaded_file($_FILES['userfile']['tmp_name'], "work_zone/" . $execID . "_" . $fname)){

			$res = array();

			//el juez "Juez" recive -> usuario , id del problema , nombre del archivo, ip de origen
			$juez_par = "java Juez " . $execID . "_" . $fname ;

       		exec($juez_par, $res);

			//imprimir la salida
			foreach ($res as $value) {
				echo "$value<br />\n"; 
			}

		}else{
			//if no problem al subirlo	
			echo "Ocurrio algun error al subir el archivo. No pudo guardarse.";
		}
	
		imprimirForma();
}


?>
<html>
<head>
		<link rel="stylesheet" type="text/css" href="css/teddy_style.css" />
			<title>Teddy Online Judge - Concurso</title>
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
	
	
	<!-- 
		INFORMACION DEL CONCURSO
	-->
	<div class="post_blanco" >
	<?php
		//informacion del concurso
		start();

	?>	
	</div>
	
	<!-- 
		ENVIAR SOLUCION
	-->
	<div class="post" >
		<div style="font-size: 18px" align=center>
			<?php

			switch($STATUS){
				case "PAST": 
 					echo "Este concurso ha terminado.";
				break;
				
				case "FUTURE": 
					echo "Este concurso iniciar&aacute; en "; 
					$datetime1 = date_create( $CDATA['Inicio']);
					$datetime2 = date_create(date("Y-m-d H:i:s"));
					$interval = date_diff($datetime1, $datetime2);
					
					if($interval->format('%D') > 0){
						echo "<b>" . $interval->format('%D') . "</b> dias.";	
					}else{

						?>
							<b><span id='time_left'><?php echo $interval->format('%H:%I:%S'); ?></span></b>.
							<script>
								function updateTime(){

									data = $("#time_left").html().split(":");
									hora = data[0];
									min = data[1];
									seg = data[2];
									
									if(--seg < 0){
										seg = 59;
										
										if(--min < 0){
											min = 59;

											if(--hora < 0){
												hora = 59;
											}
											
											hora = hora < 10 ? "0" + hora : hora;
										}
										
										min = min < 10 ? "0" + min : min;
									}

									seg = seg < 10 ? "0" + seg : seg;
																		
									if(hora == 0 && min == 0 && seg == 0)
									{
										//window.refresh();
										alert("OK");
										
									}
									
									//hora = hora < 10 ? "0" + hora : hora;

																							
									$("#time_left").html(hora+":"+min+":"+seg);
									
								}
								setInterval("updateTime()", 1000);
							</script>
						<?php
					}
					

				break;	
				
				case "NOW": 
					echo "Enviar Soluciones al concurso";
					$datetime1 = date_create( $CDATA['Final']);
					$datetime2 = date_create(date("Y-m-d H:i:s"));
					$interval = date_diff($datetime1, $datetime2);
					echo "<br><span id='time_left'>" . $interval->format('%H:%I:%S') . "</span> restante.";					
					
					if( ! isset($_SESSION['userID'] ) ){
						?> <div align="center">Debes iniciar sesion en la parte de arriba para poder enviar problemas a <b>Teddy</b>.</div> <?php
					}else{
						if( isset($_REQUEST["ENVIADO"]) )
							enviando();
						else
							imprimirForma();
					}
				break;
			}
			

			
			?>	
		</div>
	</div>
	
	
	
	
	<?php

	if( $STATUS == "NOW" || $STATUS  == "PAST" ){
		?>
		<!-- 
			RANK
		-->
		<div class="post_blanco" >
			<div style="font-size: 18px" align=center>Ranking</div>	
			<div id='ranking_div' align=center>
				<table border='0' style="font-size: 14px;" > 
				<thead> <tr >
					<th width='50px'>Rank</th> 
					<th width='12%'>Usuario</th> 
					<th width='50px'>Envios</th> 					
					<th width='50px'>Resueltos</th> 
					<?php
						
						$PROBLEMAS = explode(' ', $CDATA["Problemas"]);						

						for ($i=0; $i< sizeof( $PROBLEMAS ); $i++) {
							echo "<th width='100px'><a target='_blank' href='verProblema.php?id=" . $PROBLEMAS[$i]. "&cid=". $_REQUEST['cid']."'>".$PROBLEMAS[$i]."</a></th> ";
						}
					?>
					<th width='12%'>Penalty</th>
					</tr> 
				</thead> 
				<tbody id="ranking_tabla">

				</tbody>
				</table>
			</div>
			<script>
			
			var CurrentRank = null;
			
			function showRank(){
				//console.log("Mostrando rank", CurrentRank);
				$("#ranking_tabla").fadeOut("fast", function (){
					html = "";
					
					for( a = 0; a < CurrentRank.length; a++ )
					{	
						if(a%2 ==0){
							html += "<TR style=\"background:#e7e7e7; height: 50px;\">";
						}else{
							html += "<TR style=\"background:white; height: 50px;\">";
						}
						html +=  "<TD align='center' style='font-size: 18px' ><b>" +CurrentRank[a].RANK+ "</b></a></TD>";
						html +=  "<TD align='center' >" +CurrentRank[a].userID+"</a> </TD>";
						html +=  "<TD align='center' >" +CurrentRank[a].ENVIOS+"</a> </TD>";
						html +=  "<TD align='center' >" +CurrentRank[a].OK+"</a> </TD>";
						
						var problemas = [<?php foreach($PROBLEMAS as $p){echo $p . ",";}; ?>];
						//console.log(problemas)
						//console.log(CurrentRank[a].problemas)
						
						for( z = 0 ; z < problemas.length ; z++ ){
							foo = "";
							for ( p in CurrentRank[a].problemas  ){
								if(p == problemas[z]){
									//estoy en este problema
									foo = "x";
									//CurrentRank[a].problemas[p].bad
									if(CurrentRank[a].problemas[p].ok > 0){
										
										tiempo = parseInt(CurrentRank[a].problemas[p].ok_time / 60);
										tiempo += ":"; 
										bar = parseInt((parseInt(CurrentRank[a].problemas[p].ok_time % 60)));
										if(bar<=9){ bar = "0"+bar;}
										tiempo += bar;
										//tiempo += parseInt((parseInt(CurrentRank[a].problemas[p].ok_time % 60)*60)/100);
										/*
											100 - 60
											x - 
											(x*60)/100
										*/
										foo = "<b>" +  tiempo + "</b> / "+CurrentRank[a].problemas[p].ok_time+"<br>";
										foo += "("+CurrentRank[a].problemas[p].bad+")";
									}else{
										foo = "-"+CurrentRank[a].problemas[p].bad+"";
									}
									

									
								}
							}
							html +=  "<TD align='center' >" + foo +"</TD>";

						}

						html +=  "<TD align='center' >" +CurrentRank[a].PENALTY+" </TD>";
						html +=  "</TR>";

					}

					document.getElementById("ranking_tabla").innerHTML = html;
					
					$("#ranking_tabla").fadeIn();
				});

			}
			

			function askforrank (){
				$.ajax({
				  url: "ajax/rank.php",
				  data: "cid= <?php echo $_REQUEST['cid']; ?>",
				  cache: false,
				  success: function(data){
					CurrentRank = jQuery.parseJSON(data);
					showRank();
				  }
				})			
			}
			
			

			</script>
		</div>
		<?php
	}
	?>
	
	
	
	
	<?php
	/***********************************************
			RUNS
	 ***********************************************/
	if( $STATUS == "NOW" || $STATUS  == "PAST" ){
		?>
		<!-- 
			RUNS
		-->
		<div class="post" >
			<div style="font-size: 18px" align=center>Envios</div>
			<div id='runs_div' align=center>
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
				<tbody id="runs_tabla">

				</tbody>
				</table>
			</div>
			<script>
			
			var CurrentRuns = null;
			
			function showRuns(){
				//los runs han cambiado, entonces mostrar el rank
				askforrank();
				
				//console.log("Mostrando runs", CurrentRuns);
				
				$("#runs_div").fadeOut("fast", function (){
					html = "";

					for( a = 0; a < CurrentRuns.length; a++ )
					{	

						if(a%2 ==0){
							html += "<TR style=\"background:#e7e7e7;\">";
						}else{
							html += "<TR style=\"background:white;\">";
						}
						html +=  "<TD align='center' ><a href='verCodigo.php?METHOD=555&execID=" +CurrentRuns[a].execID+ "'>" +CurrentRuns[a].execID+ "</a></TD>";
						html +=  "<TD align='center' ><a href='verProblema.php?id=" +CurrentRuns[a].probID+"'>" +CurrentRuns[a].probID+"</a> </TD>";
						html +=  "<TD align='center' ><a href='runs.php?user=" +CurrentRuns[a].userID+"'>" +CurrentRuns[a].userID+"</a> </TD>";
						html +=  "<TD align='center' >" +CurrentRuns[a].LANG+"</TD>";
						html +=  "<TD align='center' >" +CurrentRuns[a].status+"</TD>";
						html +=  "<TD align='center' >" +(parseInt(CurrentRuns[a].tiempo)/1000)+" Seg. </TD>";
						html +=  "<TD align='center' >" +CurrentRuns[a].fecha+" </TD>";
						html +=  "</TR>";
					}

					document.getElementById("runs_tabla").innerHTML = html;
					$("#runs_div").fadeIn();
				})

			}
			
			
			function runsCallback(data){
				
				if(CurrentRuns === null){
					//es la primera vez
					CurrentRuns = data;
					showRuns();
					return;
				}
			
				if(CurrentRuns.length == data.length){
					// es el mismo, no hacer nada
					return;
				}
					
				CurrentRuns = data;
				showRuns();

			}
			
			function askforruns (){

				$.ajax({
				  url: "ajax/runs.php",
				  data: "cid= <?php echo $_REQUEST['cid']; ?>",
				  cache: false,
				  success: function(data){
					var obj = jQuery.parseJSON(data);
					runsCallback(obj);
				  }
				});	
				
				
				setTimeout("askforruns()",5000);		
			}
			
			askforruns();

			</script>
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

if( isset($resultado))
	mysql_free_result($resultado);

if( isset($enlace))
	mysql_close($enlace);

?>
