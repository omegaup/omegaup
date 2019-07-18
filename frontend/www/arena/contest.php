<?php
require_once('../../server/bootstrap_smarty.php');

try {
    $r = new Request([
        'auth_token' => array_key_exists('ouat', $_REQUEST) ? $_REQUEST['ouat'] : null,
        'contest_alias' => $_REQUEST['contest_alias'],
    ]);
    $showIntro = ContestController::showIntro($r);
    if ($showIntro) {
        $result = ContestController::getContestDetailsForSmarty($r);
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
    $smarty->display('../../templates/arena.contest.contestant.tpl');
}
