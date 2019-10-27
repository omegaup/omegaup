<?php

namespace OmegaUp;

class SessionManager {
    public function setCookie(
        string $name,
        string $value,
        int $expire,
        string $path
    ): void {
        // Expire all old cookies
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $cookies = explode(';', strval($_SERVER['HTTP_COOKIE']));
            foreach ($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $oldName = trim($parts[0]);
                setcookie($oldName, '', \OmegaUp\Time::get() - 1000);
                setcookie($oldName, '', \OmegaUp\Time::get() - 1000, '/');
            }
        }

        // Set the new one
        $domain = OMEGAUP_COOKIE_DOMAIN;
        $_COOKIE[$name] = $value;
        if (PHP_VERSION_ID < 70300) {
            setcookie(
                $name,
                $value,
                $expire,
                "{$path}; SameSite=Lax",  // This hack only works for PHP < 7.3.
                $domain,
                /*secure=*/!empty($_SERVER['HTTPS']),
                /*httponly=*/true
            );
        } else {
            /**
             * @psalm-suppress TooManyArguments this is needed to support
             *                                  Same-Site cookies.
             */
            setcookie(
                $name,
                $value,
                $expire,
                $path,
                $domain,
                /*secure=*/!empty($_SERVER['HTTPS']),
                /*httponly=*/true,
                /*samesite=*/'Lax'
            );
        }
    }

    public function getCookie(string $name): ?string {
        if (!array_key_exists($name, $_COOKIE)) {
            return null;
        }

        return strval($_COOKIE[$name]);
    }

    public function sessionStart(): void {
        if (session_status() == PHP_SESSION_ACTIVE) {
            return;
        }
        @session_start();
    }
}
