<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');
\OmegaUp\UITools::getSmartyInstance()->assign('IS_UPDATE', 1);
\OmegaUp\UITools::getSmartyInstance()->display(
    sprintf(
        '%s/templates/course.edit.tpl',
        strval(
            OMEGAUP_ROOT
        )
    )
);
