<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 1) . '/server/bootstrap.php');
if (OMEGAUP_LOCKDOWN) {
    header('Location: /arena/');
    die();
}

\OmegaUp\UITools::render(
    fn (\OmegaUp\Request $r) => \OmegaUp\Controllers\User::getIndexDetailsForSmarty(
        $r
    )
);
