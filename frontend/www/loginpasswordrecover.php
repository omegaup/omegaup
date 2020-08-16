<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 1) . '/server/bootstrap.php');

\OmegaUp\UITools::render(
    fn (\OmegaUp\Request $r) => [
        'smartyProperties' => [],
        'entrypoint' => 'login_password_recover',
    ]
);
