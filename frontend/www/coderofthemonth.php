<?php

require_once('../server/bootstrap.php');

$request = new Request([
    'auth_token' => $smarty->getTemplateVars('CURRENT_USER_AUTH_TOKEN')
]);

$response = UserController::apiCoderOfTheMonthList($request);
$smarty->assign('coders', $response['coders']);

$smarty->display('../templates/codersofthemonth.tpl');
