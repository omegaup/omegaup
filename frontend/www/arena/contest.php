<?php
require_once('../../server/bootstrap_smarty.php');

try {
    $r = new \OmegaUp\Request($_REQUEST);
    $r->ensureBool('is_practice', false);

    $contest = ContestController::validateContest($_REQUEST['contest_alias'] ?? '');
    $shouldShowIntro = (!isset($_GET['is_practice']) || $_GET['is_practice'] !== 'true')
        && ContestController::shouldShowIntro($r, $contest);
    $result = ContestController::getContestDetailsForSmarty(
        $r,
        $contest,
        $shouldShowIntro
    );
} catch (Exception $e) {
    ApiCaller::handleException($e);
}

foreach ($result as $key => $value) {
    $smarty->assign($key, $value);
}

if ($shouldShowIntro) {
    $smarty->display('../../templates/arena.contest.intro.tpl');
} elseif (!isset($_GET['is_practice']) || $_GET['is_practice'] !== 'true') {
    $smarty->display('../../templates/arena.contest.contestant.tpl');
} else {
    $smarty->display('../../templates/arena.contest.practice.tpl');
}
