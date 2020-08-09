<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');
\OmegaUp\UITools::redirectToLoginIfNotLoggedIn();

\OmegaUp\UITools::render(
    function (\OmegaUp\Request $r): array {
        return [
            'smartyProperties' => [
                'title' => 'wordsGlobalSubmissions',
                'fullWidth' => true,
            ],
            'entrypoint' => 'arena_admin',
        ];
    }
);
