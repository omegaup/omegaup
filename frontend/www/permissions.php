<?php

require_once('../server/bootstrap_smarty.php');

if (!OMEGAUP_ALLOW_PRIVILEGE_SELF_ASSIGNMENT) {
    header('HTTP/1.1 404 Not found');
    die();
}

UITools::redirectToLoginIfNotLoggedIn();

$r = new Request($_REQUEST);
$session = SessionController::apiCurrentSession($r)['session'];

$systemRoles = UserRolesDAO::getSystemRoles($session['user']->user_id);
$roles = RolesDAO::getAll();
$systemGroups = UserRolesDAO::getSystemGroups($session['user']->user_id);
$groups = GroupsDAO::SearchByName('omegaup:');
$userSystemRoles = [];
$userSystemGroups = [];
foreach ($roles as $key => $role) {
    $userSystemRoles[$key]['title'] = $role->name;
    $userSystemRoles[$key]['value'] = in_array($role->name, $systemRoles);
}
foreach ($groups as $key => $group) {
    $userSystemGroups[$key]['alias'] = $group->name;
    $userSystemGroups[$key]['value'] = in_array($group->name, $systemGroups);
}
$payload = [
    'userSystemRoles' => $userSystemRoles,
    'userSystemGroups' => $userSystemGroups,
    'username' => $session['user']->username,
];

$smarty->assign('payload', $payload);

$smarty->display('../templates/permissions.tpl');
