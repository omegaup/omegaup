<?php

require_once('../server/bootstrap_smarty.php');

try {
    [$privateProblemsAlert, $isAdmin] = ProblemController::getProblemsMineInfo(
        new Request($_REQUEST)
    );
} catch (ForbiddenAccessException $e) {
    Logger::getLogger('course')->error('APIException ' . $e);
    header('HTTP/1.1 404 Not Found');
    die(file_get_contents('404.html'));
}

$smarty->assign('PRIVATE_PROBLEMS_ALERT', $privateProblemsAlert);
$smarty->assign('IS_SYSADMIN', $isAdmin);

$smarty->display('../templates/problem.mine.tpl');
