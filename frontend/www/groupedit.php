<?php

require_once('../server/bootstrap_smarty.php');

[
    'identity' => $identity,
] = \OmegaUp\Controllers\Session::getCurrentSession();

if (is_null($identity)) {
    header('HTTP/1.1 404 Not Found');
    die();
}

$isOrganizer = \OmegaUp\Experiments::getInstance()->isEnabled(
    \OmegaUp\Experiments::IDENTITIES
) &&
    \OmegaUp\Authorization::canCreateGroupIdentities($identity);
$smarty->assign('IS_ORGANIZER', $isOrganizer);
$smarty->assign('payload', [
    'countries' => \OmegaUp\DAO\Countries::getAll(null, 100, 'name'),
]);
$smarty->display('../templates/group.edit.tpl');
