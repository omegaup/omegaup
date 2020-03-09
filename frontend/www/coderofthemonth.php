<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 1) . '/server/bootstrap.php');

\OmegaUp\UITools::render(
    function (\OmegaUp\Request $r): array {
        return \OmegaUp\Controllers\User::getCoderOfTheMonthDetailsForSmarty(
            $r
        );
    }
);
