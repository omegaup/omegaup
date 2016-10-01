<?php
require_once('../../server/bootstrap.php');

$show_intro = true;

try {
    /*
     * @TODO: Check if we should show intro
     */
} catch (Exception $e) {
    header('HTTP/1.1 404 Not Found');
    die(file_get_contents('../404.html'));
}

if ($show_intro) {
    $smarty->display('../../templates/arena.course.intro.tpl');
} else {
    /* @TODO show course contents here */
}
