<?php

/**
 * RequestContext
 * 
 * Wrapper for $_REQUEST context arrays
 */
class RequestContext{
	public static $params = array();
	
        /**
         * Sets a value in $_REQUEST
         * 
         * @param string $key
         * @param mixed $value
         */
	public static function set($key, $value){
            self::$params[$key] = $value;
	}
	
        /**
         * Returns the value for $_REQUEST
         * 
         * @param string $key
         * @return type, null when does not exists
         */
	public static function get($key){
            if ($key == 'auth_token'){
                    // only 'auth_token' can searched for in cookies
                    if (isset($_COOKIE[$key])) {
                            setcookie('auth_token', $_COOKIE[$key], time()+60*60*24, '/');
                            return $_COOKIE[$key];
                    }
            }

            return isset(self::$params[$key]) ? self::$params[$key] : null;
	}
}

// Set params referencing to $_REQUEST
RequestContext::$params = &$_REQUEST;
