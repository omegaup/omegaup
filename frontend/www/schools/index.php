<?php
require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');

try {
    $hasActivityInCourses = \OmegaUp\Controllers\Course::userHasActivityInCourses(
        new \OmegaUp\Request($_REQUEST)
    );
} catch (Exception $e) {
    \OmegaUp\ApiCaller::handleException($e);
}

// It doesn´t require information for smarty, so we  only show the proper page
if ($hasActivityInCourses) {
    die(header('Location: /course/'));
}

$constant = 'constant';
$smarty->display("{$constant('OMEGAUP_ROOT')}/templates/schools.intro.tpl");
