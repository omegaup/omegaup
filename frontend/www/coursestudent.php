<?php
require_once('../server/bootstrap.php');
if (!$experiments->isEnabled(Experiments::SCHOOLS)) {
    header('HTTP/1.1 404 Not Found');
    die();
}

$course_alias = $_REQUEST['course'];
$student_username = $_REQUEST['student'];

try {
    $payload = [
        'course' => CourseController::apiAdminDetails(new Request([
            'alias' => $course_alias,
        ])),
        'students' => CourseController::apiListStudents(new Request([
            'course_alias' => $course_alias,
        ]))['students'],
        'student' => $student_username,
    ];

    $smarty->assign('payload', $payload);
    $smarty->display('../templates/course.student.tpl');
} catch (APIException $e) {
    Logger::getLogger('coursestudents')->error('APIException ' . $e);
    header('HTTP/1.1 404 Not Found');
    die();
}
