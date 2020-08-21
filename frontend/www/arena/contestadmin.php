<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');
\OmegaUp\UITools::redirectToLoginIfNotLoggedIn();

\OmegaUp\UITools::render(
    fn (\OmegaUp\Request $r) => [
        'smartyProperties' => [],
        'template' => 'arena.contest.admin.tpl',
        'inContest' => (
            !isset($_REQUEST['is_practice']) ||
            boolval($_REQUEST['is_practice']) !== true
        ),
    ]
);
