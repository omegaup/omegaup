<?php

namespace OmegaUp;

class UITools {
    /**
     * If user is not logged in, redirect to login page
     */
    public static function redirectToLoginIfNotLoggedIn(): void {
        if (
            !is_null(
                \OmegaUp\Controllers\Session::getCurrentSession()['identity']
            )
        ) {
            return;
        }
        header(
            'Location: /login.php?redirect=' . urlencode(
                strval(
                    $_SERVER['REQUEST_URI']
                )
            )
        );
        die();
    }

    /**
     * If user is not logged in or isn't an admin, redirect to home page
     */
    public static function redirectIfNoAdmin(): void {
        if (\OmegaUp\Controllers\Session::getCurrentSession()['is_admin']) {
            return;
        }
        header('Location: /');
        die();
    }
}
