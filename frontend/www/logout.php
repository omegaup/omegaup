<?php


    require_once( "../server/bootstrap.php" );

    $c_Session = new SessionController;

    if( $c_Session->CurrentSessionAvailable( ) )
    {
        $c_Session->UnRegisterSession( );
    }

    die(header("Location: login.php"));
