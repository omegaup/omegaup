<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');
if (OMEGAUP_LOCKDOWN) {
    header('Location: /arena/');
    die();
}

\OmegaUp\UITools::getSmartyInstance()->display(
    \OmegaUp\UITools::templatePath('scoreboardmerge')
);
