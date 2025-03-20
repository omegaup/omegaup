<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');

\OmegaUp\UITools::render(
    fn (\OmegaUp\Request $r) => [
        'entrypoint' => 'schools_rank',
        'templateProperties' => [
            'payload' => \OmegaUp\Controllers\School::getRankForTypeScript(
                $r
            )['templateProperties']['payload'],
            'title' => new \OmegaUp\TranslationString(
                'omegaupTitleSchoolsRank'
            ),
        ],
        'inContest' => false,
        'navbarSection' => 'schools',
    ]
);
