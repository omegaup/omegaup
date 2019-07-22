<?php
require_once('../../server/bootstrap_smarty.php');

try {
    $r = new Request([
        'auth_token' => array_key_exists('ouat', $_REQUEST) ? $_REQUEST['ouat'] : null,
        'contest_alias' => $_REQUEST['contest_alias'],
    ]);
    $session = SessionController::apiCurrentSession($r)['session'];
    $contest = ContestController::validateContest($r['contest_alias'] ?? '');
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
} else {
    $smarty->assign('payload', [
        'shouldShowFirstAssociatedIdentityRunWarning' => !is_null($session['user']) &&
            !UserController::isMainIdentity($session['user'], $session['identity'])
            && ProblemsetsDAO::shouldShowFirstAssociatedIdentityRunWarning(
                $session['user']
            ),
    ]);
    $smarty->display('../../templates/arena.contest.contestant.tpl');
}
