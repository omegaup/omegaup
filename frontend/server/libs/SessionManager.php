<?php
class SessionManager {
	public function setCookie($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null) {
		$httponly = true;
		$domain = OMEGAUP_COOKIE_DOMAIN;
		if (!empty($_SERVER['HTTPS'])) {
			$secure = true;
		}
		$_COOKIE[$name] = $value;
		setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
	}

	public function getCookie($name) {
		if (array_key_exists($name, $_COOKIE))
		{
			return $_COOKIE[$name];
		}

		return NULL;
	}

	public function sessionStart() {
		@session_start();
	}
}
