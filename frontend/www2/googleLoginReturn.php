<?php

	define( "LEVEL_NEEDED", false );


	require_once( "../server/inc/bootstrap.php" );



	/* ************************************************************************************ */
	/* ***********************  I HAVE RETURNED 		  ********************************* */
	/* ************************************************************************************ */	 
	if(isset($_GET["return_add"])){

	  	$googleLogin = GoogleOpenID::getResponse();

	  	if( $googleLogin->success() )
		{
			LoginController::login( $googleLogin->email(), $googleLogin->identity() );
			
			die(header("Location: index.php"));
	  	}


		die(header("Location: index.php?s=0"));

	}
	/* ************************************************************************************ */
	
	

	if(isset($_GET["out"])){

		LoginController::logout(  );
		
		die(header("Location: index.php"));
	}
	
	

	$association_handle = GoogleOpenID::getAssociationHandle();

	//somehow, save the association handle (the below function is not real)
	//save_handle_somehow($association_handle);


	//somehow, retrieve the saved association handle (the below function is not real)
	//$association_handle = get_saved_handle_somehow();

	//use the saved association handle
	
	$googleLogin = GoogleOpenID::createRequest( $_SERVER["PHP_SELF"] . "?return_add=1", $association_handle, true);

	$googleLogin->redirect();
