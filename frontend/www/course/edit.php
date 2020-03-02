<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');
if (OMEGAUP_LOCKDOWN) {
    header('Location: /arena/');
    die();
}

\OmegaUp\UITools::render(
    function (\OmegaUp\Request $r) {
        $r->ensureMainUserIdentity();
        return [
            'smartyProperties' => [
                'IS_UPDATE' => 1,
            ],
            'template' => 'course.edit.tpl',
        ];
    }
);
