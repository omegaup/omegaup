<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');
\OmegaUp\UITools::getSmartyInstance()->assign('titleClassName', 'course-title');
\OmegaUp\UITools::getSmartyInstance()->display(
    sprintf(
        '%s/templates/arena.scoreboard.tpl',
        strval(
            OMEGAUP_ROOT
        )
    )
);
