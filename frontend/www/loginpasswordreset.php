<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 1) . '/server/bootstrap.php');

if (isset($_GET['email']) && isset($_GET['reset_token'])) {
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
} else {
    die(header('Location: /'));
}
