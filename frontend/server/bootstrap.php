<?php

namespace OmegaUp;

/**
 * @return list<string>
 */
function getCriticalSecretNames(): array {
    return [
        'OMEGAUP_DB_USER',
        'OMEGAUP_DB_PASS',
        'OMEGAUP_DB_HOST',
        'OMEGAUP_DB_NAME',

        'OMEGAUP_EXPERIMENT_SECRET',
        'OMEGAUP_GITSERVER_SECRET_KEY',
        'OMEGAUP_GITSERVER_PUBLIC_KEY',
        'OMEGAUP_GITSERVER_SECRET_TOKEN',
        'OMEGAUP_GRADER_SECRET',
        'OMEGAUP_COURSE_CLONE_SECRET_KEY',

        'OMEGAUP_RABBITMQ_USERNAME',
        'OMEGAUP_RABBITMQ_PASSWORD',

        'OMEGAUP_FB_APPID',
        'OMEGAUP_FB_SECRET',
        'OMEGAUP_GOOGLE_SECRET',
        'OMEGAUP_GOOGLE_CLIENTID',
        'OMEGAUP_GITHUB_CLIENT_ID',
        'OMEGAUP_GITHUB_CLIENT_SECRET',
        'OMEGAUP_GA_ID',

        'OMEGAUP_RECAPTCHA_SECRET',
    ];
}

/**
 * Ensures required secrets are set to non placeholder values.
 */
function validateCriticalSecrets(): void {
    if (PHP_SAPI === 'cli') {
        return;
    }

    $environment = defined(
        'OMEGAUP_ENVIRONMENT'
    ) ? OMEGAUP_ENVIRONMENT : 'production';

    $strictEnvironments = ['sandbox', 'production'];
    /** @psalm-suppress TypeDoesNotContainType this can change depending on environment */
    if (!in_array($environment, $strictEnvironments, true)) {
        return;
    }

    $placeholderValues = ['CHANGE_ME', 'xxxxx', ''];

    $failures = [];

    foreach (getCriticalSecretNames() as $name) {
        if (!defined($name)) {
            $failures[] = "{$name}: missing from configuration";
            continue;
        }

        /** @var mixed $value */
        $value = constant($name);
        if (!is_string($value)) {
            $failures[] = "{$name}: expected string, got " . gettype($value);
            continue;
        }

        if (in_array($value, $placeholderValues, true)) {
            $reason = $value === '' ? 'empty' : "placeholder value '{$value}'";
            $failures[] = "{$name}: {$reason}";
        }
    }

    if (empty($failures)) {
        return;
    }

    $message = sprintf(
        '[config] Startup aborted for environment "%s": invalid secrets detected:%s%s',
        $environment,
        PHP_EOL,
        implode(PHP_EOL, array_map(
            /**
             * @param string $failure
             */
            fn (string $failure): string => "  - {$failure}",
            $failures
        ))
    );

    error_log($message);

    if (PHP_SAPI !== 'cli') {
        http_response_code(500);
        echo 'Configuration error: one or more required secrets are missing or invalid. '
            . 'Please check the server logs for details.';
    }

    exit(1);
}

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

    validateCriticalSecrets();
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
        'blob:',
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

// Use NewRelic formatter only if available, fallback to LineFormatter
if (class_exists('\NewRelic\Monolog\Enricher\Formatter')) {
    $logFormatter = new \NewRelic\Monolog\Enricher\Formatter();
} else {
    $logFormatter = new \Monolog\Formatter\LineFormatter();
}

$logHandler = new \Monolog\Handler\StreamHandler(OMEGAUP_LOG_FILE, $logLevel);
$logHandler->setFormatter($logFormatter);

$rootLogger = new \Monolog\Logger('omegaup');
$rootLogger->pushProcessor(
    new \Monolog\Processor\WebProcessor()
);

// Add NewRelic processor only if available
if (class_exists('\NewRelic\Monolog\Enricher\Processor')) {
    $rootLogger->pushProcessor(
        new \NewRelic\Monolog\Enricher\Processor()
    );
}

$rootLogger->pushHandler($logHandler);
\Monolog\Registry::addLogger($rootLogger);
\Monolog\ErrorHandler::register($rootLogger);
