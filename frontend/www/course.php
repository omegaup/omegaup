<?php
require_once('../server/bootstrap.php');
if (!$experiments->isEnabled(Experiments::SCHOOLS)) {
    header('HTTP/1.1 404 Not Found');
    die();
}

$r = new Request($_REQUEST);
$session = SessionController::apiCurrentSession($r)['session'];

$show_intro = false;
$show_assignment = false;
$result = [];

try {
    $show_intro = CourseController::shouldShowIntro($r);

    if ($show_intro) {
        $result = CourseController::apiPublicDetails($r);
    }

    if (isset($_REQUEST['assignment_alias'])) {
        $show_assignment = true;
    }
} catch (Exception $e) {
    Logger::getLogger('course')->error('APIException ' . $e);
    header('HTTP/1.1 404 Not Found');
    die();
}

if ($show_intro) {
    $smarty->assign('course_payload', [
        'name' => $result['name'],
        'description' => $result['description'],
        'alias' => $result['alias'],
        'currentUsername' => $session['user']->username,
    ]);
    $smarty->display('../templates/arena.course.intro.tpl');
} elseif ($show_assignment) {
    $course = CoursesDAO::getByAlias($_REQUEST['course_alias']);
    if (is_null($course)) {
        header('HTTP/1.1 404 Not Found');
        die();
    }
    $showScoreboard = $session['valid'] && Authorization::isCourseAdmin($session['user']->user_id, $course);
    $smarty->assign('jsfile', '/ux/assignment.js');
    $smarty->assign('admin', false);
    $smarty->assign('showDeadlines', true);
    $smarty->assign('showNavigation', true);
    $smarty->assign('showPoints', true);
    $smarty->assign('showRanking', $showScoreboard);
    $smarty->display('../templates/arena.contest.tpl');
} else {
    $smarty->display('../templates/course.details.tpl');
}
