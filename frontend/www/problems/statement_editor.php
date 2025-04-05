<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');

\OmegaUp\UITools::render(
    fn (\OmegaUp\Request $r) => [
        'templateProperties' => [
            'title' => new \OmegaUp\TranslationString(
                'omegaupTitleStatementEditor',
            ),
            'payload' => [],
        ],
        'entrypoint' => 'problem_statement',
    ]
); 