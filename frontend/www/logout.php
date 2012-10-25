<?php


    require_once( "../server/bootstrap.php" );

    $c_Sesion = new SesionController;

    if( $c_Sesion->CurrentSesionAvailable( ) )
    {
        $c_Sesion->UnRegisterSesion( );
    }

    die(header("Location: login.php"));