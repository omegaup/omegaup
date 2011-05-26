<?php


	/*

		Enviar problemas a Teddy

	*/
	session_start();

	include_once("config.php");
	include_once("includes/db_con.php");
		


	
?>

<html xml:lang="es" xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta content="es_MX" http-equiv="Content-Language" />

	<title>Teddy Online Judge - Aportar Problema</title>

	<script src="js/jquery.min.js"></script>
	<script src="js/jquery-ui.custom.min.js"></script>
	<link media="all" href="css/teddy_style.css" type="text/css" rel="stylesheet" />
	<script type="text/javascript" src="prototype-1.6.0.2.js"></script>
	<style>

		.datos form {
			width:400px;
			margin:auto;
			margin-top:30px;
			padding:30px;
			border:1px solid #bbb;
			-moz-border-radius:11px;
		}
		.datos label{
			display:block;
			color:#777777;
			font-size:13px;
		}
		.datos p{
			color:#777777;
			font-size:14px;
			text-align:justify;
			margin-bottom:20px;
		}
		.datos input.text{
			background:#FBFBFB none repeat scroll 0 0;
			border:1px solid #E5E5E5;
			font-size:24px;
			margin-bottom:16px;
			margin-right:6px;
			margin-top:2px;
			padding:3px;
			width:97%;
		}
		.datos select{
			background:#FBFBFB none repeat scroll 0 0;
			border:1px solid #E5E5E5;
			font-size: 12px;
			margin-bottom:16px;
			margin-right:6px;
			margin-top:2px;
			padding:3px;
			width:80%;
		}
		
		.datos input.button {
			-moz-border-radius-bottomleft:6px;
			-moz-border-radius-bottomright:6px;
			-moz-border-radius-topleft:6px;
			-moz-border-radius-topright:6px;
			border:1px solid #AAAAAA;
			font-size:16px;
			padding:3px;
		}
		
		.right{
			text-align:right;
		}
		
	</style>
	<script language="javascript">
		function _validate(){
			if($('nombre').value.length<7){
				return Array("Ingrese su nombre completo por favor.", $('nombre'));
			}
			if (!(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test($('email').value))){
				return Array("Ingrese un email valido.", $('email'));
			}

			if($("nick").value.indexOf(" ") > -1){
				return Array("Tu nick no puede contener espacios.", $('nick'));
			}
			
			if($("twitter").value.indexOf("@") != -1){
				//return Array("Tu id de twitter sin el arroba plis :P", $('twitter'));
				$("twitter").value = $("twitter").value.substring(1);
			}
/*
			if($("password").value.length<5){
				return Array("Ingresa un password con una logitud de 5 caracteres.", $('password'));
			}
			if($("password").value!=$("re_password").value){
				return Array("Los passwords ingresados no son iguales. Confirma nuevamente tu password", $("re_password"));
			}
			
*/
			if($("escuela").value.length==0){
				return Array("Ingresa tu escuela de procedencia, es muy importante para para nosotros. Gracias", $('escuela'));
			}
			return true;
		}
		
		function validate(){

			rs = _validate();

			if(rs==true){
				$("form").value="true";
				return true; 
			}
			else {
				alert(rs[0]);
				rs[1].focus();
				rs[1].select();
			}
			return false;
		}
	</script>
</head>
<body >
<div class="wrapper">
	<div class="header">
		<h1>teddy online judge</h1>
		<h2>teddy es un oso de peluche</h2>
	</div>

	<?php include_once("includes/menu.php"); ?>

	<?php include_once("includes/session_mananger.php"); ?>

	<div class="post" style="background:white;">
		<form action="" method="post" onsubmit="return validate()">
			<p>
				Aqui puedes cambiar los datos de tu perfil. Todo menos tu nick.
			</p>
			
			<label for="nick">
				Nick :
			</label>
			<input type="text" id="nick" name="nick" class="text" value="<?php echo $datos['userID']; ?>" DISABLED/>
			
			<label for="nombre" class="datos">
				Nombre Completo:
			</label>
			<input type="text" id="nombre" name="nombre" class="text datos" value="<?php echo $datos['nombre']; ?>"/>
			
			<label for="email" class="datos">
				Correo :
			</label>
			<input type="text" id="email" name="email" class="text datos" value="<?php echo $datos['mail']; ?>"/>
			
			<label for="twitter">
				twitter :
			</label>
			<input type="text" id="twitter" name="twitter" class="text" value="<?php echo $datos['twitter']; ?>"/>
			

			<label>
				Escuela de Procedencia :
			</label>
			<input type="text" id="escuela" name="escuela" class="text" value="<?php echo $datos['escuela']; ?>"/>
			<input type="submit" class="button" value="Actualizar mis datos" />
			<input type="hidden" id="form" name="form" value="false" />
		</form>
	</div>

	

	<?php include_once("includes/footer.php"); ?>

</div>
<?php include("includes/ga.php"); ?>
</body>
</html> 
 
