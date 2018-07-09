<?php
/*
 * Bootstrap file
 *
 *
 * */

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
// TODO(alanboy): Arreglar el login mediante Google+ que nos causa hacer esto.
if (defined('OMEGAUP_BYPASS_CSP_INSECURE_NEVER_USE_THIS')) {
    $csp_mode = 'Content-Security-Policy-Report-Only';
}
header("$csp_mode: script-src 'self' https://www.google.com https://apis.google.com https://www.gstatic.com https://js-agent.newrelic.com https://bam.nr-data.net https://ssl.google-analytics.com https://connect.facebook.net https://platform.twitter.com; frame-src https://www.facebook.com https://web.facebook.com https://platform.twitter.com https://www.google.com https://apis.google.com https://accounts.google.com https://docs.google.com https://staticxx.facebook.com https://syndication.twitter.com; report-uri /cspreport.php");
header('X-Frame-Options: DENY');

/*
 * Load libraries
 *
 * */
require_once('libs/third_party/log4php/src/main/php/Logger.php');
require_once('libs/dao/model.inc.php');

require_once('libs/ApiException.php');
require_once('libs/Authorization.php');
require_once('libs/Broadcaster.php');
require_once('libs/Cache.php');
require_once('libs/Email.php');
require_once('libs/Experiments.php');
require_once('libs/Git.php');
require_once('libs/Grader.php');
require_once('libs/LinkedIn.php');
require_once('libs/Pager.php');
require_once('libs/ProblemDeployer.php');
require_once('libs/Request.php');
require_once('libs/Scoreboard.php');
require_once('libs/SecurityTools.php');
require_once('libs/SessionManager.php');
require_once('libs/Time.php');
require_once('libs/UITools.php');
require_once('libs/UrlHelper.php');
require_once('libs/Validators.php');
require_once('libs/third_party/Mailchimp/Mailchimp.php');
require_once('libs/third_party/ZipStream.php');
require_once('libs/third_party/phpmailer/class.phpmailer.php');
require_once('libs/third_party/phpmailer/class.smtp.php');

/*
 * Configurar log4php
 *
 *
 * @todo Email unknown excpetions
 * @todo Print args in call (but don't reveal password in log)
 *
 * */
$request_id = str_replace('.', '', uniqid('', true));
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
                        'conversionPattern' => "%date [%level]: $request_id %server{REQUEST_URI} %message (%F:%L) %newline",
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

/**
 * Load controllers
 *
 * */
require_once('controllers/Controller.php');
require_once('controllers/UserController.php');
require_once('controllers/IdentityController.php');
require_once('controllers/ACLController.php');
require_once('controllers/SessionController.php');
require_once('controllers/ContestController.php');
require_once('controllers/InterviewController.php');
require_once('controllers/ProblemController.php');
require_once('controllers/ProblemsetController.php');
require_once('controllers/RunController.php');
require_once('controllers/ScoreboardController.php');
require_once('controllers/TagController.php');
require_once('controllers/ClarificationController.php');
require_once('controllers/TimeController.php');
require_once('controllers/GraderController.php');
require_once('controllers/SchoolController.php');
require_once('controllers/CourseController.php');
require_once('controllers/GroupController.php');
require_once('controllers/GroupScoreboardController.php');
require_once('controllers/ResetController.php');
require_once('controllers/QualityNominationController.php');

require_once('libs/third_party/adodb/adodb.inc.php');
require_once('libs/third_party/adodb/adodb-exceptions.inc.php');

require_once('libs/third_party/facebook-php-graph-sdk/src/Facebook/autoload.php');
require_once('libs/third_party/google-api-php-client/src/Google/autoload.php');

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

include('libs/third_party/smarty/libs/Smarty.class.php');
$smarty = new Smarty();
$smarty->setTemplateDir(__DIR__ . '/../templates/');

if (!defined('IS_TEST') || IS_TEST !== true) {
    $smarty->assign('CURRENT_USER_IS_ADMIN', 0);
    if (defined('SMARTY_CACHE_DIR')) {
        $smarty->setCacheDir(SMARTY_CACHE_DIR)->setCompileDir(SMARTY_CACHE_DIR);
    }

    $smarty->assign('GOOGLECLIENTID', OMEGAUP_GOOGLE_CLIENTID);

    $smarty->assign('LOGGED_IN', '0');
    UITools::$IsLoggedIn = false;

    if (defined('OMEGAUP_GA_TRACK')  && OMEGAUP_GA_TRACK) {
        $smarty->assign('OMEGAUP_GA_TRACK', 1);
        $smarty->assign('OMEGAUP_GA_ID', OMEGAUP_GA_ID);
    } else {
        $smarty->assign('OMEGAUP_GA_TRACK', 0);
    }

    $userRequest = new Request($_REQUEST);
    $session = SessionController::apiCurrentSession($userRequest)['session'];
    if ($session['valid']) {
        $smarty->assign('LOGGED_IN', '1');
        UITools::$IsLoggedIn = true;

        $smarty->assign('CURRENT_USER_USERNAME', $session['user']->username);
        $smarty->assign('CURRENT_USER_EMAIL', $session['email']);
        $smarty->assign('CURRENT_USER_IS_EMAIL_VERIFIED', $session['user']->verified);
        $smarty->assign('CURRENT_USER_IS_ADMIN', $session['is_admin']);
        $smarty->assign('CURRENT_USER_IS_REVIEWER', Authorization::isQualityReviewer($session['identity']->identity_id));
        $smarty->assign('CURRENT_USER_AUTH_TOKEN', $session['auth_token']);
        $smarty->assign('CURRENT_USER_GRAVATAR_URL_128', '<img src="https://secure.gravatar.com/avatar/' . md5($session['email']) . '?s=92">');
        $smarty->assign('CURRENT_USER_GRAVATAR_URL_16', '<img src="https://secure.gravatar.com/avatar/' . md5($session['email']) . '?s=16">');
        $smarty->assign('CURRENT_USER_GRAVATAR_URL_32', '<img src="https://secure.gravatar.com/avatar/' . md5($session['email']) . '?s=32">');
        $smarty->assign('CURRENT_USER_GRAVATAR_URL_51', '<img src="https://secure.gravatar.com/avatar/' . md5($session['email']) . '?s=51">');

        $smarty->assign(
            'currentUserInfo',
            [
                'username' => $session['user']->username,
            ]
        );

        UITools::$IsAdmin = $session['is_admin'];
        $userRequest['username'] = $session['user']->username;
    } else {
        $userRequest['username'] = null;
        $smarty->assign('CURRENT_USER_GRAVATAR_URL_128', '<img src="/media/avatar_92.png">');
        $smarty->assign('CURRENT_USER_GRAVATAR_URL_16', '<img src="/media/avatar_16.png">');
    }

    $lang = UserController::getPreferredLanguage($userRequest);

    if (defined('OMEGAUP_ENVIRONMENT') && OMEGAUP_ENVIRONMENT === 'development') {
        $smarty->force_compile = true;
    } else {
        $smarty->compile_check = false;
    }
} else {
    // During testing We need smarty to load strings from *.lang files
    $lang = 'pseudo';
    $session = ['valid' => false];
}

$smarty->configLoad(__DIR__ . '/../templates/'. $lang . '.lang');
$smarty->addPluginsDir(__DIR__ . '/../smarty_plugins/');

$experiments = new Experiments(
    $_REQUEST,
    array_key_exists('user', $session) ? $session['user'] : null
);
$smarty->assign('ENABLED_EXPERIMENTS', $experiments->getEnabledExperiments());
