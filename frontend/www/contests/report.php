<?php
require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');
\OmegaUp\UITools::redirectToLoginIfNotLoggedIn();

try {
    /** @var array{contestReport: array{status: string, problems: array{order: int, alias: string}[], ranking: array{problems: array{alias: string, points: int, percent: int, penalty: int, runs: int, run_details: array{status: string, admin: bool, guid: string, language: string, details: array{verdict: string, contest_score: int, score: int, judged_by: string}, logs: null|string, judged_by: string}}[], username: string, name: string, country: null|string, is_invited: null|bool, total: array{points: int, penalty: int}}[], start_time: int, finish_time: int, title: string, time: int}} */
    $result = \OmegaUp\Controllers\Contest::getContestReportDetailsForSmarty(
        new \OmegaUp\Request($_REQUEST)
    );
} catch (Exception $e) {
    \OmegaUp\ApiCaller::handleException($e);
}

foreach ($result as $key => $value) {
    $smarty->assign($key, $value);
}

$constant = 'constant';
$smarty->display("{$constant('OMEGAUP_ROOT')}/templates/contest.report.tpl");
