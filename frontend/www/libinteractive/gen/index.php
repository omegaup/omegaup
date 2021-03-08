<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 3) . '/server/bootstrap.php');
if (OMEGAUP_LOCKDOWN) {
    header('Location: /arena/');
    die();
}

\OmegaUp\UITools::render(
    fn (\OmegaUp\Request $r) => \OmegaUp\Controllers\Problem::getLibinteractiveGenForTypeScript(
        $r
    )
);
