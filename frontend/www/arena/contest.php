<?php
require_once('../../server/bootstrap_smarty.php');

try {
    $r = new Request($_REQUEST);
    $r->ensureBool('is_practice', false);

    $contest = ContestController::validateContest($_REQUEST['contest_alias'] ?? '');
    $showIntro = ContestController::shouldShowIntro($r, $contest);
    if ($showIntro) {
        $result = ContestController::getContestDetailsForSmarty($r, $contest);
    }
} catch (ApiException $e) {
    header('HTTP/1.1 404 Not Found');
    die(file_get_contents('../404.html'));
}

if ($showIntro) {
    foreach ($result as $key => $value) {
        $smarty->assign($key, $value);
    }
    $smarty->display('../../templates/arena.contest.intro.tpl');
} elseif ($r['is_practice'] !== true) {
    $smarty->display('../../templates/arena.contest.contestant.tpl');
} else {
    $smarty->display('../../templates/arena.contest.practice.tpl');
}
