<?php
namespace OmegaUp;
require_once(dirname(__DIR__) . '/server/bootstrap.php');

\OmegaUp\UITools::render(
    /** @return array{templateProperties: array{fullWidth?: bool, hideFooterAndHeader?: bool, payload: array<string, mixed>, title: \OmegaUp\TranslationString}, entrypoint: string, inContest?: bool, navbarSection?: string} */
    fn (\OmegaUp\Request $r) => [
        'templateProperties' => [
            'title' => new \OmegaUp\TranslationString(
                'omegaupTitleStatementEditor'
            ),
            'payload' => [],
            'fullWidth' => true,
        ],
        'entrypoint' => 'problem_statement',
    ]
);
