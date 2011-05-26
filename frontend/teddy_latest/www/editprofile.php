<?php
	session_start();

	include_once("config.php");
	include_once("includes/db_con.php");
		
	if(isset($_REQUEST["form"]) && ($_REQUEST["form"] == true)):

		$nombre = addslashes($_REQUEST["nombre"]);
		$email = addslashes($_REQUEST["email"]);
	//	$ubicacion = addslashes($_REQUEST["ubicacion"]);
		$escuela = addslashes($_REQUEST["escuela"]);
		$form = addslashes($_REQUEST["form"]);

		$twitter = addslashes($_REQUEST["twitter"]);

		$query = "update  `teddy`.`Usuario` SET  nombre = '{$nombre}', escuela = '{$escuela}', mail = '{$email}', `twitter` =  '{$twitter}' WHERE  `Usuario`.`userID` =  '{$_SESSION['userID']}' LIMIT 1 ;";

		$rs = mysql_query($query) or die(mysql_error());

	endif;

	
?>

<html xml:lang="es" xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta content="es_MX" http-equiv="Content-Language" />
	<script src="js/jquery.min.js"></script>
	<script src="js/jquery-ui.custom.min.js"></script>
	<link media="all" href="css/teddy_style.css" type="text/css" rel="stylesheet" />
	<script type="text/javascript" src="prototype-1.6.0.2.js"></script>
	<title>Teddy Online Judge - Editar Perfil</title>
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
	<div class="post" style="background:white; " >
		<?php function showContent($datos){ ?>
		<form action="" method="post" onsubmit="return validate()" class="datos" style="width: 500px; 
				margin:auto;
				margin-top:30px;
				padding:30px;
				border:1px solid #bbb;
				-moz-border-radius:11px;">
				
		<?php if(isset($_REQUEST["form"]) && ($_REQUEST["form"] == true)): ?>
			<?php if(true): ?>
				<h2>Yeah !!   &nbsp;&nbsp;&nbsp;  :-)</h2>
				<p>
					Hola <b><?php echo $_SESSION['userID'] ?></b> ! Haz editado correctamente tus datos !<br/> Saluda a Teddy de mi parte. <br><div align="right">Atte: <b>El script de edicion de perfiles. </b></div>
				</p>
			<?php else: ?>
				<h2>Ups :-(</h2>
				<p>
					Una de dos... o ya hay un usuario con este <b>nick</b> o ya esta registrado este <b>mail</b>. Hmm puedes intentar resetear tu contrase&ntilde;a o bien <a href="javascript:history.go(-1);">regresar</a> e intentar de nuevo. <br><div align="right">Atte: <b>El script de edicion de perfiles. </b></div>
				</p>
			<?php endif; ?>
		<?php else: ?>
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
			
<!--
			<label for="password">
				Password:
			</label>
			<input type="password" id="password" name="password" class="text" value=""/>
			<label for="re_password">
				Confirma Password:
			</label>
			<input type="password" id="re_password" name="re_password" class="text" value=""/>
-->
<!--
			<label>
				Ubicación:
			</label>
			<select id="ubicacion" name="ubicacion" >
				<option value="Aguascalientes" >Aguascalientes</option>
				<option value="Baja California">Baja California</option>
				<option value="Baja California Sur">Baja California Sur</option>
				<option value="Campeche">Campeche</option>
				<option value="Chiapas">Chiapas</option>
				<option value="Chihuahua">Chihuahua</option>
				<option value="Coahuila de Zaragoza">Coahuila de Zaragoza</option>
				<option value="Colima">Colima</option>
				<option value="Distrito Federal">Distrito Federal</option>
				<option value="Durango">Durango</option>
				<option value="Guanajuato">Guanajuato</option>
				<option value="Guerrero">Guerrero</option>
				<option value="Hidalgo">Hidalgo</option>
				<option value="Jalisco">Jalisco</option>
				<option value="Mexico">Mexico</option>
				<option value="Michoacan de Ocampo">Michoacan de Ocampo</option>
				<option value="Morelos">Morelos</option>
				<option value="Nayarit">Nayarit</option>
				<option value="Nuevo Leon">Nuevo Leon</option>
				<option value="Oaxaca">Oaxaca</option>
				<option value="Puebla">Puebla</option>
				<option value="Queretaro de Arteaga">Queretaro de Arteaga</option>
				<option value="Quintana Roo">Quintana Roo</option>
				<option value="San Luis Potosi">San Luis Potosi</option>
				<option value="Sinaloa">Sinaloa</option>
				<option value="Sonora">Sonora</option>
				<option value="Tabasco">Tabasco</option>
				<option value="Tamaulipas">Tamaulipas</option>
				<option value="Tlaxcala">Tlaxcala</option>
				<option value="Veracruz-Llave">Veracruz-Llave</option>
				<option value="Yucatan">Yucatan</option>
				<option value="Zacatecas">Zacatecas</option>
				<option value="other">Fuera de Mexico</option>
			</select>
-->
			<label>
				Escuela de Procedencia :
			</label>
			<input type="text" id="escuela" name="escuela" class="text" value="<?php echo $datos['escuela']; ?>"/>
			<input type="submit" class="button" value="Actualizar mis datos" />
			<input type="hidden" id="form" name="form" value="false" />
			<?php endif; ?> 
		</form>
		<?php } ?>
		
		
		
		<?php 
			if( ! isset($_SESSION['userID'] ) ){
				?> <div align="center">Debes iniciar sesion en la parte de abajo para poder editar tus datos.</div> <?php
			}else{
				
				
					//mysql_query("select * from Usuario where userID = '". slashes() ."'")
					$query = sprintf("select * FROM Usuario WHERE userID='%s'", mysql_real_escape_string($_SESSION['userID']));
					//echo $query;
					$foo = mysql_query($query) or die(mysql_error());
					//echo ">" . mysql_num_rows($foo). "< <br>";
					$r = mysql_fetch_array($foo);
					//var_dump($r);
					showContent($r);
			}
		?>
	</div>

	

	<?php include_once("includes/footer.php"); ?>

</div>
<?php include("includes/ga.php"); ?>
</body>
</html> 
 
