<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 1) . '/server/bootstrap.php');

if (\OmegaUp\Controllers\Session::currentSessionAvailable()) {
    \OmegaUp\Controllers\Session::unregisterSession();
}

\OmegaUp\UITools::render(
    function (\OmegaUp\Request $r): array {
        return [
            'smartyProperties' => [],
            'entrypoint' => 'logout',
        ];
    }
);
