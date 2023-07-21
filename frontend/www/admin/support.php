<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');

\OmegaUp\UITools::render(
    function (\OmegaUp\Request $r) {
        $r->ensureMainUserIdentity();
        if (!\OmegaUp\Authorization::isSupportTeamMember($r->identity)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'adminSupportPageNotFound'
            );
        }
        return [
            'templateProperties' => [
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleSupportDashboard'
                ),
                'payload' => [],
            ],
            'entrypoint' => 'admin_support',
        ];
    }
);
