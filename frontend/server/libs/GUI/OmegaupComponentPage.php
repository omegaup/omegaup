<?php 

class OmegaupComponentPage extends StdComponentPage{

	
	private $user_html_menu;
	private $title;
	private $invalid_login;

	function __construct( $title = null )
	{
		$this->invalid_login = false;

		$this->title = $title;
		
		$this->doGetRequests();

		parent::__construct();

		$this->user_html_menu = ""; 

		$this->createUserMenu();
	}//__construct()





	private function doGetRequests(){

		/**
		  *
		  * GET Requests
		  **/
		if(isset($_GET["request"])){
			switch($_GET["request"]){
				case "logout" :
					LoginController::logOut();
					if (isset($_REQUEST['redirect'])) {
						die(header("Location: {$_REQUEST['redirect']}"));
					} else {
						die(header("Location: ." ));
					}
				break;

			}			
		}


		/**
		  *
		  * POST Requests
		  **/
		if(isset($_POST["request"])){

			switch($_POST["request"]){
				case "login" :

					if( LoginController::testUserCredentials(  $_POST["user"], $_POST["pass"]  ) ){
						//login correcto
						
						LoginController::login( $_POST["user"], null );
						
						//
						Logger::log("redireccionando a auth=ok");

						if(defined("FL")) {
							die(header("Location: profile.php?auth=ok&fl=1" ));	
						} else if (isset($_REQUEST['redirect'])) {
							die(header("Location: {$_REQUEST['redirect']}" ));	
						} else {
							die(header("Location: profile.php?auth=ok&" . $_SERVER["QUERY_STRING"] ));
						}

					}else{
						//login incorrecto
						Logger::log("invalid user credentials for user `" . $_POST["user"] . "`");
						$this->invalid_login = true;

					}
				break;
				
			}			

		}		



	}





	private function createUserMenu()
	{

		
		if(LoginController::isLoggedIn()){
			//user *IS* logged in
			
			$this_user = LoginController::getCurrentUser();
			
			if(is_null($this_user)) {
				die(header("Location: ." ));
			}
			
			$this->user_html_menu = '';

			if(!is_null($this_user))
			$this->user_html_menu .= 
				'<a style="background-color: white; color: #678DD7; padding: 2px; -webkit-border-radius: 5px; padding-left: 5px;" href="profile.php?id='.$this_user->getUserId()  .'">' 
				. '<img src="https://secure.gravatar.com/avatar/'. md5($this_user->getUsername())  .'?s=16&amp;d=identicon&amp;r=PG"  >'
				. '&nbsp;' . $this_user->getUsername()  .'</a>&nbsp;';

			/**
			 *
			 * Test if user is admin 
			 **/
			if(Authorization::IsSystemAdmin($this_user->getUserId())){
				//he is admin !
				$this->user_html_menu .= "| <a href='admin'>Administrar OmegaUp</a>&nbsp;";					
			}


			$this->user_html_menu .= "| <a href='?request=logout'>Cerrar Sesion</a>&nbsp;";	
			return;
		}else{
			//user is *NOT* logged in
			$this->user_html_menu = "Bienvenido a OmegaUp ! ";
			$this->user_html_menu .= "<b><a href='nativeLogin.php?redirect=" . urlencode($_SERVER['REQUEST_URI']) . "'>Inicia sesion</a> !</b>";
		}

		


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
		if(is_null($this->title)){
			$this->title = "OmegaUp | Elevando el nivel de programacion";
		}
		
		?>
		<!DOCTYPE html> 
		<html xmlns="http://www.w3.org/1999/xhtml "
		      xmlns:fb="http://www.facebook.com/2008/fbml ">

			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
			
			<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
			
			<script type="text/javascript" src="ux/api.js"></script>

			<title><?php echo $this->title; ?></title>
			<link rel="stylesheet" type="text/css" href="css/style.css">

			</head>
			
			<body>
			<div id="wrapper">
				<div class="login_bar" style="display: block">
				<?php echo $this->user_html_menu; ?>
				</div> 
				<div id="title">
					<a href="index.php">
						<div style="margin-left: 40%;"><img src="media/omegaup_curves.png"></div>
					</a>
				</div>
			    
				<div id="content">
					
					
					<div class="post footer">
								<ul >
									<li><a href='contests.php'><b>Concursos</b></a></li>
									<!-- <li><a href='probs.php'>Problemas</a></li> -->
									<li><a href='rank.php'>Ranking</a></li>
									<li><a href='recent.php'>Actividad reciente</a></li>
									<li><a href='faq.php'>FAQ</a></li>

									<!-- <li><a href='schools.php'>Escuelas</a></li> -->
									<li><a href='help.php'>Colabora</a></li>
									<!-- <li><input type='text' placeholder='Buscar'></li> -->
								</ul>
					</div>

					<div class="post">
						<div class="copy">
<?php
							if ($this->invalid_login) {
								echo "<h3 style='color: red;'>Login inv&aacute;lido</h3>";
							}
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
				
				    <div class="post footer" style="padding: 5px; color:black; margin: 0px auto">
					&nbsp;
						<img style='width: 60px; padding:0px; margin:0px; -webkit-box-shadow:0px 0px;' src='media/omegaup_curves.png'> es un lugar para mejorar tus habilidades de desarrollo de software.
				    </div>
					<!-- .post footer -->

					<div class="bottom"></div>

				</div>
				<!-- #content -->

			</div>
			<!-- #wrapper -->
			

			<script type="text/javascript" src="js/omegaup.js"></script>

			<div id="fb-root"></div>
			    <script>               
			      window.fbAsyncInit = function() {
			        FB.init({
			          appId: '<?php echo OMEGAUP_FB_APPID; ?>', 
			          cookie: true, 
			          xfbml: true,
			          oauth: true
			        });
			        FB.Event.subscribe('auth.login', function(response) {
			          window.location.reload();
			        });
			        FB.Event.subscribe('auth.logout', function(response) {
			          window.location.reload();
			        });
			      };
			      (function() {
			        var e = document.createElement('script'); e.async = true;
			        e.src = document.location.protocol +
			          '//connect.facebook.net/en_US/all.js';
			        document.getElementById('fb-root').appendChild(e);
			      }());
			    </script>
			
			
			
			
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
		exit;
	}

}




