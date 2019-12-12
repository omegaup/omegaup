<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');

\OmegaUp\UITools::render(
    function (): array {
        return \OmegaUp\Controllers\School::getTemporaryRankForSmarty();
    }
);
