<?php

require_once('../../server/bootstrap_smarty.php');
$r = new Request($_REQUEST);
try {
    $course = CourseController::apiListCourses($r);
} catch (UnauthorizedException $e) {
    // No login, so we default to show the intro screen
    // for Schools.
}

if (isset($course)
    && (!empty($course['student']) || !empty($course['admin']))) {
    die(header('Location: /course'));
} else {
    $smarty->display('../templates/schools.intro.tpl');
}
