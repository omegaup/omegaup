<?php

namespace OmegaUp;

class Time {
    /** @var int|null */
    private static $time = null;

    public static function get(): int {
        if (self::$time !== null) {
            return self::$time;
        }
        return time();
    }

    /**
     * @param int|null $time
     */
    public static function setTimeForTesting(?int $time): void {
        self::$time = $time;
    }
}
