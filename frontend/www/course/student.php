<?php
require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');

try {
    /** @var array{payload: array{course: array{status: string, name: string, description: string, alias: string, basic_information_required: bool, requests_user_information: string, assignments?: array<array-key, array{name: string, description: string, alias: string, publish_time_delay?: int, assignment_type: string, start_time: int, finish_time: int, max_points: float, order: int, scoreboard_url: string, scoreboard_url_admin: string}>, school_id?: int|null, start_time?: int, finish_time?: int, is_admin?: bool, public?: bool, show_scoreboard?: bool, student_count?: null|int, school_name?: string|null}, students: array{name: string, progress: array<string, null|float>, username: string}[], student: string}} */
    $result = \OmegaUp\Controllers\Course::getStudentsInformationForSmarty(
        new \OmegaUp\Request($_REQUEST)
    );
} catch (Exception $e) {
    \OmegaUp\ApiCaller::handleException($e);
}

foreach ($result as $key => $value) {
    $smarty->assign($key, $value);
}

$smarty->display(sprintf('%s/templates/course.student.tpl', OMEGAUP_ROOT));
