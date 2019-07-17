<?php
require_once('../../server/bootstrap_smarty.php');

try {
    $session = SessionController::apiCurrentSession(
        new Request($_REQUEST)
    )['session'];
    $smartyProperties = ProblemController::getProblemDetailsForSmarty(
        new Request($_REQUEST)
    );
} catch (ApiException $e) {
    header('HTTP/1.1 404 Not Found');
    die(file_get_contents('../404.html'));
}

foreach ($smartyProperties as $key => $value) {
    $smarty->assign($key, $value);
}

$privateProblemsAlert = 0;
if (!isset($_SESSION['private_problems_alert']) &&
    ProblemsDAO::getPrivateCount($session['user']) > 0) {
    $_SESSION['private_problems_alert'] = 1;
    $privateProblemsAlert = 1;
}
$smarty->assign('privateProblemsAlert', $privateProblemsAlert);

$smarty->display('../../templates/arena.problem.tpl');
