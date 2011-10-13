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




    /**
      * Login
      *
      **/
    $page->addComponent( new TitleComponent("Ya tienes cuenta ?"));
    $login_form = new FormComponent( new Users() );

    $login_form->addField("user", "Email o usuario", "input", "", "user" );
    $login_form->addField("pass", "Contrase&ntilde;a", "password", "", "pass" );
    $login_form->addField("", "", "hidden", "login", "request" );

    $login_form->addSubmit("Iniciar sesion",  "nativeLogin.php", "POST");

    $page->addComponent( $login_form );



    $page->addComponent( new TitleComponent("Unete a Omegaup !"));


    
    /**
      * Third Party Login
      *
      **/
    $page->addComponent( new TitleComponent("&iquest; Tienes alguna cuenta en uno de estos sitios ?", 3));
    $html = '<a ><img src="http://3.bp.blogspot.com/-fsazKKHM-kQ/TjxQgND9E_I/AAAAAAAAANU/iEQwsuALe1s/s1600/Google.png" height="50"></a>';
	$page->addComponent( new FreeHtmlComponent($html) );



    /**
      * Native registration
      *
      **/
    $page->addComponent( new TitleComponent("&iquest; No es asi ? Registrate, es facil y rapido !", 3));
    $form = new DAOFormComponent( new Users() );
    $form->hideFields( array( "user_id", "solved", "main_email_id", "submissions", "country_id", "state_id", "school_id", "last_access" ) ) ;
    $form->addField("a", "Escuela", "input" );
    $page->addComponent( $form );




    $page->render();


