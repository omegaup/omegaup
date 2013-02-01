<?php

/**
 * Controllers parent class
 *
 * @author joemmanuel
 */
class Controller {
	
	/**
	 * Given the request, returns what user is performing the request by
	 * looking at the auth_token
	 * 
	 * @param Request $r
	 * @throws InvalidDatabaseOperationException
	 * @throws ForbiddenAccessException
	 */
	protected static function authenticateRequest(Request $r) {
		
		Validators::isStringNonEmpty($r["auth_token"], "auth_token");
		
		try {
			$user = AuthTokensDAO::getUserByToken($r["auth_token"]);
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}
	Logger::log($user)	;
		if (is_null($user)) {
			throw new ForbiddenAccessException();
		}
		
		$r["current_user"] = $user;
		$r["current_user_id"] = $user->getUserId();
	}
	
}

