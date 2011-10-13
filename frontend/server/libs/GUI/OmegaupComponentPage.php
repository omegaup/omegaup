<?php 

class OmegaupComponentPage extends StdComponentPage{

	


	function __construct()
	{


		parent::__construct();
		$this->bootstrap();
		
	}//__construct()





	function bootstrap()
	{

	}





	/**
      * End page creation and ask for login
      * optionally sending a message to user
	  **/
	private function dieWithLogin($message = null)
	{
		$login_cmp = new LoginComponent();

		if( $message != null )
		{
			self::addComponent(new MessageComponent($message));				
		}

		self::addComponent($login_cmp);
		parent::render();
		exit();
	}





	/**
	  *
	  *
	  **/
	function render()
	{
		
		?>
		<!DOCTYPE html> 
		<html xmlns="http://www.w3.org/1999/xhtml "
		      xmlns:fb="http://www.facebook.com/2008/fbml ">

			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 

			
			<link rel="stylesheet" type="text/css" href="css/style.css">

			</head>
			
			<body>
			<div id="wrapper">
				<div class="login_bar"></div> 
				<div id="title">
					<div style="margin-left: 40%;"><img src="media/omegaup_curves.png"></div>
				</div>
			    
				<div id="content">
					
					
					<div class="post footer">
								<ul >
									<li><a href='probs.php'>Problemas</a></li>
									<li><a href='rank.php'>Ranking</a></li>
									<li><a href='recent.php'>Actividad reciente</a></li>
									<li><a href='faq.php'>FAQ</a></li>
									<li><a href='contests.php'>Concursos</a></li>
									<li><a href='schools.php'>Escuelas</a></li>
									<li><a href='help.php'>Colabora</a></li>
									<li><input type='text' placeholder='Buscar'></li>
								</ul>
					</div>

					<div class="post">
						<div class="copy">
							 <?php
							 /* ----------------------------------------------------------------------
										CONTENIDO
							 ---------------------------------------------------------------------- */
								foreach( $this->components as $cmp ){
									echo $cmp->renderCmp();
								}
							 ?>
						</div>
						<!-- .copy -->
					</div>
					<!-- .post -->
				
				    <div class="post footer">
				        <ul>
				            <li class="first"><a href=""></a></li>
				        </ul>
				        <p><?php //echo $GUI::getFooter(); ?></p>
				    </div>
					<!-- .post footer -->

					<div class="bottom"></div>

				</div>
				<!-- #content -->

			</div>
			<!-- #wrapper -->

			</body>
		</html>
		<?php
	}

}




