<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 1) . '/server/bootstrap.php');

if (\OmegaUp\Controllers\Session::currentSessionAvailable()) {
    \OmegaUp\Controllers\Session::unregisterSession();
}

\OmegaUp\UITools::render(
    function (\OmegaUp\Request $r): array {
        $scripts = [];
        if (
            defined('OMEGAUP_GOOGLE_CLIENTID') &&
            !empty(OMEGAUP_GOOGLE_CLIENTID)
        ) {
            $scripts[] = 'https://accounts.google.com/gsi/client';
        }
        return [
            'templateProperties' => [
                'scripts' => $scripts,
                'payload' => [],
                'title' => new \OmegaUp\TranslationString('omegaupTitleLogout'),
            ],
            'entrypoint' => 'logout',
        ];
    }
);
