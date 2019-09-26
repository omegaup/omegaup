<?php
require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');

$course_alias = $_REQUEST['course'];
$student_username = $_REQUEST['student'];

try {
    $payload = [
        'course' => \OmegaUp\Controllers\Course::apiAdminDetails(new \OmegaUp\Request([
            'alias' => $course_alias,
        ])),
        'students' => \OmegaUp\Controllers\Course::apiListStudents(new \OmegaUp\Request([
            'course_alias' => $course_alias,
        ]))['students'],
        'student' => $student_username,
    ];

    $smarty->assign('payload', $payload);
    $smarty->display(OMEGAUP_ROOT . '/templates/course.student.tpl');
} catch (\OmegaUp\Exceptions\ApiException $e) {
    Logger::getLogger('coursestudents')->error('APIException ' . $e);
    header('HTTP/1.1 404 Not Found');
    die();
}
