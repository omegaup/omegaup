<?php
require_once('../server/bootstrap_smarty.php');

$triedToLogin = false;
$emailVerified = true;

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
        \Logger::getLogger('login')->error($e);
        $apiException = new \OmegaUp\Exceptions\InternalServerErrorException(
            'generalError',
            $e
        );
        /** @var array<string, mixed> */
        $response = $apiException->asResponseArray();
    }

    $triedToLogin = true;
} elseif (
    OMEGAUP_VALIDATE_CAPTCHA &&
    isset($_POST['request']) &&
    $_POST['request'] == 'register'
) {
    // Something failed in the JavaScript side. This definitely will not have
    // ReCAPTCHA validation, so let's error out with that.
    $smarty->assign('ERROR_TO_USER', 'NATIVE_LOGIN_FAILED');
    $smarty->assign(
        'ERROR_MESSAGE',
        \OmegaUp\Translations::getInstance()->get(
            'unableToVerifyCaptcha'
        )
    );
}

if (isset($_GET['linkedin'])) {
    if (isset($_GET['code']) && isset($_GET['state'])) {
        /** @var array<string, mixed> */
        $response = \OmegaUp\Controllers\Session::LoginViaLinkedInDeprecated();
    }
    $triedToLogin = true;
} elseif (isset($_GET['fb'])) {
    /** @var array<string, mixed> */
    $response = \OmegaUp\Controllers\Session::LoginViaFacebookDeprecated();
    $triedToLogin = true;
}

if (isset($_GET['shva'])) {
    $triedToLogin = true;
}

function shouldRedirect(string $url): bool {
    $redirectParsedUrl = parse_url($url);
    // If a malformed URL is given, don't redirect.
    if ($redirectParsedUrl === false) {
        return false;
    }
    // Just the path portion of the URL was given.
    if (
        empty($redirectParsedUrl['scheme']) ||
        empty($redirectParsedUrl['host'])
    ) {
        return ($redirectParsedUrl['path'] ?? '') != '/logout/';
    }
    $redirect_url = "{$redirectParsedUrl['scheme']}://{$redirectParsedUrl['host']}";
    if (isset($redirectParsedUrl['port'])) {
        $redirect_url .= ":{$redirectParsedUrl['port']}";
    }
    return $redirect_url == OMEGAUP_URL;
}

if (\OmegaUp\Controllers\Session::currentSessionAvailable()) {
    if (
        !empty($_GET['redirect']) &&
        is_string($_GET['redirect']) &&
        shouldRedirect($_GET['redirect'])
    ) {
        header("Location: {$_GET['redirect']}");
        die();
    }
    header('Location: /profile/');
    die();
} elseif ($triedToLogin) {
    if (isset($response['error'])) {
        $smarty->assign('ERROR_TO_USER', 'NATIVE_LOGIN_FAILED');
        $smarty->assign('ERROR_MESSAGE', $response['error']);
    } else {
        $smarty->assign('ERROR_TO_USER', 'THIRD_PARTY_LOGIN_FAILED');
        $smarty->assign(
            'ERROR_MESSAGE',
            \OmegaUp\Translations::getInstance()->get(
                'loginFederatedFailed'
            )
        );
    }
}

// Only generate Login URLs if we actually need them.
$smarty->assign('FB_URL', \OmegaUp\Controllers\Session::getFacebookLoginUrl());
$smarty->assign(
    'LINKEDIN_URL',
    \OmegaUp\Controllers\Session::getLinkedInLoginUrl()
);
$smarty->assign('VALIDATE_RECAPTCHA', OMEGAUP_VALIDATE_CAPTCHA);
$smarty->assign('payload', ['validateRecaptcha' => OMEGAUP_VALIDATE_CAPTCHA]);
$smarty->display('../templates/login.tpl');
