<?php
// Set default time
date_default_timezone_set('UTC');

//set paths
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . __DIR__);

if (!(defined('IS_TEST') && IS_TEST === true)) {
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
        <?php echo htmlspecialchars(file_get_contents(__DIR__ . '/config.default.php')); ?>
        </pre>
        <p>Create a file called <code>config.php</code> &emdash; the settings there will
        override any of the default values.</p>
    </body>
</html>
        <?php
        exit;
    } else {
        require_once(__DIR__ . '/config.php');
        require_once(__DIR__ . '/config.default.php');
    }
}

define('OMEGAUP_LOCKDOWN', isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], OMEGAUP_LOCKDOWN_DOMAIN) === 0);

$csp_mode = 'Content-Security-Policy';
header("$csp_mode: script-src 'self' https://www.google.com https://apis.google.com https://www.gstatic.com https://js-agent.newrelic.com https://bam.nr-data.net https://ssl.google-analytics.com https://connect.facebook.net https://platform.twitter.com; frame-src 'self' https://www.facebook.com https://web.facebook.com https://platform.twitter.com https://www.google.com https://apis.google.com https://accounts.google.com https://docs.google.com https://staticxx.facebook.com https://syndication.twitter.com; report-uri /cspreport.php");
header('X-Frame-Options: DENY');

require_once('libs/third_party/log4php/src/main/php/Logger.php');

// Load DAOs and controllers lazily.
require_once('libs/dao/Estructura.php');
spl_autoload_register(function ($classname) {
    $suffix = 'Controller';
    if (substr_compare($classname, $suffix, strlen($classname) - strlen($suffix)) === 0) {
        // TODO: Figure out a better way of dealing with this.
        $qualityNomination = 'Qualitynomination';
        if (substr_compare($classname, $qualityNomination, 0, strlen($qualityNomination)) === 0) {
            $classname = 'QualityNominationController';
        }
        include_once "controllers/{$classname}.php";
        return;
    }
    $suffix = 'DAO';
    if (substr_compare($classname, $suffix, strlen($classname) - strlen($suffix)) === 0) {
        $classname = substr($classname, 0, strlen($classname) - strlen($suffix));
    }
    $classname = preg_replace('/([a-z])([A-Z])/', '$1_$2', $classname);
    $filename = __DIR__ . "/libs/dao/{$classname}.dao.php";
    if (file_exists($filename)) {
        include_once $filename;
    }
});

require_once('libs/ApiException.php');
require_once('libs/ApiUtils.php');
require_once('libs/Authorization.php');
require_once('libs/Broadcaster.php');
require_once('libs/Cache.php');
require_once('libs/Experiments.php');
require_once('libs/Grader.php');
require_once('libs/Pager.php');
require_once('libs/Request.php');
require_once('libs/Scoreboard.php');
require_once('libs/SecurityTools.php');
require_once('libs/SessionManager.php');
require_once('libs/Time.php');
require_once('libs/Validators.php');

Logger::configure([
        'rootLogger' => [
            'appenders' => ['default'],
            'level' => OMEGAUP_LOG_LEVEL
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
                            Request::requestId() .
                            ' %server{REQUEST_URI} %message (%F:%L) %newline'
                        ),
                    ]
                ],
                'params' => [
                    'file' => OMEGAUP_LOG_FILE,
                    'append' => true
                ]
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
            ]
        ]
    ]);
$log = Logger::getLogger('bootstrap');

require_once('libs/third_party/adodb/adodb.inc.php');
require_once('libs/third_party/adodb/adodb-exceptions.inc.php');

global $conn;
$conn = null;

try {
    $conn = ADONewConnection(OMEGAUP_DB_DRIVER);
    $conn->debug = OMEGAUP_DB_DEBUG;
    $conn->SetFetchMode(ADODB_FETCH_ASSOC);
    $conn->PConnect(OMEGAUP_DB_HOST, OMEGAUP_DB_USER, OMEGAUP_DB_PASS, OMEGAUP_DB_NAME);
} catch (Exception $databaseConectionException) {
    $log->error($databaseConectionException);

    if (!$conn) {
        /**
         * Dispatch missing parameters
         * */
        header('HTTP/1.1 500 INTERNAL SERVER ERROR');

        die(json_encode([
                    'status' => 'error',
                    'error' => 'Conection to the database has failed.',
                    'errorcode' => 1
                ]));
    }
}
$conn->SetCharSet('utf8');
$conn->EXECUTE('SET NAMES \'utf8\';');

$session = SessionController::apiCurrentSession(new Request($_REQUEST))['session'];
$experiments = new Experiments(
    $_REQUEST,
    array_key_exists('user', $session) ? $session['user'] : null
);
