<?php

require_once("../server/bootstrap.php");
require_once("api/ApiCaller.php");


if (isset($_POST["request"]) && ($_POST["request"] == "submit")) {	
	$r = new Request(array(
				"auth_token" => $smarty->getTemplateVars('CURRENT_USER_AUTH_TOKEN'),
				"problem_alias" => $_POST["edit-problem-list"],
				"title" => $_POST["title"],
				"validator" => $_POST["validator"],
				"time_limit" => $_POST["time_limit"],
				"memory_limit" => $_POST["memory_limit"],
				"output_limit" => $_POST["output_limit"],
				"source" => $_POST["source"],
				"public" => $_POST["public"],
			));
	$r->method = "ProblemController::apiUpdate";

	$response = ApiCaller::call($r);

	if ($response["status"] == "error") {
		$smarty->assign('STATUS_ERROR', $response["error"]);
		$smarty->assign('TITLE', $_POST["title"]);
		$smarty->assign('ALIAS', $_POST["edit-problem-list"]);
		$smarty->assign('VALIDATOR', $_POST["validator"]);
		$smarty->assign('TIME_LIMIT', $_POST["time_limit"]);
		$smarty->assign('MEMORY_LIMIT', $_POST["memory_limit"]);
		$smarty->assign('SOURCE', $_POST["source"]);
		$smarty->assign('PUBLIC', $_POST["public"]);
	} else if ($response["status"] == "ok") {
		$smarty->assign('STATUS_SUCCESS', "Problem updated succesfully!");
	}
}

$smarty->assign('IS_UPDATE', 1);
$smarty->assign('LOAD_MATHJAX', 1);
$smarty->assign('LOAD_PAGEDOWN', 1);
$smarty->display('../templates/problem.edit.tpl');
