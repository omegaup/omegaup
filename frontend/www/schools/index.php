<?php
require_once('../../server/bootstrap_smarty.php');

try {
    $hasActivityInCourses = CourseController::userHasActivityInCourses(
        new Request($_REQUEST)
    );
} catch (Exception $e) {
    ApiCaller::handleException($e);
}

// It doesn´t require information for smarty, so we  only show the proper page
if ($hasActivityInCourses) {
    die(header('Location: /course'));
}

$smarty->display('../templates/schools.intro.tpl');
