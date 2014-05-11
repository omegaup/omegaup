<?php

require_once("../server/bootstrap.php");
require_once("api/ApiCaller.php");

$smarty->assign('IS_UPDATE', 1);
$smarty->assign('LOAD_MATHJAX', 1);
$smarty->assign('LOAD_PAGEDOWN', 1);

if (isset($_POST["request"])) {
 	if ($_POST["request"] == "submit") {
		// Update problem contents/metadata
		$r = new Request(array(
					"auth_token" => $smarty->getTemplateVars('CURRENT_USER_AUTH_TOKEN'),
					"problem_alias" => $_POST["problem_alias"],
					"title" => $_POST["title"],
					"validator" => $_POST["validator"],
					"time_limit" => $_POST["time_limit"],
					"memory_limit" => $_POST["memory_limit"],
					"output_limit" => $_POST["output_limit"],
					"source" => $_POST["source"],
					"public" => $_POST["public"],
					"languages" => $_POST["languages"],
				));
		$r->method = "ProblemController::apiUpdate";
		$response = ApiCaller::call($r);
		if ($response["status"] == "error") {
			onError($smarty, $response);
		}
	} else if ($_POST['request'] == 'markdown') {
		// Update statement
		$r = new Request(array(
					"auth_token" => $smarty->getTemplateVars('CURRENT_USER_AUTH_TOKEN'),
					"problem_alias" => $_POST["problem_alias"],
					"statement" => $_POST["wmd-input-statement"]
				));
		$r->method = "ProblemController::apiUpdateStatement";
		$response = ApiCaller::call($r);
		if ($response["status"] == "error") {
			onError($smarty, $response);
		}
	}
	$smarty->assign('STATUS_SUCCESS', "Problem updated succesfully!");
}

$smarty->display('../templates/problem.edit.tpl');

/**
 * Handle error (print msg, die)
 * 
 * @param type $smarty
 * @param type $response
 */
function onError($smarty, $response) {
	$smarty->assign('STATUS_ERROR', $response["error"]);	
	$smarty->display('../templates/problem.edit.tpl');
	die();
}
