<?php
class SessionManager {
    public function setCookie($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null) {
        // Expire all old cookies
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach ($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $oldName = trim($parts[0]);
                setcookie($oldName, '', \OmegaUp\Time::get() - 1000);
                setcookie($oldName, '', \OmegaUp\Time::get() - 1000, '/');
            }
        }

        // Set the new one
        $httponly = true;
        $domain = OMEGAUP_COOKIE_DOMAIN;
        if (!empty($_SERVER['HTTPS'])) {
            $secure = true;
        }
        $_COOKIE[$name] = $value;
        setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    public function getCookie($name) {
        if (array_key_exists($name, $_COOKIE)) {
            return $_COOKIE[$name];
        }

        return null;
    }

    public function sessionStart() {
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }
    }
}
