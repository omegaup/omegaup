<?php

require_once('SecurityTools.php');

function randomString($length) {
    $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $str = '';
    $size = strlen($chars);
    for ($i = 0; $i < $length; $i++) {
        $str .= $chars[rand(0, $size - 1)];
    }

    return $str;
}

$password = randomString(8);
echo "$password\n";
echo SecurityTools::hashString($password) . "\n";
