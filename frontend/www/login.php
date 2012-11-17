<?php

	require_once( "../server/bootstrap.php" );

	if (isset($_POST["request"]) && ($_POST["request"] == "login")) {
		//user wants to login natively
		$c_Sesion = new SesionController;
		$r = new Request;
		$r["usernameOrEmail"] = $_POST["user"];
		$r["password"] = $_POST["pass"];
		$c_Sesion->NativeLogin($r);

		//reload page
		die(header("Location: " . $_SERVER["PHP_SELF"] . "?shva=1"));
	}

	if (isset($_GET["shva"])) {
		$c_Sesion = new SesionController;
		if(!$c_Sesion->CurrentSesionAvailable()) {
			$smarty->assign( 'ERROR_TO_USER', 'USER_OR_PASSWORD_WRONG' );
		}
	}

	$smarty->display( '../templates/login.tpl' );
