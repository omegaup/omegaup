<?php

namespace OmegaUp;

// An RAII wrapper to manage the lifetime of a session.
class ScopedSession {
    /**
     * @const
     * @var bool
     */
    private $_sessionStarted = false;

    public function __construct() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            // In case of nested sessions, we just let the
            // outermost one manage the whole session.
            return;
        }
        session_start();
        $this->_sessionStarted = true;
    }

    public function __destruct() {
        if (!$this->_sessionStarted) {
            // This instance did not start a session, so it
            // should not close it either.
            return;
        }
        session_write_close();
    }
}

class SessionManager {
    public function setCookie(
        string $name,
        string $value,
        int $expire,
        string $path
    ): void {
        // Expire all old cookies
        $httpCookie = \OmegaUp\Request::getServerVar('HTTP_COOKIE');
        if (!empty($httpCookie)) {
            $cookies = explode(';', $httpCookie);
            foreach ($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $oldName = trim($parts[0]);
                setcookie($oldName, '', \OmegaUp\Time::get() - 1000);
                setcookie($oldName, '', \OmegaUp\Time::get() - 1000, '/');
            }
        }

        // Set the new one
        $domain = OMEGAUP_COOKIE_DOMAIN;
        $secure = !empty(\OmegaUp\Request::getServerVar('HTTPS'));
        $_COOKIE[$name] = $value;
        if (PHP_VERSION_ID < 70300) {
            setcookie(
                $name,
                $value,
                $expire,
                "{$path}; SameSite=Lax",  // This hack only works for PHP < 7.3.
                $domain,
                /*secure=*/$secure,
                /*httponly=*/true
            );
        } elseif (PHP_VERSION_ID < 70400) {
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
                /*secure=*/$secure,
                /*httponly=*/true,
                /*samesite=*/'Lax'
            );
        } else {
            setcookie(
                $name,
                $value,
                [
                    'expires' => $expire,
                    'path' => $path,
                    'domain' => $domain,
                    'secure' => $secure,
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]
            );
        }
    }

    public function getCookie(string $name): ?string {
        if (!array_key_exists($name, $_COOKIE)) {
            return null;
        }

        return strval($_COOKIE[$name]);
    }

    public function sessionStart(): ScopedSession {
        return new ScopedSession();
    }
}
