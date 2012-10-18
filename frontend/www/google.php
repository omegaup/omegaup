<?php


    require_once( "../server/bootstrap.php" );
    require_once( "../server/libs/GoogleOpenID.php" );

    $user_c = new UserController( );
    $user_c->LoginViaGoogle( "alan.gohe@gmail.com" );
    exit;


    //retured from google
    if( isset( $_GET["gr"] ) )
    {
        $googleLogin = GoogleOpenID::getResponse( );

		if( $googleLogin->success( ) )
        {
        	$user_c = new UserController( );
            
            $user_c->LoginViaGoogle( $googleLogin->email( ) );

            //Logger::log( "GoogleOpenID reports user email as " . $googleLogin->email( ) );
            
            //LoginController::login( rawurldecode( $googleLogin->email( ) ), $googleLogin->identity( ) );
            
            //die(header("Location: index.php"));
        }

        //die(header("Location: nativeLogin.php?whoops"));
        die();
    }

    $association_handle = GoogleOpenID::getAssociationHandle( );
	
    //somehow, save the association handle (the below function is not real)
    //save_handle_somehow($association_handle);

    //somehow, retrieve the saved association handle (the below function is not real)
    //$association_handle = get_saved_handle_somehow();

    //use the saved association handle
    $googleLogin = GoogleOpenID::createRequest( $_SERVER["PHP_SELF"] . "?gr=1", $association_handle, true );
    $googleLogin->redirect( );
