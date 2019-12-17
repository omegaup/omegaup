<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');
\OmegaUp\UITools::redirectToLoginIfNotLoggedIn();

\OmegaUp\UITools::renderContest(
    function (\OmegaUp\Request $r): array {
        return \OmegaUp\Controllers\Course::getCourseDetailsForSmarty(
            $r
        );
    }
);
