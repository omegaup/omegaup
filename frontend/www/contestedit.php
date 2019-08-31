<?php

require_once('../server/bootstrap_smarty.php');
$r = new \OmegaUp\Request([
    'auth_token' => array_key_exists('ouat', $_REQUEST) ? $_REQUEST['ouat'] : null,
    'contest_alias' => $_REQUEST['contest'],
]);
$smarty->assign('LANGUAGES', array_keys(RunController::$kSupportedLanguages));
$smarty->assign('IS_UPDATE', 1);
try {
    $smarty->display('../templates/contest.edit.tpl');
} catch (\OmegaUp\Exceptions\ApiException $e) {
    header('HTTP/1.1 404 Not Found');
    die();
}
