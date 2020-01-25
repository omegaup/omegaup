<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');

\OmegaUp\UITools::render(
    function (\OmegaUp\Request $r): array {
        if (
            !is_null(
                \OmegaUp\Controllers\Session::getCurrentSession()['identity']
            )
        ) {
            // It doesn´t require information for smarty, so we  only show the
            // proper page
            die(header('Location: /course/'));
        }
        return [
            'smartyProperties' => [],
            'template' => 'schools.intro.tpl',
        ];
    }
);
