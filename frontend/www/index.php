<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 1) . '/server/bootstrap.php');
if (OMEGAUP_LOCKDOWN) {
    header('Location: /arena/');
    die();
}

\OmegaUp\UITools::render(
    function (\OmegaUp\Request $r) {
        return \OmegaUp\Controllers\User::getIndexDetailsForSmarty(
            $r
        );
    }
);
