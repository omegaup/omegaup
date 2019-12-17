<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');

\OmegaUp\UITools::renderContest(
    function (\OmegaUp\Request $r): array {
        return \OmegaUp\Controllers\Contest::getContestDetailsForSmarty(
            $r
        );
    }
);
