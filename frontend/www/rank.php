<?php
require_once('../server/bootstrap_smarty.php');

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

$smarty->display('../templates/rank.tpl');
