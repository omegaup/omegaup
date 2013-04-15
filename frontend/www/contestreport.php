<?php

require_once( "../server/bootstrap.php" );

$r = new Request(array(
		"contest_alias" => $_REQUEST["contest_alias"],
		"auth_token" => $smarty->getTemplateVars('CURRENT_USER_AUTH_TOKEN'),
	));
$r->method = "ContestController::apiReport";
$response = ApiCaller::call($r);

if ($response["status"] == "ok") {
	$smarty->assign('contestReport', $response);
	$smarty->display('../templates/contestreport.tpl');
}
