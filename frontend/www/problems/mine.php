<?php

require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');

try {
    $smartyProperties = \OmegaUp\Controllers\Problem::getProblemsMineInfoForSmarty(
        new \OmegaUp\Request($_REQUEST)
    );
} catch (Exception $e) {
    \OmegaUp\ApiCaller::handleException($e);
}

foreach ($smartyProperties as $key => $value) {
    $smarty->assign($key, $value);
}

$privateProblemsAlert = (!isset($_SESSION['private_problems_alert']) &&
    \OmegaUp\DAO\Problems::getPrivateCount($session['user']) > 0);
if ($privateProblemsAlert) {
    $_SESSION['private_problems_alert'] = true;
}
$smarty->assign('privateProblemsAlert', $privateProblemsAlert);

$smarty->display(OMEGAUP_ROOT . '/templates/problem.mine.tpl');
