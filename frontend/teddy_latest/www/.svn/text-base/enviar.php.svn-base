<?php 
	session_start(); 
	include_once("config.php");
	include_once("includes/db_con.php");

?>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="css/teddy_style.css" />
		<title>Teddy Online Judge - Ver Problema</title>
		<script src="js/jquery.min.js"></script>
		<script src="js/jquery-ui.custom.min.js"></script>

       <!--  
		<script type="text/javascript" src="uploadify/jquery-1.3.2.min.js"></script> 
		-->
        <script type="text/javascript" src="uploadify/swfobject.js"></script>
        <script type="text/javascript" src="uploadify/jquery.uploadify.v2.1.0.min.js"></script>
		<link rel="stylesheet" type="text/css" href="uploadify/uploadify.css" />
	</head>
<body>

<div class="wrapper">
	<div class="header">
		<h1>teddy online judge</h1>
		<h2>teddy es un oso de peluche</h2>
	</div>

	<?php include_once("includes/menu.php"); ?>
	<?php include_once("includes/session_mananger.php"); ?>

	<div class="post_blanco" style="background:white;" >

    <div class="subtitle" align="center">enviar solucion</div>


<?php

	/*
		Imprimir la forma para enviar soluciones.
	*/
	function imprimirForma(){
		?>
		<div align="center" id="form" >
			<table border=0 cellspacing="10"> 
				 <tr><td  style="text-align: right">Codigo fuente&nbsp;&nbsp;</td><td> 
					<input id="fileInput" name="fileInput" type="file" /></td></tr> 

				 <tr><td style="text-align: right">Problema&nbsp;&nbsp;</td><td> 
					<input type="text" id="prob" name="prob" size="5" value="<?php if(isset($_GET['pid'])) echo $_GET['pid']; ?>" maxlength="5"> 
				 </td></tr> 

				 <tr><td></td><td><div id="submit_b" style="display:none;"><input type="button" value="Enviar Solucion" onclick="doStart()" ></div></td></tr> 
			</table> 
		</div>
		<div align="center" id="result">
		</div>



        <script type="text/javascript">// <![CDATA[
			//datos sobre el archivo a enviar
			var file;
			var endName;

			
			/*
		 	* Inicio de envio....
		 	*/
			function doStart(){

				//revisar que el problema no este vacio y que sea un entero
				if($('#prob').val().length == 0){
					alert("Escribe el ID del problema");
					return;
				}
				
				//buscar si este problema existe, de ser asi... regresarme un id
				//para este nuevo archivo
				$.ajax({ 
						url: "ajax/enviar.php", 
						
						context: document.body, 
						
						data: {
							filename : file.name,
							probID: $('#prob').val()
						},
						success: function(data){

 							if(data == 'UNKNOWN_PROBID'){
								alert('Este problema no existe');
								return;
							}

							endName = data;
							$('#fileInput').uploadifySettings('scriptData' , {'fnombre':  endName });
							$('#fileInput').uploadifyUpload();
			        
				  		},
						failure: function (){
							alert("Algo anda mal, intenta de nuevo.");
						}
				});
			}
			

			
			/*
			 * Termino de subirse el archivo
			 */
			function fileUploadComplete(){
				$("#form").fadeOut();
				$("#result").fadeIn();
				requestResult();
			}
			

			/*
			 * Document ready
			 */
            $(document).ready(function() {
                $('#fileInput').uploadify({
                    'uploader'  : 'uploadify/uploadify.swf',
                    'script'    : 'uploadify/uploadify.php',
                    'cancelImg' : 'uploadify/cancel.png',
                    'auto'      : false,
                    'height'    : 30,
                    'sizeLimit' : 1024*1024,
                    'buttonText': 'Buscar Archivo',
					'fileDesc' :'Codigo Fuente',
					'fileExt': '*.c;*.cpp;*.java;*.cs;*.perl;*.py',
                    'onSelect'  : function (e, q, f)  { 
							file = f;
							$("#submit_b").fadeIn();
							
						},
                    'onCancel'  : function ()  { 
							$("#submit_b").fadeOut();
							
						},
                    'onComplete'  : function (){ 
							fileUploadComplete();
						},
				 	'onError'  : function (){ 
							alert('Error');
					}
                });
            });


			function showResult(data){
				var html = data.status;
				//console.log(data)
				$("#result").html( html );

			}


			function showWait(){
				
				$("#result").html( "revisando problema...." );
				
			}


			var isFirst = true;

			function requestResult(){
				
				var execID = endName.split(".")[0];
				
				$.ajax({ 
						url: "ajax/run_status.php", 
						
						context: document.body, 
						
						data: {
							execID : execID
						},
						success: function(data){
							
							j = jQuery.parseJSON(data);
							
							if( j.status == "WAITING" || j.status == "JUDGING" ){
								
								
								if( isFirst ){
									//mostrar un html de espera									
									isFirst = false;
									showWait();

								}

								
								//volver a revisar el estado en uno o medio segundo
								setTimeout("requestResult()", 1000);
								
							}else{
								
								showResult( j );								
								
							}
							

			        		
				  		},
						failure: function (){
							alert("Algo anda mal, intenta de nuevo.");
						}
				});
				
				
			}

        // ]]></script>
		<?php



	}



		
	/* **************
		Start
	************** */

	if( ! isset($_SESSION['userID'] ) ){
		?> <div align="center">Debes iniciar sesion en la parte de arriba para poder enviar problemas a <b>Teddy</b>.</div> <?php
	}else{
			imprimirForma();
	}

	if( isset($resultado))
		mysql_free_result($resultado);

	if( isset($enlace))
		mysql_close($enlace);


	?>

	</div>



	<?php include_once("includes/footer.php"); ?>

</div>
<?php include("includes/ga.php"); ?>
</body>
</html>

