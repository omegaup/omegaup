<?php
	require_once("../server/bootstrap.php");
	require_once("api/ApiCaller.php");

	$triedToLogin = false;
	$emailVerified = true;
	$c_Session = new SessionController;

	if (isset($_POST["request"]) && ($_POST["request"] == "login")) {
		// user wants to login natively
		
		$r = new Request();
		$r["usernameOrEmail"] = $_POST["user"];
		$r["password"] = $_POST["pass"];
		$r->method = "UserController::apiLogin";
		$response = ApiCaller::call($r);

		if ($response["status"] === "error") {
			if ($response["errorcode"] === 600 || $response["errorcode"] === 601) {
				$emailVerified = false;
			} 
		} 

		$triedToLogin = true;
	}

	if (isset($_GET["state"])) {
		$c_Session->LoginViaFacebook();
		$triedToLogin = true;
	}

	if (isset($_GET["shva"])) {
		$triedToLogin = true;
	}

	if ($c_Session->CurrentSessionAvailable()) {
		if (isset($_GET['redirect'])) {
			die(header('Location: ' . $_GET['redirect']));
		} else {
			die(header('Location: /profile/'));
		}
	} else if ($triedToLogin) {
		if (!$emailVerified) {
			$smarty->assign('ERROR_TO_USER', 'EMAIL_NOT_VERIFIED');
		} else {
			$smarty->assign('ERROR_TO_USER', 'USER_OR_PASSWORD_WRONG');
		}
		$smarty->assign('ERROR_MESSAGE', $response["error"]);
	}

	$smarty->display('../templates/login.tpl');
