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


	require_once( "../server/controllers/problems.controller.php" );
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

	           <div class="title">Problemas</div>

				<div class="copy">
					
					
					<!-- - - - - - - - - - - - - - - - -
						Lista de juecez
					  - - - - - - - - - - - - - - - - - -->
					<p id="listaDeJuez">
					<?php
					
					$judges = ProblemsController::getJudgesList();
					if(sizeof($judges) == 0){

					}else{
						foreach($judges as $j){
							echo $j . " ";
						}
					}					
					
					?>	
					</p>
					
					
					
					<!-- - - - - - - - - - - - - - - - -
						Lista de problemas
					  - - - - - - - - - - - - - - - - - -->					
					<div id="listaProblemas">
					<p>
					<?php
							
					$problemas = ProblemsController::getProblemList();
					if(sizeof($problemas) == 0){
						echo "No hay problemas !";
						
					}else{
						echo "Si hay problema!";
					}
							
					?>
					</p>
						
					</div>

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

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.0/jquery.min.js"></script>
	
	<script>

		
		var JuezExterno = [];
		
		/** 
		  * Fill judges list
		  *
		  **/
		<?php
			$judges = ProblemsController::getJudgesList();
		
			foreach( $judges as $servidor => $descripcion ){
				echo " JuezExterno.push({ descripcion : '$descripcion', servidor : '$servidor' }); \n";
			}
		?>
		
		
		
	

		$(document).ready(function(){
			
			/**
			  * Write judges list into html
			  **/
			var html = '';
			
			for (var i = JuezExterno.length - 1; i >= 0; i--){
				html += JuezExterno[i].descripcion;
				if(i > 0){
					html += " | ";
				}
			}
			
			jQuery("#listaDeJuez").html(html);


		});
			

		
	</script>

	</body>
</html>