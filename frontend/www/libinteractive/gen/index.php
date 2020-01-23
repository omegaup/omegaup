<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 3) . '/server/bootstrap.php');
if (OMEGAUP_LOCKDOWN) {
    header('Location: /arena/');
    die();
}

\OmegaUp\UITools::render(
    function (\OmegaUp\Request $r): array {
        return \OmegaUp\Controllers\Problem::getLibinteractiveGenForSmarty(
            $r
        );
    }
);
