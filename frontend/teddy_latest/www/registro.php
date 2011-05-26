<?php
	session_start();
	include_once("config.php");
	include_once("includes/db_con.php");

	if(isset($_REQUEST["form"]) && ($_REQUEST["form"] == true)):

		$nombre = addslashes($_REQUEST["nombre"]);
		$email = addslashes($_REQUEST["email"]);
		$password = crypt(addslashes($_REQUEST["password"]));
		$ubicacion = addslashes($_REQUEST["ubicacion"]);
		$escuela = addslashes($_REQUEST["escuela"]);
		$form = addslashes($_REQUEST["form"]);
		$nick = addslashes($_REQUEST["nick"]);
		$twitter = addslashes($_REQUEST["twitter"]);
	
		$query = "select * from Usuario where userID = '$nick' or mail = '$email'";
		$rs = mysql_query($query) or die('Algo anda mal: ' . mysql_error());

		$validate = false;		

		if(mysql_numrows($rs)==0){
			$query = "insert into Usuario(userID, nombre, pswd, ubicacion, escuela, mail, twitter) 
			values ('$nick','$nombre','$password','$ubicacion','$escuela', '$email', '$twitter')";
			$rs = mysql_query($query) or die("upts");
			$validate = true; 
		}



	endif;
	
?>

<html xml:lang="es" xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta content="es_MX" http-equiv="Content-Language" />

	<link media="all" href="css/teddy_style.css" type="text/css" rel="stylesheet" />
	<script src="js/jquery.min.js"></script> 
			<script src="js/jquery-ui.custom.min.js"></script>
	<style>

		.post>form{
			width:400px;
			margin:auto;
			margin-top:30px;
			padding:30px;
			border:1px solid #bbb;
			-moz-border-radius:11px;
		}

		.post>form label{
			display:block;
			color:#777777;
			font-size:13px;
		}
		.post>form p{
			color:#777777;
			font-size:14px;
			text-align:justify;
			margin-bottom:20px;
		}
		.post>form input.text{
			background:#FBFBFB none repeat scroll 0 0;
			border:1px solid #E5E5E5;
			font-size:24px;
			margin-bottom:16px;
			margin-right:6px;
			margin-top:2px;
			padding:3px;
			width:97%;
		}
		.post>form select{
			background:#FBFBFB none repeat scroll 0 0;
			border:1px solid #E5E5E5;
			font-size: 12px;
			margin-bottom:16px;
			margin-right:6px;
			margin-top:2px;
			padding:3px;
			width:80%;
		}
		.post>form input.button {
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


			if( $('#nombre')[0].value.length<7){
				return Array("Ingrese su nombre completo por favor.", $('#nombre')[0]);
			}
			

			
			if (!(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test($('#email')[0].value))){
				return Array("Ingrese un email valido.", $('#email')[0]);
			}

			if($("#nick")[0].value.indexOf(" ") > -1){
				return Array("Tu nick no puede contener espacios.", $('#nick')[0]);
			}
			
			if($("#twitter")[0].value.indexOf("@") != -1){
				//return Array("Tu id de twitter sin el arroba plis :P", $('twitter'));
				$("#twitter")[0].value = $("#twitter")[0].value.substring(1);
			}
			
			if($("#nick")[0].value.length < 5){
				return Array("Tu nick no debe ser menor a 5 caracteres.", $('#nick')[0]);
			}
			


			if($("#password")[0].value.length<5){
				return Array("Ingresa un password con una logitud de 5 caracteres.", $('#password')[0]);
			}
			if($("#password")[0].value != $("#re_password")[0].value){
				return Array("Los passwords ingresados no son iguales. Confirma nuevamente tu password", $("#re_password")[0]);
			}
			if($("#escuela")[0].value.length==0){
				return Array("Ingresa tu escuela de procedencia, es muy importante para para nosotros. Gracias", $('#escuela')[0]);
			}
			return true;
		}
		
		function validate(){


			rs = _validate();
			console.log("validando", rs)

			
			if(rs=== true){
				$("form").value="true";
				return true; 
			}else {
				alert(rs[0]);
				rs[1].focus();
				rs[1].select();
				return false;
			}

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
		<?php if(isset($_REQUEST["form"]) && ($_REQUEST["form"] == true)): ?>					
			<?php if($validate): ?>
				<h2>Yeah !!   &nbsp;&nbsp;&nbsp;  :-)</h2>
				<p>
					Hola <b><?php echo $nick ?></b> ! Haz sido seleccionado de una lista de miles para poder enviar problemas a Teddy ! Saluda a Teddy de mi parte. <br><div align="right">Atte: <b>El script de inscripcion </b>
				</p>
				</div>
			<?php else: ?>
				<h2>Ups :-(</h2>
				<p>
					Una de dos... o ya hay un usuario con este <b>nick</b> o ya esta registrado este <b>mail</b>. Hmm puedes intentar resetear tu contrase&ntilde;a o bien <a href="javascript:history.go(-1);">regresar</a> e intentar de nuevo. <br><div align="right">Atte: <b>El script de inscripcion </b></div>
				</p>
			<?php endif; ?>
		<?php else: ?>
			<p>
			Ingresa los datos necesarios para registrarte en el Juez Teddy.
			</p>
			<label for="nombre">
				Nombre Completo:
			</label>
			<input type="text" id="nombre" name="nombre" class="text" />
			<label for="email">
				Correo :
			</label>
			<input type="text" id="email" name="email" class="text" />

			<label for="twitter">
				twitter :
			</label>
			<input type="text" id="twitter" name="twitter" class="text" />

			<label for="nick">
				Nick (sin espacios):
			</label>
			<input type="text" id="nick" name="nick" class="text" />
			<label for="password">
				Password:
			</label>
			<input type="password" id="password" name="password" class="text" />
			<label for="re_password">
				Confirma Password:
			</label>
			<input type="password" id="re_password" name="re_password" class="text" />
			<label>
				Ubicación:
			</label>
			<select id="ubicacion" name="ubicacion" >
				<script language="javascript">
				
				var states = new Array("Mexico","Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burma", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Central African Republic", "Chad", "Chile", "China", "Colombia", "Comoros", "Congo, Democratic Republic", "Congo, Republic of the", "Costa Rica", "Cote d'Ivoire", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Fiji", "Finland", "France", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Greece", "Greenland", "Grenada", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, North", "Korea, South", "Kuwait", "Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Macedonia", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania", "Mauritius", "Mexico", "Micronesia", "Moldova", "Mongolia", "Morocco", "Monaco", "Mozambique", "Namibia", "Nauru", "Nepal", "Netherlands", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Norway", "Oman", "Pakistan", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal", "Qatar", "Romania", "Russia", "Rwanda", "Samoa", "San Marino", " Sao Tome", "Saudi Arabia", "Senegal", "Serbia and Montenegro", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "Spain", "Sri Lanka", "Sudan", "Suriname", "Swaziland", "Sweden", "Switzerland", "Syria", "Taiwan", "Tajikistan", "Tanzania", "Thailand", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe");

				for(var hi=0; hi<states.length; hi++) 
				document.write("<option value=\""+states[hi]+"\">"+states[hi]+"</option>");
				</script>
			</select>
			<label>
				Escuela de Procedencia :
			</label>
			<input type="text" id="escuela" name="escuela" class="text" />
			<input type="submit" class="button" value="Registrar" />
			<input type="hidden" id="form" name="form" value="false" />
			<?php endif; ?> 
		</form>
	</div>



	<?php include_once("includes/footer.php"); ?>

</div>
<?php include("includes/ga.php"); ?>
</body>
</html> 
 
