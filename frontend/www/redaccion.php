<?php
namespace OmegaUp;
require_once(dirname(__DIR__) . '/server/bootstrap.php');

\OmegaUp\UITools::render(
    function (\OmegaUp\Request $r): array {
        return [
            'smartyProperties' => [
                'LOAD_MATHJAX' => true,
            ],
            'entrypoint' => 'problem_statement',
        ];
    }
);
