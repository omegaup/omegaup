<?php

require_once('../../server/bootstrap.php');
$currentSession = SessionController::apiCurrentSession()['session'];
$r = new Request($_REQUEST);
$course = CourseController::apiListCourses($r);

if (count($course['student'])!=0 or count($course['admin'])!=0) {
    die(header('Location: /course'));
} else {
    $smarty->display('../templates/schools.intro.tpl');
}
