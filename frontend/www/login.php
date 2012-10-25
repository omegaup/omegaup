<?php

    require_once( "../server/bootstrap.php" );


    if ( isset( $_POST["request"] ) && ($_POST["request"] == "login") )
    {
      //user wants to login natively
      $c_Sesion = new SesionController;
      $c_Sesion->NativeLogin( $_POST["user"], $_POST["pass"] );

      //reload page
      die(header("Location: " . $_SERVER["PHP_SELF"] . "?shva=1"));
    }




    // create object
    $smarty = new Smarty;


    $smarty->setTemplateDir( SERVER_PATH . '\\..\\templates\\' );
    $smarty->setCacheDir( "C:\\Users\\Alan\\Desktop\\cache" )->setCompileDir(  "C:\\Users\\Alan\\Desktop\\cache" );
    $smarty->configLoad("C:\\xampp\\htdocs\\omegaup\\omegaup\\frontend\\templates\\es.lang");
    //$smarty->configLoad("C:\\xampp\\htdocs\\omegaup\\omegaup\\frontend\\templates\\en.lang");

    $smarty->display( '../templates/login.tpl' );