<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');
\OmegaUp\UITools::getSmartyInstance()->assign('titleClassName', 'course-title');
\OmegaUp\UITools::getSmartyInstance()->display(
    \OmegaUp\UITools::templatePath('arena.scoreboard')
);
