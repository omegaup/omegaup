<?php
require_once('../../server/bootstrap_smarty.php');

try {
    $session = SessionController::apiCurrentSession(
        new \OmegaUp\Request($_REQUEST)
    )['session'];
    $smartyProperties = ProblemController::getProblemDetailsForSmarty(
        new \OmegaUp\Request($_REQUEST)
    );
} catch (Exception $e) {
    ApiCaller::handleException($e);
}

foreach ($smartyProperties as $key => $value) {
    $smarty->assign($key, $value);
}

$smarty->display('../../templates/arena.problem.tpl');
