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
