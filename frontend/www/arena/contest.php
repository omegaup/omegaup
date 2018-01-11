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

if ($show_intro['shouldShowResults']) {
    $user = SessionController::getCurrentSession($r)['user'];
    $smarty->assign(
        'needsBasicInformation',
        $show_intro['needsBasicInformation'] && (
            !$user['country_id'] || !$user['state_id'] || !$user['school_id']
        )
    );
    $smarty->display('../../templates/arena.contest.intro.tpl');
} else {
    $smarty->display('../../templates/arena.contest.contestant.tpl');
}
