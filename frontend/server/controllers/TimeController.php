<?php

/**
 * TimeController
 *
 * Used by arena to sync time between client and server from time to time
 *
 * @author joemmanuel
 */
class TimeController extends Controller {
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
