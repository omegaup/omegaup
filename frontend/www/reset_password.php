<?php
require_once( "../server/bootstrap.php" );
if (isset($_POST['email'])
	&& isset($_POST['reset_token'])
	&& isset($_POST['password'])
	&& isset($_POST['password_confirmation'])
) {
	$r = new Request(Array(
		'email'					=> $_POST['email'],
		'reset_token'			=> $_POST['reset_token'],
		'password'				=> $_POST['password'],
		'password_confirmation'	=> $_POST['password_confirmation']
	));
	$response = ResetController::apiUpdate($r);

	if ($response['status'] !== STATUS_OK) {
		$smarty->assign('REQUEST_STATUS', 'failure');
		$smarty->assign('EMAIL', $_POST['email']);
		$smarty->assign('RESET_TOKEN', $_POST['reset_token']);
	} else {
		$smarty->assign('REQUEST_STATUS', 'success');
	}
} else if (isset($_GET['email']) && isset($_GET['reset_token'])) {
	$email = $_GET['email'];
	$info = UsersDAO::FindResetInfoByEmail($email);
	if (is_null($info) || is_null($info['reset_sent_at'])) {
		die(header('Location: index.php'));
	} else {
		$seconds = time() - strtotime($info['reset_sent_at']);
		if ($seconds > 2 * 3600) {
			$smarty->assign('REQUEST_STATUS', 'expired');
		} else {
			$smarty->assign('EMAIL', $_GET['email']);
			$smarty->assign('RESET_TOKEN', $_GET['reset_token']);
		}
	}
} else {
	die(header('Location: index.php'));
}

$smarty->display('../templates/reset_password.tpl');

