<?php
require_once('../server/bootstrap_smarty.php');

$course_alias = $_REQUEST['course'];

try {
    $payload = [
        'course' => CourseController::apiAdminDetails(new \OmegaUp\Request([
            'alias' => $course_alias,
        ])),
        'students' => CourseController::apiListStudents(new \OmegaUp\Request([
            'course_alias' => $course_alias,
        ]))['students']
    ];

    $smarty->assign('payload', $payload);
    $smarty->display('../templates/course.students.tpl');
} catch (\OmegaUp\Exceptions\ApiException $e) {
    Logger::getLogger('coursestudents')->error('APIException ' . $e);
    header('HTTP/1.1 404 Not Found');
    die();
}
