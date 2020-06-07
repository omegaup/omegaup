<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 1) . '/server/bootstrap.php');

if (\OmegaUp\Controllers\Session::currentSessionAvailable()) {
    \OmegaUp\Controllers\Session::unregisterSession();
}

\OmegaUp\UITools::render(
    function (\OmegaUp\Request $r): array {
        $scripts = [];  // por consistencia con el nombre en L16.
        if (defined('OMEGAUP_GOOGLE_CLIENTID')) {
            $scripts[] = 'https://apis.google.com/js/api.js';
        }
        return [
            'smartyProperties' => [
                'scripts' => $scripts,
            ],
            'entrypoint' => 'logout',
        ];
    }
);
