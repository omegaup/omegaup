<?php
require_once( "../server/bootstrap.php" );
if (!is_null($_POST['email'])) {
	$email = $_POST['email'];
	$r = new Request(Array('email' => $email));
	$response = ResetController::apiCreate($r);
	if ($response['status'] !== STATUS_OK) {
		$smarty->assign('REQUEST_STATUS', 'failure');
	} else {
		$smarty->assign('REQUEST_STATUS', 'success');
	}
}

$smarty->display('../templates/forgot_password.tpl');
