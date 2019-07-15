<?php
require_once('../../server/bootstrap_smarty.php');

try {
    $r = new Request([
        'auth_token' => array_key_exists('ouat', $_REQUEST) ? $_REQUEST['ouat'] : null,
        'contest_alias' => $_REQUEST['contest_alias'],
    ]);
    $showIntro = ContestController::showContestIntro($r);
} catch (Exception $e) {
    header('HTTP/1.1 404 Not Found');
    die(file_get_contents('../404.html'));
}

$session = SessionController::apiCurrentSession($r)['session'];
if ($showIntro['shouldShowIntro']) {
    $smarty->assign(
        'needsBasicInformation',
        $showIntro['needs_basic_information'] &&
            !is_null($session['identity']) && (!$session['identity']->country_id
            || !$session['identity']->state_id || !$session['identity']->school_id
        )
    );
    $smarty->assign(
        'requestsUserInformation',
        $showIntro['requests_user_information']
    );
    if (isset($showIntro['privacy_statement_markdown'])) {
        $smarty->assign('privacyStatement', [
            'markdown' => $showIntro['privacy_statement_markdown'],
            'gitObjectId' => $showIntro['git_object_id'],
            'statementType' => $showIntro['statement_type'],
        ]);
    }
    $smarty->display('../../templates/arena.contest.intro.tpl');
} else {
    $smarty->assign('payload', [
        'shouldShowFirstAssociatedIdentityRunWarning' =>
            !$session['is_main_identity'] && !is_null($r->user) &&
            ProblemsetsDAO::shouldShowFirstAssociatedIdentityRunWarning(
                $session['user']
            )
    ]);
    $smarty->display('../../templates/arena.contest.contestant.tpl');
}
