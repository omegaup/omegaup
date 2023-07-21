<?php

 namespace OmegaUp\Controllers;

/**
 * TimeController
 *
 * Used by arena to sync time between client and server from time to time
 */
class Time extends \OmegaUp\Controllers\Controller {
    /**
     * Entry point for /time API
     *
     * @return array{time: int}
     */
    public static function apiGet(?\OmegaUp\Request $r = null): array {
        return [
            'time' => \OmegaUp\Time::get(),
        ];
    }
}
