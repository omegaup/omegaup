<?php

require_once(__DIR__ . '/../bootstrap.php');

Logger::configure([
    'rootLogger' => [
        'appenders' => ['default'],
        'level' => OMEGAUP_LOG_LEVEL,
    ],
    'appenders' => [
        'default' => [
            'class' => 'LoggerAppenderConsole',
            'layout' => [
                'class' => 'LoggerLayoutPattern',
                'params' => [
                    'conversionPattern' => (
                        '%date [%level]: %server{REQUEST_URI} %message (%F:%L) %newline'
                    ),
                ],
            ],
            'params' => [
                'target' => 'stderr',
            ],
        ],
    ],
]);

/**
 * @return Generator<int, string>
 */
function listDir(string $path): Generator {
    $dh = opendir($path);
    if (!is_resource($dh)) {
        die("Failed to open {$path}");
    }
    while (($problem = readdir($dh)) !== false) {
        if ($problem == '.' || $problem == '..') {
            continue;
        }
        yield $problem;
    }
    closedir($dh);
}

function pathJoin(string $parent, string ...$components): string {
    $path = rtrim($parent, '/');
    foreach ($components as $component) {
        if (strpos('/', $component) === 0) {
            $path = rtrim($component, '/');
            continue;
        }
        $path .= '/' . rtrim($component, '/');
    }
    return $path;
}

foreach (listDir(TEMPLATES_PATH) as $problemAlias) {
    $problemDeployer = new \OmegaUp\ProblemDeployer($problemAlias);
    foreach (
        listDir(
            pathJoin(
                TEMPLATES_PATH,
                $problemAlias
            )
        ) as $problemCommit
    ) {
        $problemDeployer->generateLibinteractiveTemplates($problemCommit);
    }
}
