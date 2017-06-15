<?php
require_once('../server/bootstrap.php');
if (!$experiments->isEnabled(Experiments::SCHOOLS)) {
    header('HTTP/1.1 404 Not Found');
    die();
}

$show_intro = false;
$show_assignment = false;

try {
    /*
     * @TODO: Check if we should show intro
     */
    if (isset($_REQUEST['assignment_alias'])) {
        $show_assignment = true;
    }
} catch (Exception $e) {
    header('HTTP/1.1 404 Not Found');
    die();
}

if ($show_intro) {
    $smarty->display('../templates/arena.course.intro.tpl');
} elseif ($show_assignment) {
    $smarty->assign('jsfile', '/ux/assignment.js');
    $smarty->assign('admin', false);
    $smarty->assign('practice', false);
    $smarty->assign('showRanking', true);
    $smarty->display('../templates/arena.contest.tpl');
} else {
    $smarty->display('../templates/course.details.tpl');
}
