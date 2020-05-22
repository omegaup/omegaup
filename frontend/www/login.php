<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 1) . '/server/bootstrap.php');

$triedToLogin = false;
$emailVerified = true;
/*
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
            $e
        );
        /** @var array<string, mixed>
        $response = $apiException->asResponseArray();
    }

    $triedToLogin = true;
} /*elseif (
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
*/
if (isset($_GET['linkedin'])) {
    if (isset($_GET['code']) && isset($_GET['state'])) {
        /** @var array<string, mixed> */
        $response = \OmegaUp\Controllers\Session::LoginViaLinkedIn();
        shouldRedirect($_GET['redirect']);
    }
} elseif (isset($_GET['fb'])) {
    /** @var array<string, mixed> */
    $response = \OmegaUp\Controllers\Session::LoginViaFacebook();
    shouldRedirect($_GET['redirect']);
}

function shouldRedirect(string $url): bool {
    print($url);
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
        return true;
    }
    $redirect_url = "{$redirectParsedUrl['scheme']}://{$redirectParsedUrl['host']}";
    if (isset($redirectParsedUrl['port'])) {
        $redirect_url .= ":{$redirectParsedUrl['port']}";
    }
    return $redirect_url == OMEGAUP_URL;
}

/*if (\OmegaUp\Controllers\Session::currentSessionAvailable()) {
    if (
        !empty($_GET['redirect']) &&
        is_string($_GET['redirect']) &&
        shouldRedirect($_GET['redirect'])
    ) {
        die(header("Location: {$_GET['redirect']}"));
    }
    die(header('Location: /profile/'));
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
}*/

\OmegaUp\UITools::render(
    function (\OmegaUp\Request $r): array {
        return [
            'smartyProperties' => [
                'payload' => [
                    'validateRecaptcha' => OMEGAUP_VALIDATE_CAPTCHA,
                    'facebookURL' => \OmegaUp\Controllers\Session::getFacebookLoginUrl(),
                    'linkedinURL' => \OmegaUp\Controllers\Session::getLinkedInLoginUrl(),
                    ],
            ],
            'template' => '../templates/login.tpl',
        ];
    }
);
