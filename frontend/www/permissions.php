<?php

require_once('../server/bootstrap.php');

if (!OMEGAUP_ALLOW_PRIVILEGE_SELF_ASSIGNMENT) {
    header('HTTP/1.1 404 Not found');
    die();
}

UITools::redirectToLoginIfNotLoggedIn();

$r = new Request($_REQUEST);
$session = SessionController::apiCurrentSession($r)['session'];

// TODO: Also support GroupRoles.
$systemRoles = UserRolesDAO::getSystemRoles($session['user']->user_id);
$roles = RolesDAO::getAll();
$systemGroups = UserRolesDAO::getSystemGroups($session['user']->user_id);
$groups = GroupsDAO::SearchByName('omegaup:');
$rolesUser = [];
$groupsUser = [];
foreach ($roles as $key => $role) {
    $rolesUser[$key]['title'] = $role->name;
    $rolesUser[$key]['value'] = in_array($role->name, $systemRoles);
}
foreach ($groups as $key => $group) {
    $groupsUser[$key]['title'] = $group->name;
    $groupsUser[$key]['value'] = in_array($group->name, $systemGroups);
}
$payload = [
    'rolesUser' => $rolesUser,
    'groupsUser' => $groupsUser,
    'username' => $session['user']->username,
];

$smarty->assign('payload', $payload);

$smarty->display('../templates/permissions.tpl');
