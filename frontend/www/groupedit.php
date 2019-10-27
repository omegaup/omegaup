<?php

require_once('../server/bootstrap_smarty.php');

/** @var array{valid: bool, email: string|null, user: \OmegaUp\DAO\VO\Users|null, identity: \OmegaUp\DAO\VO\Identities|null, auth_token: string|null, is_admin: bool} */
[
    'identity' => $_identity,
] = \OmegaUp\Controllers\Session::getCurrentSession();

if (is_null($_identity)) {
    header('HTTP/1.1 404 Not Found');
    die();
}

$isOrganizer = \OmegaUp\Experiments::getInstance()->isEnabled(
    \OmegaUp\Experiments::IDENTITIES
) &&
    \OmegaUp\Authorization::canCreateGroupIdentities($_identity);
$smarty->assign('IS_ORGANIZER', $isOrganizer);
$smarty->assign('payload', [
    'countries' => \OmegaUp\DAO\Countries::getAll(null, 100, 'name'),
]);
$smarty->display('../templates/group.edit.tpl');
