<?php

require_once('../server/bootstrap_smarty.php');

try {
    $smartyProperties = ProblemController::getProblemsMineInfoForSmarty(
        new \OmegaUp\Request($_REQUEST)
    );
} catch (Exception $e) {
    ApiCaller::handleException($e);
}

foreach ($smartyProperties as $key => $value) {
    $smarty->assign($key, $value);
}

$privateProblemsAlert = (!isset($_SESSION['private_problems_alert']) &&
    ProblemsDAO::getPrivateCount($session['user']) > 0);
if ($privateProblemsAlert) {
    $_SESSION['private_problems_alert'] = true;
}
$smarty->assign('privateProblemsAlert', $privateProblemsAlert);

$smarty->display('../templates/problem.mine.tpl');
