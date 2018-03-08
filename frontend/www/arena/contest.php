<?php
require_once('../../server/bootstrap.php');

$show_intro = true;

try {
    $r = new Request([
        'auth_token' => array_key_exists('ouat', $_REQUEST) ? $_REQUEST['ouat'] : null,
        'contest_alias' => $_REQUEST['contest_alias'],
    ]);
    $show_intro = ContestController::showContestIntro($r);
} catch (Exception $e) {
    header('HTTP/1.1 404 Not Found');
    die(file_get_contents('../404.html'));
}

if ($show_intro['shouldShowIntro']) {
    $session = SessionController::apiCurrentSession($r)['session'];
    $smarty->assign(
        'needsBasicInformation',
        $show_intro['needs_basic_information'] && !is_null($session['user']) && (
            !$session['user']->country_id || !$session['user']->state_id || !$session['user']->school_id
        )
    );
    $smarty->assign(
        'requestsUserInformation',
        $show_intro['requests_user_information']
    );
    $smarty->display('../../templates/arena.contest.intro.tpl');
} else {
    $smarty->display('../../templates/arena.contest.contestant.tpl');
}
