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
	 * @param Request $r
	 * @return array
	 */
	public static function apiGet(Request $r = null) {
		$response = array();
		$response['time'] = time();
		$response['status'] = 'ok';

		return $response;
	}
}
