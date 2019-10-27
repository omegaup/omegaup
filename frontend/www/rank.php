<?php
require_once('../server/bootstrap_smarty.php');

/** @var array{valid: bool, email: string|null, user: \OmegaUp\DAO\VO\Users|null, identity: \OmegaUp\DAO\VO\Identities|null, auth_token: string|null, is_admin: bool} */
[
    'identity' => $_identity,
] = \OmegaUp\Controllers\Session::getCurrentSession();

try {
    $smartyProperties = \OmegaUp\Controllers\User::getRankDetailsForSmarty(
        new \OmegaUp\Request($_REQUEST),
        $_identity,
        $smarty
    );
} catch (Exception  $e) {
    \OmegaUp\ApiCaller::handleException($e);
}

foreach ($smartyProperties as $key => $value) {
    $smarty->assign($key, $value);
}

$smarty->display('../templates/rank.tpl');
