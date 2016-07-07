<?php

require_once('../../server/bootstrap.php');

$show_intro = true;

try {
    $r = new Request(array(
        'auth_token' => array_key_exists('ouat', $_REQUEST) ? $_REQUEST['ouat'] : null,
        'contest_alias' => $_REQUEST['contest_alias'],
    ));

    $show_intro = InterviewController::showIntro($r);
} catch (Exception $e) {
    header('HTTP/1.1 404 Not Found');
    die(file_get_contents('../404.html'));
}

if ($show_intro) {
    $smarty->display('../../templates/interviews.arena.intro.tpl');
} else {
    $smarty->display('../../templates/interviews.arena.index.tpl');
}
