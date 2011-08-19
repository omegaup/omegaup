<?php
	define( "LEVEL_NEEDED", false );
	
	require_once( "../../server/inc/bootstrap.php" );


	require_once( "../../server/controllers/problems.controller.php" );
	
		$rs = array("0"=>"","problem_id"=>"-1",
					"1"=>"","title"=>"",
					"2"=>"","public"=>"",
					"3"=>"","time_limit"=>"",
					"4"=>"","memory_limit"=>"",
					"5"=>"","visits"=>"",
					"6"=>"","submissions"=>"",
					"7"=>"","accepted"=>"",
					"8"=>"","difficulty"=>"");
	
?>

<!DOCTYPE html> 
<html xmlns="http://www.w3.org/1999/xhtml "
      xmlns:fb="http://www.facebook.com/2008/fbml ">
<head>
	<link rel="stylesheet" type="text/css" href="css/format1.css"/>
	<link rel="stylesheet" type="text/css" href="css/facebox.css"/>
	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/facebox.js"></script>
	
	<script type="text/javascript" src="js/ajaxupload.js"></script>
	<script type="text/javascript" src="js/scripts.js"></script>
	<title> Admin </title>
</head>
<body>
	<div id="dat" class="dat">

		<table width="100%">
			<tr>
				<td colspan="3">

					<div class="datos_opciones">
						Administraci&oacute;n.<br/>
					</div>

				</td>
			</tr>
			<tr >
				<td colspan="2" class="datos_opciones">
					<div class="boton" id="bm1">
						Nuevo
					</div>
					<div class="boton" id="bm2">
						Guardar
					</div>
					<div class="boton" id="bm3">
						Eliminar
					</div>
				</td>				
				<td rowspan="<?php echo (sizeof($rs));?>" class="images" id="images">
					<div id="img" class="img">
						
					</div>
					<div class="boton" id="upload_img">
							Subir Imagen
					</div>
				</td>
			</tr>
			
			<tr><td></td>
				
				<td rowspan="<?php echo (sizeof($rs)-4);?>" class="text" id="text">
					<div class="datos_opciones_b" id="b1">
						Problema
					</div>
					<div class="datos_opciones_b" id="b2">
						Caso prueba (input)
					</div>
					<div class="datos_opciones_b" id="b3">
						Caso prueba (output)
					</div>
					</br>
					<div id="texto">					
						<div >
						Problema <div id="showHtmlProblem" rel="facebox" > ( click previsualización ) </div>
						</div>
						<form action="foto.php"	enctype="multipart/form-data" method="post" target="oculto">
							<textarea class="source" name="tex" id="tex"></textarea>
							<hr/>
							 <center> &oacute; bien </center>
							<div class="boton" id="upload_text">
								 Cargar un archivo
							</div>
					</div>
					<div id="input">
						<div >
							Caso prueba (input)
						</div>
						
							<textarea class="source" name="in" id="in"></textarea>

							<hr/>
							<center> &oacute; bien </center>
							<div class="boton" id="upload_in">
								Cargar un archivo
							</div>
						
					</div>
					<div id="output">
						<div >
							Caso prueba (output)
						</div>
						
							<textarea class="source" name="out" id="out"></textarea>							
							<hr/>
							<center> &oacute; bien </center>
							<div class="boton" id="upload_out">
								Cargar un archivo
							</div>
						
					</div>
				</td> 
					
			</tr>
			<tr>
				<td class="datos">

					Buscar:<br/>
				
					<input type="text" id="buscar_s" value="Buscar" class="search"  name="buscar_s"
					onclick="borraG(this,1,'Buscar');" 
					onfocus="borraG(this,1,'Buscar');" 
					onblur="borraG(this,2,'Buscar');"/>
				</td>				
			</tr>
			<?php $j=0; $i=0;  foreach( $rs as $key => $value ){ if(!$i){ $i=1; continue;}
					if($key == "problem_id"){
			?>			
								<input type="hidden" id="<?php echo $key; ?>" value="<?php echo $value; ?>"/>
						
			<?php } else {?>
			<tr>
				<td class="datos">

					<?php echo $key; ?>:<br/>
					<input type="hidden" id="<?php echo $key; ?>" value="<?php echo $value; ?>"/>
					<input type="text" id="<?php echo $key; ?>_t" value="<?php echo $value; ?>" class="search2"  name="<?php echo $key; ?>"
					onclick="borraG(this,1);" 
					onfocus="borraG(this,1);" 
					onblur="borraG(this,2);"/>
				</td>
			</tr>
			<?php } $i=0; $j++;}?>
			<tr>
				<td class="datos_opciones" rowspan="4">
					
				</td>
			</tr>
		</table>
	</div><br/>
	<div id="sugg" name="sugg" ></div>	
	<div id="pre" name="pre" ></div>
</body>
</html>