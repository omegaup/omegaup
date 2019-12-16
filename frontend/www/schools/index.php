<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');

try {
    // It doesn´t require information for smarty, so we  only show the proper page
    if (
        \OmegaUp\Controllers\Course::userHasActivityInCourses(
            new \OmegaUp\Request($_REQUEST)
        )
    ) {
        die(header('Location: /course/'));
    }
} catch (\Exception $e) {
    \OmegaUp\ApiCaller::handleException($e);
}

\OmegaUp\UITools::getSmartyInstance()->display(
    sprintf(
        '%s/templates/schools.intro.tpl',
        strval(
            OMEGAUP_ROOT
        )
    )
);
