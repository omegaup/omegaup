<?php

require_once('../../server/bootstrap_smarty.php');

\OmegaUp\UITools::redirectToLoginIfNotLoggedIn();
\OmegaUp\UITools::redirectIfNoAdmin();

$user = \OmegaUp\DAO\Users::FindByUsername($_REQUEST['username']);
if (is_null($user)) {
    header('HTTP/1.1 404 Not found');
    die();
}
$emails = \OmegaUp\DAO\Emails::getByUserId($user->user_id);
$userExperiments = \OmegaUp\DAO\UsersExperiments::getByUserId($user->user_id);
// TODO: Also support GroupRoles.
$systemRoles = \OmegaUp\DAO\UserRoles::getSystemRoles($user->user_id);
$roles = \OmegaUp\DAO\Roles::getAll();
$systemExperiments = [];
$defines = get_defined_constants(true)['user'];
foreach (\OmegaUp\Experiments::getInstance()->getAllKnownExperiments() as $experiment) {
    $systemExperiments[] = [
        'name' => $experiment,
        'hash' => \OmegaUp\Experiments::getExperimentHash($experiment),
        'config' => \OmegaUp\Experiments::getInstance()->isEnabledByConfig($experiment, $defines),
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
        return ['name' => $role->name];
    }, $roles),
    'systemRoles' => $systemRoles,
    'username' => $user->username,
    'verified' => $user->verified != 0,
];
$smarty->assign('payload', $payload);

$smarty->display('../templates/admin.user.tpl');
