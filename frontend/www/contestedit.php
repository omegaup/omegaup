<?php

require_once('../server/bootstrap.php');
$r = new Request([
    'auth_token' => array_key_exists('ouat', $_REQUEST) ? $_REQUEST['ouat'] : null,
    'contest_alias' => $_REQUEST['contest'],
]);
$smarty->assign('LANGUAGES', array_keys(RunController::$kSupportedLanguages));
$smarty->assign('IS_UPDATE', 1);
$smarty->assign('REQUEST_PAYLOAD', ContestController::apiRequests($r));
$smarty->display('../templates/contest.edit.tpl');
