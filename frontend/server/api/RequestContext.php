<?php
class RequestContext
{
	public static $params = array();
	
	public static function set($key, $value)
	{
		self::$params[$key] = $value;
	}
	
	public static function get($key)
	{
		if ($key == 'auth_token') {
			// only 'auth_token' can searched for in cookies
			if (isset($_COOKIE[$key])) {
				setcookie('auth_token', $_COOKIE[$key], time()+60*60*24);
				return $_COOKIE[$key];
			}
		}

		return isset(self::$params[$key]) ? self::$params[$key] : null;
	}
}

// Set params referencing to $_REQUEST
RequestContext::$params = &$_REQUEST;
