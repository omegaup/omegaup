<?php

require_once('../../server/bootstrap_smarty.php');

$show_intro = true;

try {
    $r = new \OmegaUp\Request([
        'auth_token' => array_key_exists(
            'ouat',
            $_REQUEST
        ) ? $_REQUEST['ouat'] : null,
        'contest_alias' => $_REQUEST['alias'],
    ]);

    $show_intro = \OmegaUp\Controllers\Interview::showIntro($r);
} catch (Exception $e) {
    header('HTTP/1.1 404 Not Found');
    die(file_get_contents('../404.html'));
}

if ($show_intro) {
    $smarty->display('../../templates/interviews.arena.intro.tpl');
} else {
    $smarty->display('../../templates/arena.contest.interview.tpl');
}
