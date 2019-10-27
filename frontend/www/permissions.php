<?php

require_once('../server/bootstrap_smarty.php');

if (!OMEGAUP_ALLOW_PRIVILEGE_SELF_ASSIGNMENT) {
    header('HTTP/1.1 404 Not found');
    die();
}

\OmegaUp\UITools::redirectToLoginIfNotLoggedIn();

/** @var array{valid: bool, email: string|null, user: \OmegaUp\DAO\VO\Users|null, identity: \OmegaUp\DAO\VO\Identities|null, auth_token: string|null, is_admin: bool} */
[
    'user' => $_user,
] = \OmegaUp\Controllers\Session::getCurrentSession();
if (is_null($_user)) {
    header('HTTP/1.1 404 Not found');
    die();
}

$systemRoles = \OmegaUp\DAO\UserRoles::getSystemRoles(
    $_user->user_id
);
$roles = \OmegaUp\DAO\Roles::getAll();
$systemGroups = \OmegaUp\DAO\UserRoles::getSystemGroups(
    $_user->user_id
);
$groups = \OmegaUp\DAO\Groups::SearchByName('omegaup:');
$userSystemRoles = [];
$userSystemGroups = [];
foreach ($roles as $key => $role) {
    $userSystemRoles[$key] = [
        'name' => $role->name,
        'value' => in_array($role->name, $systemRoles),
    ];
}
foreach ($groups as $key => $group) {
    $userSystemGroups[$key] = [
        'name' => $group->name,
        'value' => in_array($group->name, $systemGroups),
    ];
}
$payload = [
    'userSystemRoles' => $userSystemRoles,
    'userSystemGroups' => $userSystemGroups,
    'username' => $_user->username,
];

$smarty->assign('payload', $payload);

$smarty->display('../templates/permissions.tpl');
