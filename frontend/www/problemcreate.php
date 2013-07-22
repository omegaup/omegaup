<?php

require_once("../server/bootstrap.php");
require_once("api/ApiCaller.php");

$smarty->assign('TITLE', "");
$smarty->assign('VALIDATOR', "token-caseless");
$smarty->assign('TIME_LIMIT', "1000");
$smarty->assign('MEMORY_LIMIT', "32768");
$smarty->assign('SOURCE', "");

if (isset($_POST["request"]) && ($_POST["request"] == "submit")) {
		
	$r = new Request(array(
				"auth_token" => $smarty->getTemplateVars('CURRENT_USER_AUTH_TOKEN'),
				"title" => $_POST["title"],
				"validator" => $_POST["validator"],
				"time_limit" => $_POST["time_limit"],
				"memory_limit" => $_POST["memory_limit"],
				"source" => $_POST["source"],
				"public" => 0,
			));
	$r->method = "ProblemController::apiCreate";

	$response = ApiCaller::call($r);

	if ($response["status"] == "error") {
		$smarty->assign('STATUS', $response["error"]);
		$smarty->assign('TITLE', $_POST["title"]);
		$smarty->assign('VALIDATOR', $_POST["validator"]);
		$smarty->assign('TIME_LIMIT', $_POST["time_limit"]);
		$smarty->assign('MEMORY_LIMIT', $_POST["memory_limit"]);
		$smarty->assign('SOURCE', $_POST["source"]);
	} else if ($response["status"] == "ok") {
		$smarty->assign('STATUS', "New problem created succesfully! Alias: " . $response["alias"]);
	}
}

$smarty->display('../templates/problem.new.tpl');
