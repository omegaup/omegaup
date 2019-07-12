<?php
require_once('../../server/bootstrap_smarty.php');

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
        $show_intro['needs_basic_information'] && !is_null($session['identity']) && (
            !$session['identity']->country_id || !$session['identity']->state_id ||
            !$session['identity']->school_id
        )
    );
    $smarty->assign(
        'requestsUserInformation',
        $show_intro['requests_user_information']
    );
    if (isset($show_intro['privacy_statement_markdown'])) {
        $smarty->assign('privacyStatement', [
            'markdown' => $show_intro['privacy_statement_markdown'],
            'gitObjectId' => $show_intro['git_object_id'],
            'statementType' => $show_intro['statement_type'],
        ]);
    }
    $smarty->display('../../templates/arena.contest.intro.tpl');
} else {
    $smarty->display('../../templates/arena.contest.practice.tpl');
}
