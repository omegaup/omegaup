<?php

require_once('../server/bootstrap.php');

UITools::redirectToLoginIfNotLoggedIn();

$r = new Request($_REQUEST);
$session = SessionController::apiCurrentSession($r)['session'];

if (!OMEGAUP_ALLOW_PRIVILEGE_SELF_ASSIGNMENT) {
    header('HTTP/1.1 404 Not found');
    die();
}
// TODO: Also support GroupRoles.
$systemRoles = UserRolesDAO::getSystemRoles($session['user']->user_id);
$roles = RolesDAO::getAll();
$systemGroups = UserRolesDAO::getSystemGroups($session['user']->user_id);
$groups = GroupsDAO::SearchByName(':');

$payload = [
    'roleNames' => array_map(function ($role) {
        return $role->name;
    }, $roles),
    'systemRoles' => $systemRoles,
    'groupNames' => array_map(function ($group) {
        return $group->name;
    }, $groups),
    'systemGroups' => $systemGroups,
    'username' => $session['user']->username,
];

$smarty->assign('payload', $payload);

$smarty->display('../templates/permissions.tpl');
