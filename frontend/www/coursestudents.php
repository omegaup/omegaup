<?php
require_once('../server/bootstrap.php');
if (!$experiments->isEnabled(Experiments::SCHOOLS)) {
    header('HTTP/1.1 404 Not Found');
    die();
}

$course_alias = $_REQUEST['course'];

try {
    $payload = [
        'course' => CourseController::apiAdminDetails(new Request([
            'alias' => $course_alias,
        ])),
        'students' => CourseController::apiListStudents(new Request([
            'course_alias' => $course_alias,
        ]))['students']
    ];

    $smarty->assign('payload', $payload);
    $smarty->display('../templates/course.students.tpl');
} catch (APIException $e) {
    Logger::getLogger('coursestudents')->error('APIException ' . $e);
    header('HTTP/1.1 404 Not Found');
    die();
}
