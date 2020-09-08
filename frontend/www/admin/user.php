<?php

namespace OmegaUp;

require_once('../../server/bootstrap.php');

\OmegaUp\UITools::redirectToLoginIfNotLoggedIn();
\OmegaUp\UITools::redirectIfNoAdmin();

$username = \OmegaUp\Request::getRequestVar('username');
if (is_null($username)) {
    header('HTTP/1.1 404 Not found');
    die();
}
$user = \OmegaUp\DAO\Users::FindByUsername($username);
if (is_null($user) || is_null($user->user_id)) {
    header('HTTP/1.1 404 Not found');
    die();
}
$emails = \OmegaUp\DAO\Emails::getByUserId($user->user_id);
$userExperiments = \OmegaUp\DAO\UsersExperiments::getByUserId(
    $user->user_id
);
// TODO: Also support GroupRoles.
$systemRoles = \OmegaUp\DAO\UserRoles::getSystemRoles($user->user_id);
$roles = \OmegaUp\DAO\Roles::getAll();
$systemExperiments = [];
/** @var array<string, mixed> */
$defines = get_defined_constants(true)['user'];
foreach (\OmegaUp\Experiments::getInstance()->getAllKnownExperiments() as $experiment) {
    $systemExperiments[] = [
        'name' => $experiment,
        'hash' => \OmegaUp\Experiments::getExperimentHash($experiment),
        'config' => \OmegaUp\Experiments::getInstance()->isEnabledByConfig(
            $experiment,
            $defines
        ),
    ];
}

$payload = [
    'emails' => array_map(fn ($email) => $email->email, $emails),
    'experiments' => array_map(
        fn ($experiment) => $experiment->experiment,
        $userExperiments
    ),
    'systemExperiments' => $systemExperiments,
    'roleNames' => array_map(fn ($role) => ['name' => $role->name], $roles),
    'systemRoles' => $systemRoles,
    'username' => $username,
    'verified' => $user->verified != 0,
];

\OmegaUp\UITools::getSmartyInstance()->assign('payload', $payload);
\OmegaUp\UITools::getSmartyInstance()->display(
    '../templates/admin.user.tpl'
);
