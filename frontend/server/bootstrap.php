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
define('OMEGAUP_AUTH_TOKEN_COOKIE_NAME', 'ouat');

$csp_mode = 'Content-Security-Policy';
// TODO(alanboy): Arreglar el login mediante Google+ que nos causa hacer esto.
if (defined('OMEGAUP_BYPASS_CSP_INSECURE_NEVER_USE_THIS')) {
    $csp_mode = 'Content-Security-Policy-Report-Only';
}
header("$csp_mode: script-src 'self' https://www.google.com https://apis.google.com https://www.gstatic.com https://js-agent.newrelic.com https://bam.nr-data.net https://ssl.google-analytics.com; frame-src https://www.facebook.com https://platform.twitter.com https://www.google.com https://apis.google.com https://accounts.google.com https://docs.google.com; report-uri /cspreport.php");
header('X-Frame-Options: DENY');

/*
 * Load libraries
 *
 * */
require_once('libs/log4php/src/main/php/Logger.php');
require_once('libs/dao/model.inc.php');
require_once('libs/SessionManager.php');
require_once('libs/Request.php');
require_once('libs/Validators.php');
require_once('libs/SecurityTools.php');
require_once('libs/Cache.php');
require_once('libs/Authorization.php');
require_once('libs/Git.php');
require_once('libs/Grader.php');
require_once('libs/Broadcaster.php');
require_once('libs/Scoreboard.php');
require_once('libs/ZipStream.php');
require_once('libs/ProblemDeployer.php');
require_once('libs/phpmailer/class.phpmailer.php');
require_once('libs/UITools.php');
require_once('libs/Mailchimp/Mailchimp.php');
require_once('libs/ApiException.php');
require_once('libs/UrlHelper.php');

/*
 * Configurar log4php
 *
 *
 * @todo Email unknown excpetions
 * @todo Print args in call (but don't reveal password in log)
 *
 * */
$request_id = str_replace('.', '', uniqid('', true));
Logger::configure(array(
        'rootLogger' => array(
            'appenders' => array('default'),
            'level' => OMEGAUP_LOG_LEVEL
        ),
        'appenders' => array(
            'default' => array(
                'class' => 'LoggerAppenderFile',
                'layout' => array(
                    'class' => 'LoggerLayoutPattern',
                    'params' => array(
                        'conversionPattern' => "%date [%level]: $request_id %server{REQUEST_URI} %message (%F:%L) %newline",
                    )
                ),
                'params' => array(
                    'file' => OMEGAUP_LOG_FILE,
                    'append' => true
                )
            )
        )
    ));
$log = Logger::getLogger('bootstrap');

/**
 * Load controllers
 *
 * */
require_once('controllers/Controller.php');
require_once('controllers/UserController.php');
require_once('controllers/SessionController.php');
require_once('controllers/ContestController.php');
require_once('controllers/InterviewController.php');
require_once('controllers/ProblemController.php');
require_once('controllers/RunController.php');
require_once('controllers/ScoreboardController.php');
require_once('controllers/TagController.php');
require_once('controllers/ClarificationController.php');
require_once('controllers/TimeController.php');
require_once('controllers/GraderController.php');
require_once('controllers/SchoolController.php');
require_once('controllers/GroupController.php');
require_once('controllers/GroupScoreboardController.php');
require_once('controllers/ResetController.php');

require_once('libs/adodb/adodb.inc.php');
require_once('libs/adodb/adodb-exceptions.inc.php');

require_once('libs/facebook-php-sdk/facebook.php');
require_once('libs/google-api-php-client/src/Google/autoload.php');

global $conn;
$conn = null;

try {
    $conn = ADONewConnection(OMEGAUP_DB_DRIVER);
    // HHVM doesn't like ADOdb's default value of 'false' for port and socket.
    $conn->port = null;
    $conn->socket = null;
    $conn->debug = OMEGAUP_DB_DEBUG;
    $conn->SetFetchMode(ADODB_FETCH_ASSOC);
    // HHVM also doesn't like PConnect. It leaks.
    $conn->Connect(OMEGAUP_DB_HOST, OMEGAUP_DB_USER, OMEGAUP_DB_PASS, OMEGAUP_DB_NAME);
} catch (Exception $databaseConectionException) {
    $log->error($databaseConectionException);

    if (!$conn) {
        /**
         * Dispatch missing parameters
         * */
        header('HTTP/1.1 500 INTERNAL SERVER ERROR');

        die(json_encode(array(
                    'status' => 'error',
                    'error' => 'Conection to the database has failed.',
                    'errorcode' => 1
                )));
    }
}
$conn->SetCharSet('utf8');
$conn->EXECUTE('SET NAMES \'utf8\';');

include('libs/smarty/libs/Smarty.class.php');
$smarty = new Smarty();
$smarty->setTemplateDir(__DIR__ . '/../templates/');

if (/* do we need smarty to load? */true && !(defined('IS_TEST') && IS_TEST === true)) {
    $smarty->assign('CURRENT_USER_IS_ADMIN', 0);
    if (defined('SMARTY_CACHE_DIR')) {
        $smarty->setCacheDir(SMARTY_CACHE_DIR)->setCompileDir(SMARTY_CACHE_DIR);
    }

    $smarty->assign('GOOGLECLIENTID', OMEGAUP_GOOGLE_CLIENTID);

    $smarty->assign('LOGGED_IN', '0');
    UITools::$IsLoggedIn = false;
    $smarty->assign('FB_URL', SessionController::getFacebookLoginUrl());

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
        $smarty->assign('CURRENT_USER_AUTH_TOKEN', $session['auth_token']);
        $smarty->assign('CURRENT_USER_GRAVATAR_URL_128', '<img src="https://secure.gravatar.com/avatar/' . md5($session['email']) . '?s=92">');
        $smarty->assign('CURRENT_USER_GRAVATAR_URL_16', '<img src="https://secure.gravatar.com/avatar/' . md5($session['email']) . '?s=16">');
        $smarty->assign('CURRENT_USER_GRAVATAR_URL_32', '<img src="https://secure.gravatar.com/avatar/' . md5($session['email']) . '?s=32">');
        $smarty->assign('CURRENT_USER_GRAVATAR_URL_51', '<img src="https://secure.gravatar.com/avatar/' . md5($session['email']) . '?s=51">');

        UITools::$isAdmin = $session['is_admin'];
        $userRequest['username'] = $session['user']->username;
    } else {
        $smarty->assign('CURRENT_USER_GRAVATAR_URL_128', '<img src="/media/avatar_92.png">');
        $smarty->assign('CURRENT_USER_GRAVATAR_URL_16', '<img src="/media/avatar_16.png">');
    }

    $lang = UserController::getPreferredLanguage($userRequest);

    if (defined('OMEGAUP_DEVELOPMENT_MODE') && OMEGAUP_DEVELOPMENT_MODE) {
        $smarty->force_compile = true;
        $smarty->caching = 0;
    }
} else {
    // During testing We need smarty to load strings from *.lang files
    $lang = 'pseudo';
}

$smarty->configLoad(__DIR__ . '/../templates/'. $lang . '.lang');

// Load pager class
require_once('libs/Pager.php');
