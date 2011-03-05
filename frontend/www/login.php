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

	</head>
	
	<body>
	<div id="wrapper">
		
		<div id="title"><?php echo $GUI::getHeader(); ?></div>
	    
		<div id="content">
			
			
			<div class="post footer"><?php echo $GUI::getMainMenu(); ?></div>

			<div class="post">

	           <div class="title">Inicia sesion en Omgeaup</div>

				<div class="copy">
					


				<div id="fb-root"></div> 
			    <p><fb:login-button autologoutlink="true"></fb:login-button></p> 
			    <p><fb:like></fb:like></p>

				</div>
				<!-- .copy -->


			</div>
			<!-- .post -->
		
		
		
		    <div class="post footer">
		        <ul>
		            <li class="first"><a href=""><?php echo $GUI::getFooter(); ?></a></li>
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