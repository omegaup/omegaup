<?php

	/* 
		sesion activa
	*/
function ok(){
	?>
	<div class="post">
		<div class="navcenter" align=center>
			<div align=center>
				Bienvenido <b><?php echo $_SESSION['userID']; ?></b> !<br>
			</div>

			<span style="float: left;">
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="runs.php?user=<?php echo $_SESSION['userID']; ?>"><img src="img/67.png" > Mi perfil</a>
			</span>

			<div style="float: left;" >
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="editprofile.php"><img src="img/71.png" > Editar tu perfil</a>
			</div>
			
			<span style="float: left;">
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="problemas.php?userID=<?php echo $_SESSION['userID']; ?>"><img src="img/67.png" > Problemas no resueltos</a>
			</span>
			
			<!--	
			<span style="float: left;">
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="submit.php"><img src="img/67.png" > Aportar con un problema</a>
			</span>
			-->

			<?php

				//buscar mensajes no leidos para este usuario
				$consulta = "select id from Mensaje where para = '{$_SESSION["userID"]}' AND unread = '1';";
				$resultado = mysql_query($consulta) or die('Donte be evil with teddy :P ' . mysql_error());
				
				?>
				<div style="float: left;" >
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="inbox.php"><img src="img/71.png" > Mensajes (<?php echo mysql_num_rows($resultado) ?>)</a>
				</div>
				<?php
			?>

				
			<?php if (($_SESSION["userMode"] == "ADMIN")||($_SESSION["userMode"] == "OWNER")) { ?>
				<div style="float: left;" >
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="admin/"><img src="img/71.png" >Administracion</a>
				</DIV>
			<?php } ?>


			<div style="float: left;">
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="includes/login_app.php?log_out=logout"><img src="img/55.png" > Cerrar Sesion</a>
			</div>

		</div>
		&nbsp;
	</div>
	<?php
	}


	/* 
		no hay sesion activa
	*/
	function none(){
	?>
	<script>
	function encriptar(xD, mensaje){ 
		var __ = xD; var _ = parseInt(Math.random()*32); var ____ = ""; for (o_O=0;o_O<__.length; o_O++) { u_U = parseInt(__.charCodeAt(o_O)); if(o_O%2==0 ) u_U += _; else u_U -= _; var $_$ = u_U.toString(2); while( $_$.length < 9) $_$ = "0" + $_$; ____ += $_$; } _ = parseInt(_).toString(2); while( _.length < 9 ) _ = "0" + _; __ = _ + "" +____;var ___ = ""; var _____ = 0; for( i = 0; i < __.length; i++){ ___ += __.charAt(i) == 0 ? mensaje.charAt(_____) : mensaje.charAt( _____ ).toUpperCase(); if(_____ == (mensaje.length - 1)) _____ = 0; else _____++; /* alanBoy */ } return ___; 
	}

	function lost_returned(data){
		$('#message').fadeOut('slow', function() {
			alert(data.responseText);
			try{
				x = jQuery.parseJSON( data.responseText );
			}catch(e){
				//invalid json
				alert("Algo anda mal con teddy. Por favor envia un mail a alan@clubdeprogra.com si este problema persiste.");
				location.reload(true);
				return;
			}
		
		
			if(x.success){
				alert("Se ha enviado un correo a este usuario con instrucciones para obtener una nueva contrase&ntilde;a");
				$('#login_area').slideDown('slow');
			}else{
				alert(x.reason);
				$('#login_area').slideDown('slow');
			}
		});//efecto

	}

	function lost(){
		
		if($("#user").val().length < 2){
			alert("Escribe tu nombre de usuario o correo electronico en el campo.");
			return;
		}

		$('#login_area').slideUp('slow', function() {

				$('#wrong').slideUp('slow');

		    		$('#message').slideDown('slow', function() {
					//actual ajax call
					$.ajax({ 
						url: "ajax/lost_pass.php", 
						dataType: 'json',
						data: { 'user' : document.getElementById("user").value },
						context: document.body, 
						complete: lost_returned
					});
			  	});
		  	});

	}
	</script>

	<div class="post" >
		<div id="login_area" class="navcenter">
			<form method="post" onSubmit="return submitdata()">
				<img  src="img/37.png"> <input type="text" value="" id="user" placeholder="usuario">
				<img  src="img/55.png"> <input type="password" value="" id="pswd" placeholder="contrase&ntilde;a">
				<input type="submit" value="Iniciar Sesion">
				<input type="button" onClick="lost()" id="lost_pass" value="Olvide mi contase&ntilde;a">
			</form>

		</div>
		<div align=center id="wrong" style="display:none;">
			<img  src="img/12.png"> Datos invalidos
		</div>
		<div align=center id="message" style="display:none">
			<img src="img/load.gif">
		</div>
	</div>
	<script>
		//fade content
		function submitdata(){

			$('#login_area').slideUp('slow', function() {

				$('#wrong').slideUp('slow');

		    		$('#message').slideDown('slow', function() {
					//actual ajax call
					$.ajax({ 
						url: "includes/login_app.php", 
						dataType: 'json',
						data: { 'user' : document.getElementById("user").value, 'pswd': encriptar( document.getElementById("pswd").value, "teddy" ) },
						context: document.body, 
						complete: returned
					});
			  	});
		  	});
			
			return false;
		}
		
		
		function returned( data ){
			$('#message').fadeOut('slow', function() {
				var x = null;
				
				try{
					x = jQuery.parseJSON( data.responseText );
				}catch(e){
					//invalid json
					alert("Algo anda mal con teddy. Por favor envia un mail a alan@clubdeprogra.com si este problema persiste.");
					console.log(x,e)
					//location.reload(true);
					return;
				}
				
				if(x.badguy){
					document.getElementById("login_area").innerHTML = x.msg;
					$("#login_area").slideDown("slow");
					return;
				}
				
				if(x.sucess){
					location.reload(true);
				}else{
					$("#wrong").slideDown("slow", function (){ 
						$('#login_area').slideDown('slow', function() {
					   		$("#login_area").effect("shake", { times:2 }, 100);
						});					
					});

				}
			});//efecto
		}
	</script>
	<?php
	}



	if(isset($_SESSION['status']) && $_SESSION['status'] == "OK") { 
		ok();
	}else{
		none();
	}

?>
