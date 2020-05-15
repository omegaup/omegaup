<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 1) . '/server/bootstrap.php');

\OmegaUp\UITools::render(
    function (\OmegaUp\Request $r): array {
        return [
            'smartyProperties' => [],
            'entrypoint' => 'login_password_recover',
        ];
    }
);
