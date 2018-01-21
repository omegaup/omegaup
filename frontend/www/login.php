<?php
define('OMEGAUP_BYPASS_CSP_INSECURE_NEVER_USE_THIS', true);
require_once('../server/bootstrap.php');
require_once('api/ApiCaller.php');

$triedToLogin = false;
$emailVerified = true;
$c_Session = new SessionController;

if (isset($_POST['request']) && ($_POST['request'] == 'login')) {
    // user wants to login natively

    $r = new Request();
    $r['usernameOrEmail'] = $_POST['user'];
    $r['password'] = $_POST['pass'];
    $r->method = 'UserController::apiLogin';
    $response = ApiCaller::call($r);

    if ($response['status'] === 'error') {
        if ($response['errorcode'] === 600 || $response['errorcode'] === 601) {
            $emailVerified = false;
        }
    }

    $triedToLogin = true;
} elseif (OMEGAUP_VALIDATE_CAPTCHA && isset($_POST['request']) && $_POST['request'] == 'register') {
    // Something failed in the JavaScript side. This definitely will not have
    // ReCAPTCHA validation, so let's error out with that.
    $smarty->assign('ERROR_TO_USER', 'NATIVE_LOGIN_FAILED');
    $smarty->assign('ERROR_MESSAGE', $smarty->getConfigVars('unableToVerifyCaptcha'));
}

if (isset($_GET['linkedin'])) {
    if (isset($_GET['code']) && isset($_GET['state'])) {
        $response = $c_Session->LoginViaLinkedIn();
    }
    $triedToLogin = true;
} elseif (isset($_GET['fb'])) {
    $response = $c_Session->LoginViaFacebook();
    $triedToLogin = true;
}

if (isset($_GET['shva'])) {
    $triedToLogin = true;
}

if ($c_Session->CurrentSessionAvailable()) {
    if (isset($_GET['redirect'])) {
        die(header('Location: ' . $_GET['redirect']));
    } else {
        die(header('Location: /profile/'));
    }
} elseif ($triedToLogin) {
    if (isset($response['error'])) {
        $smarty->assign('ERROR_TO_USER', 'NATIVE_LOGIN_FAILED');
        $smarty->assign('ERROR_MESSAGE', $response['error']);
    } else {
        $smarty->assign('ERROR_TO_USER', 'THIRD_PARTY_LOGIN_FAILED');
        $smarty->assign('ERROR_MESSAGE', $smarty->getConfigVars('loginFederatedFailed'));
    }
}

// Only generate Login URLs if we actually need them.
$smarty->assign('FB_URL', SessionController::getFacebookLoginUrl());
$smarty->assign('LINKEDIN_URL', SessionController::getLinkedInLoginUrl());
$smarty->assign('VALIDATE_RECAPTCHA', OMEGAUP_VALIDATE_CAPTCHA);
$smarty->assign('payload', ['validateRecaptcha' => OMEGAUP_VALIDATE_CAPTCHA]);
$smarty->display('../templates/login.tpl');
