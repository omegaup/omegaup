<?php

require_once( "../server/bootstrap.php" );
require_once("api/ApiCaller.php");
$r = new Request(array(
		"contest_alias" => $_REQUEST["contest_alias"],
		"auth_token" => $smarty->getTemplateVars('CURRENT_USER_AUTH_TOKEN'),
	));
$r->method = "ContestController::apiReport";
$response = ApiCaller::call($r);

if ($response["status"] == "ok") {
	for ($i = 0; $i < count($response); $i++) {
		if (!isset($response[$i]['problems'])) continue;
		foreach ($response[$i]['problems'] as &$problem) {
			if (!isset($problem['run_details']) || !isset($problem['run_details']['cases'])) continue;
			foreach ($problem['run_details']['cases'] as &$case) {
				$case['meta']['time'] = (float)$case['meta']['time'];
				$case['meta']['time-wall'] = (float)$case['meta']['time-wall'];
				$case['meta']['mem'] = (float)$case['meta']['mem'] / 1024.0 / 1024.0;
			}
		}
	}
	$smarty->assign('contestReport', $response);
	$smarty->display('../templates/contestreport.tpl');
}
