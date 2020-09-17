<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 1) . '/server/bootstrap.php');

if (!isset($_GET['email']) || !isset($_GET['reset_token'])) {
    header('Location: /');
    die();
}
\OmegaUp\UITools::render(
    fn (\OmegaUp\Request $r) => [
        'smartyProperties' => [
            'payload' => [
                'email' => $_GET['email'],
                'resetToken' => $_GET['reset_token']
            ],
            'title' => new \OmegaUp\TranslationString(
                'passwordResetResetTitle'
            ),
        ],
        'entrypoint' => 'login_password_reset',
    ]
);
