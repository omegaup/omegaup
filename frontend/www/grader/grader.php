<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');

\OmegaUp\UITools::render(
    function (\OmegaUp\Request $r): array {
        return [
            'templateProperties' => [
                'title' => new \OmegaUp\TranslationString(
                    'omegaUp problemless grader'
                ),
                'fullWidth' => true,
                'hideFooterAndHeader' => true,
            ],
            'entrypoint' => 'ide'
        ];
    }
);
