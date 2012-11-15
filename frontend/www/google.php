<?php

    /**
      * Description:
      *     Sesion controller handles sesions.
      *
      * Author:
      *     Alan Gonzalez alanboy@alanboy.net
      *
      **/
    require_once( "../server/bootstrap.php" );
    require_once( "../server/libs/GoogleOpenID.php" );


    //retured from google
    if( isset( $_GET["gr"] ) )
    {
        $googleLogin = GoogleOpenID::getResponse( );

    	if( $googleLogin->success( ) )
        {

            $c_Sesion = new SesionController( );
            $c_Sesion->LoginViaGoogle( $googleLogin->email( ) );

            // ---------------------------

            $context = new Request(array("email" => $googleLogin->email( )));

            $c_Sesion = new SesionController( );
            $c_Sesion->LoginViaGoogle( $context );


            // ---------------------------

            $api = new ApiCaller();
            $api->Exectue();

            die( header( "Location: index.php" ) );

        }

        
        die(header("Location: login.php?shva=1"));
    }

    $association_handle = GoogleOpenID::getAssociationHandle( );

    //somehow, save the association handle (the below function is not real)
    //save_handle_somehow($association_handle);

    //somehow, retrieve the saved association handle (the below function is not real)
    //$association_handle = get_saved_handle_somehow();

    //use the saved association handle
    $googleLogin = GoogleOpenID::createRequest( $_SERVER["PHP_SELF"] . "?gr=1", $association_handle, true );
    $googleLogin->redirect( );
