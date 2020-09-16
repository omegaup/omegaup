<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');
\OmegaUp\UITools::redirectToLoginIfNotLoggedIn();

\OmegaUp\UITools::render(
    fn (\OmegaUp\Request $r) => [
        'smartyProperties' => [
            'fullWidth' => true,
            'payload' => [],
            'title' => 'wordsGlobalSubmissions',
        ],
        'entrypoint' => 'arena_admin',
    ]
);
