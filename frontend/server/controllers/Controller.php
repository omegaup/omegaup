<?php

/**
 * Controllers parent class
 *
 * @author joemmanuel
 */
class Controller {

	// If we turn this into protected,
	// how are we going to initialize?
	public static $log;

	/**
	 * List of veredicts
	 * 
	 * @var array 
	 */
	public static $veredicts = array("AC", "PA", "WA", "TLE", "MLE", "OLE", "RTE", "RFE", "CE", "JE", "NO-AC");
	
	/**
	 * Given the request, returns what user is performing the request by
	 * looking at the auth_token
	 * 
	 * @param Request $r
	 * @throws InvalidDatabaseOperationException
	 * @throws ForbiddenAccessException
	 */
	protected static function authenticateRequest(Request $r) {
		
		try {
			Validators::isStringNonEmpty($r["auth_token"], "auth_token");
		} catch(Exception $e) {
			throw new ForbiddenAccessException();
		}
		
		try {
			$user = AuthTokensDAO::getUserByToken($r["auth_token"]);
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}
	
		if (is_null($user)) {
			throw new ForbiddenAccessException();
		}
		
		$r["current_user"] = $user;
		$r["current_user_id"] = $user->getUserId();
	}
	
	/**
	 * Resolves the target user for the API. If a username is provided in
	 * the request, then we use that one. Otherwise, we use currently logged-in
	 * user.
	 * 
	 * Request must be authenticated before this function is called.
	 * 
	 * @param Request $r
	 * @return Users
	 * @throws InvalidDatabaseOperationException
	 * @throws NotFoundException
	 */
	protected static function resolveTargetUser(Request $r) {
		
		// By default use current user		
		$user = $r["current_user"];	 
		
		if (!is_null($r["username"])) {
			
			Validators::isStringNonEmpty($r["username"], "username");
			
			try {
				$user = UsersDAO::FindByUsername($r["username"]);

				if (is_null($user)) {
					throw new NotFoundException("User does not exist");
				}
			} 
			catch (ApiException $e) {
				throw $e;
			}
			catch (Exception $e) {
				throw new InvalidDatabaseOperationException($e);
			}			
		}
		
		return $user;
	}
	
	/**
	 * Retunrs a random string of size $length
	 * 
	 * @param string $length
	 * @return string
	 */
	public static function randomString($length) {
		$chars = "abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789";
		$str = "";
		$size = strlen($chars);
		for ($i = 0; $i < $length; $i++) {
			$str .= $chars[rand(0, $size - 1)];
		}

		return $str;
	}

	/**
	 *
	 * @param Request $request
	 * @param object $object
	 * @param array $properties
	 * @return boolean
	 */
	protected static function updateValueProperties($request, $object, $properties) {
		$importantChange = false;
		foreach ($properties as $source => $info) {
			if (is_int($source)) {
				// Simple property:
				$source = $info;
				$info = [preg_replace_callback(
					'|_(\w)|',
					function($matches) { return ucfirst($matches[1]); },
					ucfirst($source))
				];
			}
			if (is_null($request[$source])) {
				continue;
			}
			// Get or calculate new value.
			$value = $request[$source];
			if (count($info) >= 3) {
				$transform = $info[2];
				$value = $transform($value);
			}
			// Important property, so check if it changes.
			if (count($info) >= 2 && $info[1]) {
				$getter = "get" . $info[0];
				if ($value != $object->$getter()) {
					$importantChange = true;
				}
			}
			$setter = "set" . $info[0];
			$object->$setter($value);
		}
		return $importantChange;
	}
}

Controller::$log = Logger::getLogger("controller");

