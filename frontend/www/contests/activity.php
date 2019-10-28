<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');
\OmegaUp\UITools::getSmartyInstance()->display(
    sprintf(
        '%s/templates/contest.activity.tpl',
        strval(
            OMEGAUP_ROOT
        )
    )
);
