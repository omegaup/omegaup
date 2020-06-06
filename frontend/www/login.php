<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 1) . '/server/bootstrap.php');

$triedToLogin = false;
$emailVerified = true;

if (isset($_GET['linkedin'])) {
    if (isset($_GET['code']) && isset($_GET['state'])) {
        /** @var array<string, mixed> */
        $response = \OmegaUp\Controllers\Session::LoginViaLinkedIn();
        //shouldRedirect(strval($_GET['redirect']));
    }
} elseif (isset($_GET['fb'])) {
    /** @var array<string, mixed> */
    $response = \OmegaUp\Controllers\Session::LoginViaFacebook();
    //shouldRedirect(strval($_GET['redirect']));
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

if (\OmegaUp\Controllers\Session::currentSessionAvailable()) {
    if (
        !empty($_GET['redirect']) &&
        is_string($_GET['redirect']) &&
        shouldRedirect($_GET['redirect'])
    ) {
        die(header("Location: {$_GET['redirect']}"));
    }
    die(header('Location: /profile/'));
}

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
