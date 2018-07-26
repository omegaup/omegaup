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

if ($intro_details['shouldShowResults'] || $intro_details['showAcceptTeacher'] ||
    ($intro_details['isFirstTimeAccess'] && $intro_details['requests_user_information'] != 'no')) {
    $smarty->assign('course_payload', [
        'name' => $intro_details['name'],
        'description' => $intro_details['description'],
        'alias' => $intro_details['alias'],
        'currentUsername' => $session['user']->username,
        'needsBasicInformation' => $intro_details['basic_information_required'] && !is_null($session['user']) && (
            !$session['user']->country_id || !$session['user']->state_id || !$session['user']->school_id
        ),
        'requestsUserInformation' => $intro_details['requests_user_information'],
        'showAcceptTeacher' => $intro_details['showAcceptTeacher'],
        'statements' => [
            'privacy' => [
                'markdown' => $intro_details['privacy_statement_markdown'],
                'gitObjectId' => $intro_details['git_object_id'],
                'statementType' => $intro_details['statement_type'],
            ],
            'acceptTeacher' => [
                'markdown' => $intro_details['accept_teacher_statement']['markdown'],
                'gitObjectId' => $intro_details['accept_teacher_statement']['git_object_id'],
                'statementType' => 'accept_teacher',
            ],
        ],
    ]);

    $smarty->display('../templates/arena.course.intro.tpl');
} elseif ($show_assignment) {
    $course = CoursesDAO::getByAlias($_REQUEST['course_alias']);
    if (is_null($course)) {
        header('HTTP/1.1 404 Not Found');
        die();
    }
    $showScoreboard = $session['valid'] && Authorization::isCourseAdmin($session['identity']->identity_id, $course);
    $smarty->assign('showRanking', $showScoreboard);
    $smarty->display('../templates/arena.contest.course.tpl');
} else {
    $course = CoursesDAO::getByAlias($_REQUEST['course_alias']);
    $showScoreboard = $session['valid'] && Authorization::isCourseAdmin($session['user']->user_id, $course);
    $smarty->assign('showRanking', $showScoreboard);
    $smarty->display('../templates/course.details.tpl');
}
