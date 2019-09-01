<?php
require_once('../server/bootstrap_smarty.php');

\OmegaUp\UITools::redirectToLoginIfNotLoggedIn();

try {
    $result = ContestController::getContestReportDetailsForSmarty(
        new \OmegaUp\Request($_REQUEST)
    );
} catch (Exception $e) {
    \OmegaUp\ApiCaller::handleException($e);
}

foreach ($result as $key => $value) {
    $smarty->assign($key, $value);
}

$smarty->display('../templates/contest.report.tpl');
