<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');

try {
    $result = \OmegaUp\Controllers\School::apiProfileDetails(
        new \OmegaUp\Request($_REQUEST)
    );
} catch (\OmegaUp\Exceptions\ApiException $e) {
    \OmegaUp\ApiCaller::handleException($e);
}

\OmegaUp\UITools::getSmartyInstance()->assign('profile', $result);

\OmegaUp\UITools::getSmartyInstance()->display(
    sprintf(
        '%s/templates/school.profile.tpl',
        strval(
            OMEGAUP_ROOT
        )
    )
);
