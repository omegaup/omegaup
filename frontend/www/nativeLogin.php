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
	

	/**
	  *
	  * If user is logged in, and somehow
	  * reached this page, send him to home
	  **/
	if( LoginController::isLoggedIn() )
		die(header("Location: index.php"));


  /**
    *
    * Logic for registering a new user
    *
    **/
    if(isset($_POST["request"]) && ($_POST["request"] == "register"))
    {
      

      if( 
             isset( $_POST["email"] )
          && isset( $_POST["pass"] )
          && isset( $_POST["name"] ) 
      ){
        
        try{
            UsersController::registerNewUser( $_POST["name"], $_POST["email"], $_POST["pass"] );  

        }catch(Exception $e){
            die($e);
        }

        $_POST["request"] = "login";
        $_POST["user"]    = $_POST["email"];
        $_POST["pass"]    = $_POST["pass"];

      }
      

      //registration went ok
      //login this user

    }






    $page = new OmegaupComponentPage();




    /**
      * Login
      *
      **/
    $page->addComponent( new TitleComponent("&iquest; Ya tienes cuenta ?"));
    $login_form = new FormComponent( new Users() );
    $login_form->addField("user", "Email o usuario"		, "input"	, ""		, "user" );
    $login_form->addField("pass", "Contrase&ntilde;a"	, "password", ""		, "pass" );
    $login_form->addField(""	, ""					, "hidden"	, "login"	, "request" );

    $login_form->addSubmit("Iniciar sesion",  "nativeLogin.php", "POST");

    $page->addComponent( $login_form );



    $page->addComponent( new TitleComponent("Unete a Omegaup !"));


    
    /**
      * Third Party Login
      *
      **/
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




    $page->render();


