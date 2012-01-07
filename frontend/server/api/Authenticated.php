<?php

/**
 * 
 * 
 * 
 *
 *
 * 
  *
 * */
require_once("ApiHandler.php");

require_once(SERVER_PATH ."/libs/ApiException.php");

class Authenticated extends ApiHandler {
	private $authenticated = true;

	protected function CheckAuthToken()
	{
		try {
			parent::CheckAuthToken();
		} catch (ApiException $e) {
			$this->authenticated = false;
		}
	}

	protected function RegisterValidatorsToRequest() 
	{								
	}

	protected function GenerateResponse() 
	{
		if (!$this->authenticated) {
			$this->addResponse('login_url', '/nativeLogin.php');
			$this->addResponse('status', 'error');
			$this->addResponse('error', 'Not authenticated');
		}
	}
}

?>
