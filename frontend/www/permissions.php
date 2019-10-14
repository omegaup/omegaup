<?php

require_once('../server/bootstrap_smarty.php');

if (!OMEGAUP_ALLOW_PRIVILEGE_SELF_ASSIGNMENT) {
    header('HTTP/1.1 404 Not found');
    die();
}

\OmegaUp\UITools::redirectToLoginIfNotLoggedIn();

$r = new \OmegaUp\Request($_REQUEST);
$session = \OmegaUp\Controllers\Session::apiCurrentSession($r)['session'];

$systemRoles = \OmegaUp\DAO\UserRoles::getSystemRoles(
    $session['user']->user_id
);
$roles = \OmegaUp\DAO\Roles::getAll();
$systemGroups = \OmegaUp\DAO\UserRoles::getSystemGroups(
    $session['user']->user_id
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
    'username' => $session['user']->username,
];

$smarty->assign('payload', $payload);

$smarty->display('../templates/permissions.tpl');
