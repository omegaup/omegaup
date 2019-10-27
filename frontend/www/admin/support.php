<?php

require_once('../../server/bootstrap_smarty.php');

\OmegaUp\UITools::redirectToLoginIfNotLoggedIn();

/** @var array{valid: bool, email: string|null, user: \OmegaUp\DAO\VO\Users|null, identity: \OmegaUp\DAO\VO\Identities|null, auth_token: string|null, is_admin: bool} */
[
    'identity' => $_identity,
] = \OmegaUp\Controllers\Session::getCurrentSession();
if (
    is_null($_identity) ||
    !\OmegaUp\Authorization::isSupportTeamMember($_identity)
) {
    header('HTTP/1.1 404 Not found');
    die();
}

$smarty->display('../templates/admin.support.tpl');
