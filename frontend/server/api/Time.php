<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 * Gets the server time, in seconds from Unix epoch.
 *
 * */
require_once("ApiHandler.php");


class Time extends ApiHandler {
	protected function CheckAuthToken() 
	{
	}

	protected function RegisterValidatorsToRequest() 
	{
	}

	protected function GenerateResponse() {
		$this->addResponse('time', time());
	}
}
