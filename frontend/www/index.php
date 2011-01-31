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

<html>  
	<head>

	<?php echo $GUI::getExternalCSS(); ?>
	
	</head>
	
	<body>
	<div id="wrapper">
		
		<div id="title"><?php echo $GUI::getHeader(); ?></div>
	    
		<div id="content">
			
			
			<div class="post footer"><?php echo $GUI::getMainMenu(); ?></div>

			<div class="post">

	           <div class="title">Bienvenido a OmegaUp</div>

				<div class="copy">
					
					<p>Omegaup es un juez.</p> 

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
		            <li class="first"><a href="http://observerapp.com/about/"><?php echo $GUI::getFooter(); ?></a></li>
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