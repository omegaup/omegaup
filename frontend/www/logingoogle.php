<?php
    /**
      * Description:
      *     Session controller handles sessions.
      *
      * Author:
      *     Alan Gonzalez alanboy@alanboy.net
      *
      **/
    require_once( "../server/bootstrap.php" );
    require_once( "../server/libs/GoogleOpenID.php" );

    //retured from google
    if (isset($_GET["gr"])) {
        $googleLogin = GoogleOpenID::getResponse();

    	if($googleLogin->success()) {
            $c_Session = new SessionController();
	    $c_Session->LoginViaGoogle($googleLogin->email());
	    if (isset($_GET['redirect'])) {
		    die(header('Location: ' . $_GET['redirect']));
	    } else {
	            die(header("Location: /profile/"));
	    }
        }

        die(header("Location: /login/?shva=1"));
    }

    $association_handle = GoogleOpenID::getAssociationHandle( );

    //somehow, save the association handle (the below function is not real)
    //save_handle_somehow($association_handle);

    //somehow, retrieve the saved association handle (the below function is not real)
    //$association_handle = get_saved_handle_somehow();

    //use the saved association handle
    $return_to = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
    $googleLogin = GoogleOpenID::createRequest($return_to . "?gr=1" . (isset($_GET['redirect']) ? '&redirect=' . urlencode($_GET['redirect']) : ''), $association_handle, true);
    $googleLogin->redirect();
