<?php
namespace OmegaUp;
require_once(dirname(__DIR__) . '/server/bootstrap.php');

\OmegaUp\UITools::render(
    fn (\OmegaUp\Request $r) => [
        'smartyProperties' => [],
        'entrypoint' => 'problem_statement',
    ]
);
