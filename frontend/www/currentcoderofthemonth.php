<?php

require_once('../server/bootstrap.php');

$request = new Request([
    'auth_token' => $smarty->getTemplateVars('CURRENT_USER_AUTH_TOKEN')
]);

$response = UserController::apiCurrentCoderOfTheMonthList();
$smarty->assign('coders', $response['coders']);

$smarty->display('../templates/currentcodersofthemonth.tpl');
