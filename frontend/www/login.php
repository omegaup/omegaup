<?php
require_once('../server/bootstrap_smarty.php');

$triedToLogin = false;
$emailVerified = true;
$c_Session = new \OmegaUp\Controllers\Session();

if (isset($_POST['request']) && ($_POST['request'] == 'login')) {
    // user wants to login natively
    try {
        $response = \OmegaUp\Controllers\User::apiLogin(new \OmegaUp\Request([
            'usernameOrEmail' => $_POST['user'],
            'password' => $_POST['pass'],
        ]));
    } catch (\OmegaUp\Exceptions\EmailNotVerifiedException $e) {
        $emailVerified = false;
        $response = $e->asResponseArray();
    } catch (\OmegaUp\Exceptions\ApiException $e) {
        $response = $e->asResponseArray();
    } catch (\Exception $e) {
        self::$log->error($e);
        $apiException = new \OmegaUp\Exceptions\InternalServerErrorException($e);
        /** @var array<string, mixed> */
        $response = $apiException->asResponseArray();
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
        /** @var array<string, mixed> */
        $response = $c_Session->LoginViaLinkedIn();
    }
    $triedToLogin = true;
} elseif (isset($_GET['fb'])) {
    /** @var array<string, mixed> */
    $response = $c_Session->LoginViaFacebook();
    $triedToLogin = true;
}

if (isset($_GET['shva'])) {
    $triedToLogin = true;
}

function shouldRedirect($url) {
    $redirect_parsed_url = parse_url($_GET['redirect']);
    // If a malformed URL is given, don't redirect.
    if ($redirect_parsed_url === false) {
        return false;
    }
    // Just the path portion of the URL was given.
    if (!isset($redirect_parsed_url['scheme']) && !isset($redirect_parsed_url['host'])) {
        return true;
    }
    $redirect_url = $redirect_parsed_url['scheme'] . '://' . $redirect_parsed_url['host'];
    if (isset($redirect_parsed_url['port'])) {
        $redirect_url .= ':' . $redirect_parsed_url['port'];
    }
    return $redirect_url == OMEGAUP_URL;
}

if ($c_Session->currentSessionAvailable()) {
    if (!empty($_GET['redirect']) && shouldRedirect($_GET['redirect'])) {
        die(header('Location: ' . $_GET['redirect']));
    }
    die(header('Location: /profile/'));
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
$smarty->assign('FB_URL', \OmegaUp\Controllers\Session::getFacebookLoginUrl());
$smarty->assign('LINKEDIN_URL', \OmegaUp\Controllers\Session::getLinkedInLoginUrl());
$smarty->assign('VALIDATE_RECAPTCHA', OMEGAUP_VALIDATE_CAPTCHA);
$smarty->assign('payload', ['validateRecaptcha' => OMEGAUP_VALIDATE_CAPTCHA]);
$smarty->display('../templates/login.tpl');
