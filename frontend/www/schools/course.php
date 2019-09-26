<?php
require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');

\OmegaUp\UITools::redirectToLoginIfNotLoggedIn();

try {
    [
        'smartyProperties' => $smartyProperties,
        'template' => $template
    ] = \OmegaUp\Controllers\Course::getCourseDetailsForSmarty(
        new \OmegaUp\Request($_REQUEST)
    );
} catch (Exception $e) {
    \OmegaUp\ApiCaller::handleException($e);
}

foreach ($smartyProperties as $key => $value) {
    $smarty->assign($key, $value);
}

$smarty->display(OMEGAUP_ROOT . "/templates/{$template}");
