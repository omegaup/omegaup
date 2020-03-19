<?php
namespace OmegaUp;
require_once(dirname(__DIR__) . '/server/bootstrap.php');

\OmegaUp\UITools::render(
    function (\OmegaUp\Request $r): array {
        return \OmegaUp\Controllers\Badge::getDetailsForSmarty(
            $r
        );
    }
);
