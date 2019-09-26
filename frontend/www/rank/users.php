<?php
require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');

try {
    $session = \OmegaUp\Controllers\Session::apiCurrentSession(
        new \OmegaUp\Request($_REQUEST)
    )['session'];
    $smartyProperties = \OmegaUp\Controllers\User::getRankDetailsForSmarty(
        new \OmegaUp\Request($_REQUEST),
        $session['identity'],
        $smarty
    );
} catch (Exception  $e) {
    \OmegaUp\ApiCaller::handleException($e);
}

foreach ($smartyProperties as $key => $value) {
    $smarty->assign($key, $value);
}

$smarty->display(OMEGAUP_ROOT . '/templates/rank.tpl');
