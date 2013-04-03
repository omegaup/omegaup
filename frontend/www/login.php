<?php
	require_once("../server/bootstrap.php");

	$triedToLogin = false;

	if (isset($_POST["request"]) && ($_POST["request"] == "login")) {
		// user wants to login natively
		$c_Session = new SessionController;
		$r = new Request;
		$r["usernameOrEmail"] = $_POST["user"];
		$r["password"] = $_POST["pass"];
		$c_Session->NativeLogin($r);

		$triedToLogin = true;
	}

	if (isset($_GET["state"])) {
		$c_Session = new SessionController;
		$c_Session->LoginViaFacebook();
		$triedToLogin = true;
	}

	if (isset($_GET["shva"])) {
		$c_Session = new SessionController;
		$triedToLogin = true;
	}

	if ($c_Session->CurrentSessionAvailable()) {
		if (isset($_GET['redirect'])) {
			die(header('Location: ' . $_GET['redirect']));
		} else {
			die(header('Location: /profile.php'));
		}
	} else if ($triedToLogin) {
		$smarty->assign('ERROR_TO_USER', 'USER_OR_PASSWORD_WRONG');
	}

	$smarty->display('../templates/login.tpl');
