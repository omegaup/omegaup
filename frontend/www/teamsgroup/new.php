<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');

\OmegaUp\UITools::render(
    fn (\OmegaUp\Request $r) => [
        'smartyProperties' => [
            'payload' => [],
            'title' => new \OmegaUp\TranslationString(
                'omegaupTitleTeamsGroupNew'
            ),
        ],
        'entrypoint' => 'teams_group_new',
    ]
);
