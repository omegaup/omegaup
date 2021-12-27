<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');

\OmegaUp\UITools::render(
    fn (\OmegaUp\Request $r) => [
        'templateProperties' => [
            'payload' => [],
            'title' => new \OmegaUp\TranslationString('omegaupTitleGroupsNew'),
        ],
        'entrypoint' => 'group_new',
    ]
);
