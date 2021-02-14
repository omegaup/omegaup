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
            'smartyProperties' => [
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleSupportDashboard'
                ),
            ],
            'entrypoint' => 'admin_support',
        ];
    }
);
