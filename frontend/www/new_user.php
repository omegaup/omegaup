<?php

	/*
	 * LEVEL_NEEDED defines the users who can see this page.
	 * Anyone without permission to see this page, will	
	 * be redirected to a page saying so.
	 * This variable *must* be set in order to bootstrap
	 * to continue. This is by design, in order to prevent
	 * leaving open holes for new pages.
	 * 
	 * */
	define( "LEVEL_NEEDED", false );


	require_once( "../server/inc/bootstrap.php" );

?>  

<!DOCTYPE html> 
<html xmlns="http://www.w3.org/1999/xhtml "
      xmlns:fb="http://www.facebook.com/2008/fbml ">

	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
	<?php echo $GUI::getExternalCSS(); ?>
		<script>
			/* scripting for this particular page */
			function send(){
				
				if($("#registry_form #p1").val() != $("#registry_form #p2").val()){
					$("#reg_error").html("passwords must match");
					$("#reg_error").slideDown();
					return;
				}
				
				var r = new Registry();
				var res =  r.validate_basic_user_registration( 
						$("#registry_form #name").val(),
						$("#registry_form #email").val(),
						$("#registry_form #p1").val()
					 );
					
				if(!res.valid){
					$("#reg_error").html(res.reason);
					$("#reg_error").slideDown();
					return;
				}
				
				$("#reg_error").slideUp();
				
				//everything is ok, send the registration
				r.send_basic_registration(
						$("#registry_form #name").val(),
						$("#registry_form #email").val(),
						$("#registry_form #p1").val(),
						back_from_ajax
					);
			}
			
			var back_from_ajax = function(responseObj){
				if(responseObj.success){
					//everything went smooth
					window.location = "home.php";
				}else{
					$("#reg_error").html(responseObj.reason);
					$("#reg_error").slideDown();
					return;
				}
			}
			/* scripting for this particular page */			
		</script>
	</head>
	
	<body>
	<div id="wrapper">
		<div class="login_bar"></div> 
		<div id="title"><?php echo $GUI::getHeader(); ?></div>
	    
		<div id="content">
			
			
			<div class="post footer"><?php echo $GUI::getMainMenu(); ?></div>

			<div class="post">

	           <div class="title">Registro</div>

				<div class="copy">
						<table id="registry_form" border=0>
							<tr>
								<td>Nombre</td><td><input id="name" type="text" ></td>
							</tr>
							<tr>
								<td>Tu correo electronico</td><td><input id="email" type="text" ></td>
							</tr>
							<tr>
								<td>Tu nueva contrase&ntilde;a</td><td><input id="p1" type="text" ></td>
							</tr>
							<tr>
								<td>Tu nueva contrase&ntilde;a</td><td><input id="p2" type="text" ></td>
							</tr>
							<tr>
								<td></td><td><input type="button" value="Registrarme" onClick="send()"></td>
							</tr>
							<tr>
								<td colspan=2 class="error" id="reg_error" style="display:none" ></td>
							</tr>
						</table>
				</div>
				<!-- .copy -->

			</div>
			<!-- .post -->
		
		
		
		    <div class="post footer">
		        <ul>
		            <li class="first"><a href=""></a></li>
		        </ul>
		
		        <p><?php echo $GUI::getFooter(); ?></p>
		    </div>
			<!-- .post footer -->

			<div class="bottom"></div>

		</div>
		<!-- #content -->

	</div>
	<!-- #wrapper -->

	</body>
</html>