<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');
$smartyProperties = [];
$template = '';
try {
    [
        'smartyProperties' => $smartyProperties,
        'template' => $template
    ] = \OmegaUp\Controllers\QualityNomination::getMyQualityNominationListForSmarty(
        new \OmegaUp\Request($_REQUEST)
    );
} catch (\Exception $e) {
    \OmegaUp\ApiCaller::handleException($e);
}

foreach ($smartyProperties as $key => $value) {
    \OmegaUp\UITools::getSmartyInstance()->assign($key, $value);
}

\OmegaUp\UITools::getSmartyInstance()->display(
    sprintf(
        '%s/templates/%s',
        strval(
            OMEGAUP_ROOT
        ),
        $template
    )
);
