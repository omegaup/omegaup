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

	$page = new OmegaupComponentPage();
	
	if(!LoginController::isLoggedIn()){
    
		    /**
		      * Third Party Login
		      *
		      **/
		    $page->addComponent( new TitleComponent("Necesitas registrarte en OmegaUp primero", 1));		
		    $page->addComponent( new TitleComponent("&iquest; Tienes alguna cuenta en uno de estos sitios ?", 3));
    
			$html = '<a href="googleLoginReturn.php">
		              <img src="http://3.bp.blogspot.com/-fsazKKHM-kQ/TjxQgND9E_I/AAAAAAAAANU/iEQwsuALe1s/s1600/Google.png" height="50">
		            </a>';
			$page->addComponent( new FreeHtmlComponent($html) );



		    /**
		      * Native registration
		      *
		      **/
		    $page->addComponent( new TitleComponent("&iquest; No es asi ? Registrate, es facil y rapido !", 3));
		    $reg_form = new FormComponent( new Users() );

		    $reg_form->addField("name", "Nombre", "input", "", "name" );
		    $reg_form->addField("email", "Email", "input", "", "email" );
		    $reg_form->addField("pass", "Contrase&ntilde;a", "password", "", "pass" );
		    $reg_form->addField("pass2", "De nuevo", "password", "", "pass2" );
		    $reg_form->addField("", "", "hidden", "register", "request" );
		    $reg_form->addSubmit("Registrar",  "nativeLogin.php", "POST");
		    $page->addComponent( $reg_form );
	}
	
	
	$page->addComponent( new SubmitSolutionComponent(  ) );
	
	$page->render(  );
