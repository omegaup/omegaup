<?php

/**
 *
 * @author juan.pablo
 */
class Time {
    private static $time = null;

    public static function get() {
        if (self::$time != null) {
            return self::$time;
        }
        return time();
    }

    public static function setTimeForTesting($time) {
        self::$time = $time;
    }
}
