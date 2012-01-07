<?php

class SessionManager 
{
    public function SetCookie($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }
}

?>
