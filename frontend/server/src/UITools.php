<?php

namespace OmegaUp;

class UITools {
    /** @var bool */
    public static $isLoggedIn = false;
    /** @var bool */
    public static $isAdmin = false;

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
}
