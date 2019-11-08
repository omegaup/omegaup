<?php
require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');

[
    'identity' => $identity,
] = \OmegaUp\Controllers\Session::getCurrentSession();

try {
    $smartyProperties = \OmegaUp\Controllers\User::getRankDetailsForSmarty(
        new \OmegaUp\Request($_REQUEST),
        $identity
    );
} catch (Exception  $e) {
    \OmegaUp\ApiCaller::handleException($e);
}

foreach ($smartyProperties as $key => $value) {
    $smarty->assign($key, $value);
}

$smarty->display(OMEGAUP_ROOT . '/templates/rank.tpl');
