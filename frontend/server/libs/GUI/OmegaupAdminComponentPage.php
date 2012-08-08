<?php 

class OmegaupAdminComponentPage extends StdComponentPage{

	
	private $user_html_menu;

	function __construct()
	{

		//must be logged in
		if( !LoginController::isLoggedIn() ){
			header("HTTP/1.1 403 Forbidden");
			exit;
		}

		$user_id = LoginController::getCurrentUser()->getUserId();
		
		if( !Authorization::IsSystemAdmin($user_id) ){
			header("HTTP/1.1 403 Forbidden");
			exit;	
		}


		//check out permissions

		$this->addJs("../js/jquery-ui-1.8.16.custom.min.js");
		$this->addJs("../js/jquery-ui-sliderAccess.js");
		$this->addJs("../js/jquery-ui-timepicker-addon.js");
		
		parent::__construct();

		$this->user_html_menu = ""; 

		$this->createUserMenu();

		
		
	}//__construct()





	




	private function createUserMenu()
	{

		
		if(LoginController::isLoggedIn()){
			//user is NOT logged in
			
			$this_user = LoginController::getCurrentUser();
			$this->user_html_menu = '<img src="http://www.gravatar.com/avatar/'. md5($this_user->getUsername())  .'?s=16&amp;d=identicon&amp;r=PG"  >';
			$this->user_html_menu .= ' Hola <a href="../profile.php?id='.$this_user->getUserId()  .'">' . $this_user->getUsername()  .'</a>&nbsp;';

			/**
			 *
			 * Test if user is admin 
			 **/
			$test_admin = UserRolesDAO::getByPK( $this_user->getUserId(), 1 );
			
			if(!is_null($test_admin)){
				//he is admin !
				$this->user_html_menu .= "| <a href='index.php'>Administrar Omegaup</a>&nbsp;";					
			}


			$this->user_html_menu .= "| <a href='../?request=logout'>Cerrar Sesion</a>&nbsp;";	
			return;
		}

		
		//user is not logged in
		$this->user_html_menu = "Bienvenido a Omegaup ! ";
		$this->user_html_menu .= "<a href='nativeLogin.php'>Inicia sesion !</a>";

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

			
			<link rel="stylesheet" type="text/css" href="../css/style.css">


            <script type="text/javascript" src="../js/jquery.js"></script>
            <script type="text/javascript" src="../js/jquery-autocomplete.js"></script>

			<link rel="stylesheet" type="text/css" href="../css/jquery-ui-1.8.16.custom.css">
			<link rel="stylesheet" type="text/css" href="../css/jquery-ui-timepicker-addon.css">			
						

<!--			<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>-->
			<!--<script type="text/javascript" src="../js/jquery-ui-1.8.16.custom.min.js"></script>
			<script type="text/javascript" src="../js/jquery-ui-sliderAccess.js"></script>
			<script type="text/javascript" src="../js/jquery-ui-timepicker-addon.js"></script>-->

			<script type="text/javascript" src="../js/omegaup.js"></script>


			<title>OmegaUp Admin Pages</title>

			</head>
			
			<body>
			<div id="wrapper">
				<div class="login_bar" style="display: block">
				<?php echo $this->user_html_menu; ?>
				</div> 
				<div id="title">
					<div style="margin-left: 40%;"><img src="../media/omegaup_curves.png"></div>
				</div>
			    
				<div id="content">
					
					
					<div class="post footer">
								<ul >
									<li><a href='../index.php'>Regresar a omegaup</a></li>
									<li><a href='contests.php'>Concursos</a></li>
									<li><a href='users.php'>Usuarios</a></li>
									<li><a href='probs.php'>Problemas</a></li>
									<li><a href='log.php'>Log</a></li>
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
			
			<?php if(defined("OMEGAUP_GA_TRACK") && OMEGAUP_GA_TRACK === true){ ?>

				<script type="text/javascript">
				var _gaq = _gaq || [];
				_gaq.push(['_setAccount', '<?php echo OMEGAUP_GA_ID; ?>']);
				_gaq.push(['_trackPageview']);
				(function() {
				var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
				ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
				var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
				})();
				</script>

			<?php } ?>
			</body>
		</html>
		<?php
	}

}




