<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');

try {
    $result = \OmegaUp\Controllers\Problem::getProblemEditDetailsForSmarty(
        new \OmegaUp\Request($_REQUEST)
    );
} catch (\Exception $e) {
    \OmegaUp\ApiCaller::handleException($e);
}

foreach ($result as $key => $value) {
    \OmegaUp\UITools::getSmartyInstance()->assign($key, $value);
}

\OmegaUp\UITools::getSmartyInstance()->display(
    sprintf(
        '%s/templates/problem.edit.tpl',
        strval(
            OMEGAUP_ROOT
        )
    )
);
