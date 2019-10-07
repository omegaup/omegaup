<?php
require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');

try {
    /** @var array{payload: array{status: string, contests: array{title: string, description: string, start_time: int, finish_time: int, last_updated: int, window_length: int, rerun_id: int, admission_mode: string, alias: string, scoreboard: int, points_decay_factor: float, partial_score: int, submissions_gap: int, feedback: string, penalty: int, penalty_type: string, penalty_calc_policy: string, show_scoreboard_after: int, urgent: int, languages: string, recommended: int, scoreboard_url: string, scoreboard_url_admin: string}[]}, privateContestsAlert: bool} */
    $result = \OmegaUp\Controllers\Contest::getContestListMineForSmarty(
        new \OmegaUp\Request($_REQUEST)
    );
} catch (Exception $e) {
    \OmegaUp\ApiCaller::handleException($e);
}

foreach ($result as $key => $value) {
    $smarty->assign($key, $value);
}

$constant = 'constant';
$smarty->display("{$constant('OMEGAUP_ROOT')}/templates/contest.mine.tpl");
