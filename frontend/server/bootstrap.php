<?php

namespace OmegaUp;

// Set paths
if (!defined('OMEGAUP_ROOT')) {
    define('OMEGAUP_ROOT', dirname(__DIR__));
}
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . __DIR__);
require_once 'autoload.php';

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

define(
    'OMEGAUP_LOCKDOWN',
    isset(
        $_SERVER['HTTP_HOST']
    ) && strpos(
        strval($_SERVER['HTTP_HOST']),
        OMEGAUP_LOCKDOWN_DOMAIN
    ) === 0
);

$contentSecurityPolicy = [
    'script-src' => [
        '\'self\'',
        'https://www.google.com',
        'https://apis.google.com',
        'https://www.gstatic.com',
        'https://js-agent.newrelic.com',
        'https://bam.nr-data.net',
        'https://ssl.google-analytics.com',
        'https://www.google-analytics.com',
        'https://connect.facebook.net',
        'https://platform.twitter.com',
    ],
    'frame-src' => [
        '\'self\'',
        'https://www.facebook.com',
        'https://web.facebook.com',
        'https://platform.twitter.com',
        'https://www.google.com',
        'https://apis.google.com',
        'https://accounts.google.com',
        'https://docs.google.com',
        'https://staticxx.facebook.com',
        'https://syndication.twitter.com',
    ],
    'report-uri' => [
        '/cspreport.php',
    ],
];
if (!is_null(NEW_RELIC_SCRIPT_HASH)) {
    array_push($contentSecurityPolicy['script-src'], NEW_RELIC_SCRIPT_HASH);
}
header('Content-Security-Policy: ' . implode('; ', array_map(
    function ($k) use ($contentSecurityPolicy) {
        return "{$k} " . implode(' ', $contentSecurityPolicy[$k]);
    },
    array_keys($contentSecurityPolicy)
)));
header('X-Frame-Options: DENY');

require_once('libs/third_party/log4php/src/main/php/Logger.php');
\Logger::configure([
    'rootLogger' => [
        'appenders' => ['default'],
        'level' => OMEGAUP_LOG_LEVEL,
    ],
    'loggers' => [
        'csp' => [
            'appenders' => ['csp'],
            'additivity' => false,
        ],
        'jserror' => [
            'appenders' => ['jserror'],
            'additivity' => false,
        ],
    ],
    'appenders' => [
        'default' => [
            'class' => 'LoggerAppenderFile',
            'layout' => [
                'class' => 'LoggerLayoutPattern',
                'params' => [
                    'conversionPattern' => (
                        '%date [%level]: ' .
                        \OmegaUp\Request::requestId() .
                        ' %server{REQUEST_URI} %message (%F:%L) %newline'
                    ),
                ],
            ],
            'params' => [
                'file' => OMEGAUP_LOG_FILE,
                'append' => true,
            ],
        ],
        'csp' => [
            'class' => 'LoggerAppenderFile',
            'layout' => [
                'class' => 'LoggerLayoutPattern',
                'params' => [
                    'conversionPattern' => '%date: %message %newline',
                ],
            ],
            'params' => [
                'file' => OMEGAUP_CSP_LOG_FILE,
                'append' => true,
            ],
        ],
        'jserror' => [
            'class' => 'LoggerAppenderFile',
            'layout' => [
                'class' => 'LoggerLayoutPattern',
                'params' => [
                    'conversionPattern' => '%date: %message %newline',
                ],
            ],
            'params' => [
                'file' => OMEGAUP_JSERROR_LOG_FILE,
                'append' => true,
            ],
        ],
    ],
]);
