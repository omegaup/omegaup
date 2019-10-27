<?php

require_once('../server/bootstrap_smarty.php');

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

[
    'user' => $user,
] = \OmegaUp\Controllers\Session::getCurrentSession();
$privateProblemsAlert = (!isset($_SESSION['private_problems_alert']) &&
    !is_null($user) &&
    \OmegaUp\DAO\Problems::getPrivateCount($user) > 0);
if ($privateProblemsAlert) {
    $_SESSION['private_problems_alert'] = true;
}
$smarty->assign('privateProblemsAlert', $privateProblemsAlert);

$smarty->display('../templates/problem.mine.tpl');
