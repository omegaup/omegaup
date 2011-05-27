<?php

	define( "LEVEL_NEEDED", false );

	require_once("../server/inc/bootstrap.php");

	switch($_POST["action"]){
		
		case "new_user_basic" : 
		
			require_once("controllers/users.controller.php");
			
			UsersController::registerNewUser($_POST["name"], $_POST["email"], $_POST["password"], JSON);

		break;
		
	}