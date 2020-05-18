<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 1) . '/server/bootstrap.php');
if (OMEGAUP_LOCKDOWN) {
    header('Location: /');
    die();
}

\OmegaUp\UITools::render(
    function (\OmegaUp\Request $r) {
        return [
            'smartyProperties' => [
                'payload' => [
                    'email' => $_GET['email'],
                    'resetToken' => $_GET['reset_token']
                ],
            ],
            'entrypoint' => 'login_password_reset',
        ];
    }
);

/*require_once('../server/bootstrap_smarty.php');
if (isset($_GET['email']) && isset($_GET['reset_token'])) {
    $smarty->assign('EMAIL', $_GET['email']);
    $smarty->assign('RESET_TOKEN', $_GET['reset_token']);
} else {
    die(header('Location: /'));
}

$smarty->display('../templates/login.password.reset.tpl');*/
