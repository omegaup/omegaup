<?php
require_once('../../server/bootstrap_smarty.php');

try {
    $r = new Request([
        'auth_token' => array_key_exists('ouat', $_REQUEST) ? $_REQUEST['ouat'] : null,
        'contest_alias' => $_REQUEST['contest_alias'],
    ]);
    $session = SessionController::apiCurrentSession($r)['session'];
    $contest = ContestController::validateContest($_REQUEST['contest_alias']);
    $result = ContestController::getContestDetailsForSmarty($r, $contest);
} catch (ApiException $e) {
    header('HTTP/1.1 404 Not Found');
    die(file_get_contents('../404.html'));
}
foreach ($result['smartyProperties'] as $key => $value) {
    $smarty->assign($key, $value);
}

if ($result['shouldShowIntro']) {
    $smarty->display('../../templates/arena.contest.intro.tpl');
} else {
    $smarty->display('../../templates/arena.contest.practice.tpl');
}
