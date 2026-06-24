<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 1) . '/server/bootstrap.php');

\OmegaUp\UITools::render(
    fn (\OmegaUp\Request $r) => [
        'templateProperties' => [
            'payload' => [],
            'title' => new \OmegaUp\TranslationString('omegaupTitleHelp'),
        ],
        'entrypoint' => 'common_help',
    ]
);
