<?php
require_once('../../server/bootstrap_smarty.php');

try {
    $r = new Request([
        'auth_token' => array_key_exists('ouat', $_REQUEST) ? $_REQUEST['ouat'] : null,
        'contest_alias' => $_REQUEST['contest_alias'],
    ]);
    $introDetails = ContestController::showContestIntro($r);
} catch (Exception $e) {
    header('HTTP/1.1 404 Not Found');
    die(file_get_contents('../404.html'));
}

if ($introDetails['shouldShowIntro']) {
    $smarty->assign('needsBasicInformation', $introDetails['needsBasicInformation']);
    $smarty->assign('requestsUserInformation', $introDetails['requestUserInformation']);
    $smarty->assign('privacyStatement', $introDetails['privacyStatement']);
}
if ($introDetails['shouldShowIntroForNotLoggedIdentity'] ||
    $introDetails['shouldShowIntro']
) {
    $smarty->display('../../templates/arena.contest.intro.tpl');
} else {
    $smarty->display('../../templates/arena.contest.contestant.tpl');
}
