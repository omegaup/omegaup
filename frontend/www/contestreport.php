<?php
require_once('../server/bootstrap_smarty.php');

UITools::redirectToLoginIfNotLoggedIn();

try {
    $result = ContestController::getContestReportDetailsForSmarty(
        new Request($_REQUEST)
    );
} catch (Exception $e) {
    ApiCaller::handleException($e);
}

foreach ($result as $key => $value) {
    $smarty->assign($key, $value);
}

$smarty->display('../templates/contest.report.tpl');
