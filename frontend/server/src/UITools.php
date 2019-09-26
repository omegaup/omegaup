<?php

namespace OmegaUp;

class UITools {
    /** @var bool */
    public static $isLoggedIn = false;
    /** @var bool */
    public static $isAdmin = false;
    /** @var string[] */
    public static $contestPages = [
        'arena/admin.php',
        'arena/contest.php',
        'schools/course.php',
        'arena/contest.php',
        'arena/courseadmin.php',
    ];

    /**
     * If user is not logged in, redirect to login page
     */
    public static function redirectToLoginIfNotLoggedIn() : void {
        if (\OmegaUp\UITools::$isLoggedIn === false) {
            header('Location: /login.php?redirect=' . urlencode(strval($_SERVER['REQUEST_URI'])));
            die();
        }
    }

    /**
     * If user is not logged in or isn't an admin, redirect to home page
     */
    public static function redirectIfNoAdmin() : void {
        if (\OmegaUp\UITools::$isAdmin !== true) {
            header('Location: /');
            die();
        }
    }

    public static function getFormattedGravatarURL(string $hashedEmail, string $size) : string {
        return "https://secure.gravatar.com/avatar/{$hashedEmail}?s={$size}";
    }

    public static function getSmartyNavbarHeader(
        $smarty,
        array $session,
        string $navbarSection,
        bool $inContest
    ) : void {
        $smarty->assign(
            'headerPayload',
            [
                'omegaUpLockDown' => OMEGAUP_LOCKDOWN,
                'inContest' => $inContest,
                'isLoggedIn' => \OmegaUp\UITools::$isLoggedIn,
                'isReviewer' => is_null($session['identity']) ? false :
                  \OmegaUp\Authorization::isQualityReviewer($session['identity']),
                'gravatarURL51' => is_null($session['email']) ? '' :
                  \OmegaUp\UITools::getFormattedGravatarURL(md5($session['email']), '51'),
                'currentUsername' => is_null($session['identity']) ? '' :
                  $session['identity']->username,
                'isAdmin' => \OmegaUp\UITools::$isAdmin,
                'lockDownImage' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAA6UlEQVQ4jd2TMYoCMRiFv5HBwnJBsFqEiGxtISps6RGmFD2CZRr7aQSPIFjmCGsnrFYeQJjGytJKRERsfp2QmahY+iDk5c97L/wJCchBFCclYAD8SmkBTI1WB1cb5Ji/gT+g7mxtgK7RausNiOIEYAm0pHSWOZR5BbSNVndPwTmlaZnnQFnGXGot0XgDfiw+NlrtjVZ7YOzRZAJCix893NZkAi4eYejRpJcYxckQ6AENKf0DO+EVoCN8DcyMVhM3eQR8WesO+WgAVWDituC28wiFDHkXHxBgv0IfKL7oO+UF1Ei/7zMsbuQKTFoqpb8KS2AAAAAASUVORK5CYII=',
                'navbarSection' => $navbarSection,
            ]
        );
    }
}
