<?php

	require_once( "../server/bootstrap.php" );

	if (isset($_POST["request"]) && ($_POST["request"] == "login")) {
		//user wants to login natively
		$c_Session = new SessionController;
		$r = new Request;
		$r["usernameOrEmail"] = $_POST["user"];
		$r["password"] = $_POST["pass"];
		$c_Session->NativeLogin($r);

		//reload page
		die(header("Location: " . $_SERVER["PHP_SELF"] . "?shva=1"));
	}

	if (isset($_GET["shva"])) {
		$c_Session = new SessionController;
		if(!$c_Session->CurrentSessionAvailable()) {
			$smarty->assign( 'ERROR_TO_USER', 'USER_OR_PASSWORD_WRONG' );
		}
	}

	if(isset($_GET["state"])) {
		$c_Session = new SessionController;
		$c_Session->LoginViaFacebook();
	}

	$smarty->display( '../templates/login.tpl' );
