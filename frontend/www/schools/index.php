<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');

try {
    $hasActivityInCourses = \OmegaUp\Controllers\Course::userHasActivityInCourses(
        new \OmegaUp\Request($_REQUEST)
    );
} catch (\Exception $e) {
    \OmegaUp\ApiCaller::handleException($e);
}

// It doesn´t require information for smarty, so we  only show the proper page
if ($hasActivityInCourses) {
    die(header('Location: /course/'));
}

\OmegaUp\UITools::getSmartyInstance()->display(
    sprintf(
        '%s/templates/schools.intro.tpl',
        strval(
            OMEGAUP_ROOT
        )
    )
);
