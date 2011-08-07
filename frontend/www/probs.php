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

<!DOCTYPE html> 
<html xmlns="http://www.w3.org/1999/xhtml "
      xmlns:fb="http://www.facebook.com/2008/fbml ">

	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
	<?php echo $GUI::getExternalCSS(); ?>

	</head>
	
	<body>
	<div id="wrapper">
		<div class="login_bar"></div> 
		<div id="title"><?php echo $GUI::getHeader(); ?></div>
	    
		<div id="content">
			
			
			<div class="post footer"><?php echo $GUI::getMainMenu(); ?></div>

			<div class="post">

	           <div class="title">Elevando el nivel de Desarrolladores</div>

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
					$serv = "tju";
					$noPage = 1;
					$sizePage = 25;
					$orderBy = "title";
					if(isset($_REQUEST['serv']))$serv = $_REQUEST['serv'];
					if(isset($_REQUEST['noPage']))$noPage = $_REQUEST['noPage'];
					if(isset($_REQUEST['order']))$orderBy = $_REQUEST['order'];						
					$problemas = ProblemsController::getProblemList($sizePage , $noPage , $serv, $orderBy);
					if(sizeof($problemas) == 2){
						echo "No hay problemas !";
						
					}else{
						$html = "<div width='100%' align='right'>$problemas[0]</div>";
						$html .= "</br><center>$problemas[1]</center></br>";
						$size = sizeof($problemas);
						$html .= "<table id='problems' width='100%'><tr>
								<th><a href='?order=title'>Titulo</a></th>
								<th><a href='?order=time_limit'>Tiempo limite (s)</a></th>
								<th><a href='?order=memory_limit'>Memoria limite (Kb)</a></th>
								<th><a href='?order=visits'>Visitas </a></th>
								<th><a href='?order=submissions'>Envios</a></th>
								<th><a href='?order=accepted'>Aceptados</a></th>
								<th><a href='?order=difficulty'>Dificultad</a></th>
								</tr>";
						for($i=2; $i < $size; $i++){
							$problemas[$i] = json_decode($problemas[$i]);
							$html.= "<tr><td>".$problemas[$i]->title."</td>";
							$html.= "<td>".$problemas[$i]->time_limit."</td>";
							$html.= "<td>".$problemas[$i]->memory_limit."</td>";
							$html.= "<td>".$problemas[$i]->visits."</td>";
							$html.= "<td>".$problemas[$i]->submissions."</td>";
							$html.= "<td>".$problemas[$i]->accepted."</td>";
							$html.= "<td>".$problemas[$i]->difficulty."</td> </tr>";
						}
						$html .= "</table>";						
						$html .= "<div width='100%' align='right'>$problemas[0]</div>";
						$html .= "</br><center>$problemas[1]</center></br>";
						echo $html;
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