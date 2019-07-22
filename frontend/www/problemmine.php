<?php

require_once('../server/bootstrap_smarty.php');

try {
    $smartyProperties = ProblemController::getProblemsMineInfoForSmarty(
        new Request($_REQUEST)
    );
} catch (ForbiddenAccessException $e) {
    Logger::getLogger('problem')->error('APIException ' . $e);
    header('HTTP/1.1 404 Not Found');
    die(file_get_contents('404.html'));
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
