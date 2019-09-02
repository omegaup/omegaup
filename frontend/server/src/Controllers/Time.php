<?php

 namespace OmegaUp\Controllers;

/**
 * TimeController
 *
 * Used by arena to sync time between client and server from time to time
 *
 * @author joemmanuel
 */
class Time extends \OmegaUp\Controllers\Controller {
    /**
     * Entry point for /time API
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiGet(\OmegaUp\Request $r = null) {
        $response = [];
        $response['time'] = \OmegaUp\Time::get();
        $response['status'] = 'ok';

        return $response;
    }
}
