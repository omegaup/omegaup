<?php

require_once('../../server/bootstrap.php');

UITools::redirectToLoginIfNotLoggedIn();
UITools::redirectIfNoAdmin();

$user = UsersDAO::FindByUsername($_REQUEST['username']);
if (is_null($user)) {
    header('HTTP/1.1 404 Not found');
    die();
}
$emails = EmailsDAO::getByUserId($user->user_id);
$userExperiments = UsersExperimentsDAO::getByUserId($user->user_id);
// TODO: Also support GroupRoles.
$systemRoles = UserRolesDAO::getSystemRoles($user->user_id);
$roles = RolesDAO::getAll();
$systemExperiments = [];
$defines = get_defined_constants(true)['user'];
foreach ($experiments->getAllKnownExperiments() as $experiment) {
    $systemExperiments[] = [
        'name' => $experiment,
        'hash' => Experiments::getExperimentHash($experiment),
        'config' => $experiments->isEnabledByConfig($experiment, $defines),
    ];
}

$payload = [
    'emails' => array_map(function ($email) {
        return $email->email;
    }, $emails),
    'experiments' => array_map(function ($experiment) {
        return $experiment->experiment;
    }, $userExperiments),
    'systemExperiments' => $systemExperiments,
    'roleNames' => array_map(function ($role) {
        return $role->name;
    }, $roles),
    'systemRoles' => $systemRoles,
    'username' => $user->username,
    'verified' => $user->verified != 0,
];
$smarty->assign('payload', $payload);

$smarty->display('../templates/admin.user.tpl');
