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
		<div class="login_bar">Welcome alan bongnzalez | <a href="login.php">Login</a></div> 
		<div id="title"><?php echo $GUI::getHeader(); ?></div>
	    
		<div id="content">
			
			
			<div class="post footer"><?php echo $GUI::getMainMenu(); ?></div>

			<div class="post">

	           <div class="title">Elevando el nivel de Desarrolladores</div>

				<div class="copy">
					
					<p>OmegaUp est&aacute; pensado como m&aacute;s que otra p&aacute;gina de concursos, otro juez en l&iacute;nea. OmegaUp tendr&aacute; muchas caracter&iacute;sticas que no se encontrar&aacute;n en ning&uacute;n otro sitio, y todo ir&aacute; enfocado para hacer realidad nuestro lema: <i>Elevando el nivel de Desarrolladores</i></p> 

					<h3>New features</h3> 
					<ul> 
						<li>pretty</li> 
						<li>robust</li> 
						<li>elegant</li> 						
					</ul>
					
<pre><code>//this is some code
printf(2.14);</code></pre>

				<p>keep talking</p>

				


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