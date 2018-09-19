<?php

require_once('../../server/bootstrap.php');
$r = new Request($_REQUEST);
try {
    $course = CourseController::apiListCourses($r);
} catch (UnauthorizedException $e) {
    // No login, so we default to show the intro screen
    // for Schools.
}

if (isset($course)
    && (count($course['student']) != 0 || count($course['admin']) != 0)) {
    die(header('Location: /course'));
} else {
    $smarty->display('../templates/schools.intro.tpl');
}
