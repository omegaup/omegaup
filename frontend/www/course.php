<?php
require_once('../server/bootstrap.php');

UITools::redirectToLoginIfNotLoggedIn();

$r = new Request($_REQUEST);
$session = SessionController::apiCurrentSession($r)['session'];

$show_assignment = false;
$intro_details = [];

try {
    $intro_details = CourseController::apiIntroDetails($r);
    if (isset($_REQUEST['assignment_alias'])) {
        $show_assignment = true;
    }
} catch (Exception $e) {
    Logger::getLogger('course')->error('APIException ' . $e);
    header('HTTP/1.1 404 Not Found');
    die();
}

if ($intro_details['shouldShowResults'] ||
    ($intro_details['isFirstTimeAccess'] && $intro_details['requests_user_information'] != 'no')) {
    $smarty->assign('course_payload', [
        'name' => $intro_details['name'],
        'description' => $intro_details['description'],
        'alias' => $intro_details['alias'],
        'currentUsername' => $session['user']->username,
        'needsBasicInformation' => $intro_details['basic_information_required'] && !is_null($session['user']) && (
            !$session['user']->country_id || !$session['user']->state_id || !$session['user']->school_id
        ),
        'requestsUserInformation' => $intro_details['requests_user_information']
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
    $smarty->assign('showClarifications', false);
    $smarty->assign('showDeadlines', true);
    $smarty->assign('showNavigation', true);
    $smarty->assign('showPoints', true);
    $smarty->assign('showRanking', $showScoreboard);
    $smarty->display('../templates/arena.contest.tpl');
} else {
    $smarty->display('../templates/course.details.tpl');
}
