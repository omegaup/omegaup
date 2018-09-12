<?php

require_once('../server/bootstrap.php');
$r = new Request([
    'auth_token' => array_key_exists('ouat', $_REQUEST) ? $_REQUEST['ouat'] : null,
    'contest_alias' => $_REQUEST['contest'],
]);
$smarty->assign('LANGUAGES', array_keys(RunController::$kSupportedLanguages));
$smarty->assign('IS_UPDATE', 1);
try {
    $smarty->display('../templates/contest.edit.tpl');
} catch (APIException $e) {
    header('HTTP/1.1 404 Not Found');
    die();
}
