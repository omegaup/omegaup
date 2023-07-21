<?php

namespace OmegaUp;

// Set paths
if (!defined('OMEGAUP_ROOT')) {
    define('OMEGAUP_ROOT', dirname(__DIR__));
}
require_once __DIR__ . '/../../vendor/autoload.php';

// Set default time
date_default_timezone_set('UTC');

/** @psalm-suppress RedundantCondition IS_TEST may be defined as true in tests. */
if (!defined('IS_TEST') || IS_TEST !== true) {
    if (!is_file(__DIR__ . '/config.php')) { ?>
<!doctype html>
<HTML>
    <head>
        <link rel="stylesheet" type="text/css" href="css/style.css">
    </head>
    <body style="padding:5px">
        <h1>No config file.</h1>
        <p>You are missing the config file. These are the default values:</p>
        <pre class="code" style="margin: 3em; border: 1px solid #000; background: #ccc;">
        <?php echo htmlspecialchars(
            file_get_contents(
                __DIR__ . '/config.default.php'
            )
        ); ?>
        </pre>
        <p>Create a file called <code>config.php</code> &emdash; the settings there will
        override any of the default values.</p>
    </body>
</html>
        <?php
        exit;
    }
    require_once(__DIR__ . '/config.php');
    require_once(__DIR__ . '/config.default.php');
}

if (!defined('OMEGAUP_LOCKDOWN')) {
    define(
        'OMEGAUP_LOCKDOWN',
        isset($_SERVER['HTTP_HOST']) &&
        strpos($_SERVER['HTTP_HOST'], OMEGAUP_LOCKDOWN_DOMAIN) === 0
    );
}

$contentSecurityPolicy = [
    'connect-src' => [
        '\'self\'',
        'https://*.google-analytics.com',
        'https://*.analytics.google.com',
        'https://*.googletagmanager.com',
        'https://accounts.google.com',
    ],
    'img-src' => [
        // Problems can embed images from anywhere in the internet, so we need
        // to be permissive here.
        '*',
        '\'self\'',
        'data:',
        'blob:',
        'https://*.google-analytics.com',
        'https://*.googletagmanager.com',
        'https://secure.gravatar.com',
    ],
    'script-src' => [
        '\'self\'',
        'https://www.google.com',
        'https://accounts.google.com',
        'https://www.gstatic.com',
        'https://js-agent.newrelic.com',
        'https://bam.nr-data.net',
        'https://*.googletagmanager.com',
        'https://ssl.google-analytics.com',
        'https://www.google-analytics.com',
        'https://connect.facebook.net',
        'https://platform.twitter.com',
    ],
    'frame-src' => [
        '\'self\'',
        'https://www.facebook.com',
        'https://web.facebook.com',
        'https://www.youtube.com',
        'https://platform.twitter.com',
        'https://www.google.com',
        'https://accounts.google.com',
        'https://docs.google.com',
        'https://staticxx.facebook.com',
        'https://syndication.twitter.com',
    ],
    'report-uri' => [
        '/cspreport.php',
    ],
];
/** @var string|null $nrsh */
$nrsh = NEW_RELIC_SCRIPT_HASH;
if (!is_null($nrsh)) {
    array_push($contentSecurityPolicy['script-src'], $nrsh);
}
header('Content-Security-Policy: ' . implode('; ', array_map(
    fn ($k) => "{$k} " . implode(' ', $contentSecurityPolicy[$k]),
    array_keys($contentSecurityPolicy)
)));
header('X-Frame-Options: DENY');

// Configure the root logger
/** @psalm-suppress UndefinedDocblockClass Level is declared in a phpstan-type annotation. */
$logLevel = \Monolog\Logger::toMonologLevel(OMEGAUP_LOG_LEVEL);
$logFormatter = new \NewRelic\Monolog\Enricher\Formatter();
$logHandler = new \Monolog\Handler\StreamHandler(OMEGAUP_LOG_FILE, $logLevel);
$logHandler->setFormatter($logFormatter);

$rootLogger = new \Monolog\Logger('omegaup');
$rootLogger->pushProcessor(
    new \Monolog\Processor\WebProcessor()
);
$rootLogger->pushProcessor(
    new \NewRelic\Monolog\Enricher\Processor()
);
$rootLogger->pushHandler($logHandler);
\Monolog\Registry::addLogger($rootLogger);
\Monolog\ErrorHandler::register($rootLogger);
