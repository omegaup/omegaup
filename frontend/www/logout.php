<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 1) . '/server/bootstrap.php');

if (\OmegaUp\Controllers\Session::currentSessionAvailable()) {
    \OmegaUp\Controllers\Session::unregisterSession();
}

\OmegaUp\UITools::render(
    function (\OmegaUp\Request $r): array {
        if (defined(OMEGAUP_GOOGLE_CLIENTID)) {
            $script = ["<script src='https://apis.google.com/js/api.js' async defer></script>"];
        }
        return [
            'smartyProperties' => [
                'scripts' => $script ?? [],
            ],
            'entrypoint' => 'logout',
        ];
    }
);
