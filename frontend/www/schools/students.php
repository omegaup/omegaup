<?php
require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');

try {
    /** @var array{payload: array{course: array{status: string, name: string, description: string, alias: string, basic_information_required: bool, requests_user_information: bool, assignments?: array{name: string, description: string, alias: string, publish_time_delay?: int, assignment_type: string, start_time: int, finish_time: int, max_points: float, order: int}, school_id?: int, school_name?: string, start_time?: int, finish_time?: int, is_admin?: bool, public?: bool, show_scoreboard?: bool, student_count?: int}, students: array{name: string, progress: array<string, int>, username: string}[]}} */
    $result = \OmegaUp\Controllers\Course::getStudentsInformationForSmarty(
        new \OmegaUp\Request($_REQUEST)
    );
} catch (Exception $e) {
    \OmegaUp\ApiCaller::handleException($e);
}

foreach ($result as $key => $value) {
    $smarty->assign($key, $value);
}

$constant = 'constant';
$smarty->display("{$constant('OMEGAUP_ROOT')}/templates/course.students.tpl");
