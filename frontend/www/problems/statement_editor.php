<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');

\OmegaUp\UITools::render(
    /** @return array{templateProperties: array{fullWidth?: bool, hideFooterAndHeader?: bool, payload: array<string, mixed>, scripts?: list<string>, title: \OmegaUp\TranslationString}, entrypoint: string, inContest?: bool, navbarSection?: string} */
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