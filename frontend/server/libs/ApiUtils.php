<?php


class ApiUtils 
{
    public static function GetRandomString()
    {
        md5(uniqid(rand(), true));
    }
}

?>
