<?php

/**
 *  UserController
 *
 * @author joemmanuel
 */
require_once 'SessionController.php';

class UserController extends Controller {

	/**
	 * Entry point for Create a User API
	 * 
	 * @param Request $r
	 * @return array
	 * @throws InvalidDatabaseOperationException
	 * @throws DuplicatedEntryInDatabaseException
	 */
	public static function apiCreate(Request $r) {

		// Validate request
		Validators::isStringOfMinLength($r["username"], "username", 2);
		Validators::isEmail($r["email"], "email");

		// Check password
		SecurityTools::testStrongPassword($r["password"]);

		// Does user or email already exists?
		try {
			$user = UsersDAO::FindByUsername($r["username"]);
			$userByEmail = UsersDAO::FindByEmail($r["email"]);
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		if (!is_null($userByEmail)) {
			throw new DuplicatedEntryInDatabaseException("email already exists");
		}

		if (!is_null($user)) {
			throw new DuplicatedEntryInDatabaseException("Username already exists.");
		}

		// Prepare DAOs
		$user = new Users(array(
					"username" => $r["username"],
					"password" => SecurityTools::hashString($r["password"]),
					"solved" => 0,
					"submissions" => 0,
				));

		$email = new Emails(array(
					"email" => $r["email"],
				));

		// Save objects into DB
		try {
			DAO::transBegin();

			UsersDAO::save($user);

			$email->setUserId($user->getUserId());
			EmailsDAO::save($email);

			$user->setMainEmailId($email->getEmailId());
			UsersDAO::save($user);

			DAO::transEnd();
		} catch (Exception $e) {
			DAO::transRollback();
			throw new InvalidDatabaseOperationException($e);
		}

		Logger::log("User " . $user->getUsername() . " created");

		return array(
			"status" => "ok",
			"user_id" => $user->getUserId()
		);
	}

	/**
	 *
	 * Description:
	 *     Tests a if a password is valid for a given user.
	 *
	 * @param user_id
	 * @param email
	 * @param username
	 * @param password
	 *
	 * */
	public function TestPassword(Request $r) {
		$user_id = $email = $username = $password = null;

		if (isset($r["user_id"])) {
			$user_id = $r["user_id"];
		}

		if (isset($r["email"])) {
			$email = $r["email"];
		}

		if (isset($r["username"])) {
			$username = $r["username"];
		}

		if (isset($r["password"])) {
			$password = $r["password"];
		}


		if (is_null($user_id) && is_null($email) && is_null($username)) {
			throw new Exception("You must provide either one of the following: user_id, email or username");
		}

		$vo_UserToTest = null;

		//find this user
		if (!is_null($user_id)) {
			$vo_UserToTest = UsersDAO::getByPK($user_id);
		} else if (!is_null($email)) {
			$vo_UserToTest = $this->FindByEmail();
		} else {
			$vo_UserToTest = $this->FindByUserName();
		}

		if (is_null($vo_UserToTest)) {
			//user does not even exist
			return false;
		}

		$newPasswordCheck = SecurityTools::compareHashedStrings(
						$password, $vo_UserToTest->getPassword());

		// We are OK
		if ($newPasswordCheck === true) {
			return true;
		}

		// It might be an old password
		if (strcmp($vo_UserToTest->getPassword(), md5($password)) === 0) {
			try {
				// It is an old password, need to update
				$vo_UserToTest->setPassword(SecurityTools::hashString($password));
				UsersDAO::save($vo_UserToTest);
			} catch (Exception $e) {
				// We did our best effort, log that user update failed
				Logger::warn("Failed to update user password!!");
			}

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Exposes API /user/login
	 * Expects in request:
	 * user
	 * password 
	 *
	 * 
	 * @param Request $r
	 */
	public static function apiLogin(Request $r) {

		// Create a SessionController to perform login
		$sessionController = new SessionController();

		// Require the auth_token back
		$r["returnAuthToken"] = true;

		// Get auth_token
		$auth_token = $sessionController->NativeLogin($r);

		// If user was correctly logged in
		if ($auth_token !== false) {
			return array(
				"status" => "ok",
				"auth_token" => $auth_token);
		} else {
			throw new InvalidCredentialsException();
		}
	}

	/**
	 * Resets the password of a user
	 * 
	 * @param Request $rﬁ
	 * @return array
	 * @throws ForbiddenAccessException
	 */
	public static function apiResetPassword(Request $r) {

		self::authenticateRequest($r);

		Validators::isStringNonEmpty($r["username"], "username");
		SecurityTools::testStrongPassword($r["password"]);

		if (!Authorization::IsSystemAdmin($r["current_user_id"])) {

			$user = $r["current_user"];

			// Check the old password
			Validators::isStringNonEmpty($r["old_password"], "old_password");

			$old_password_valid = SecurityTools::compareHashedStrings(
							$r["old_password"], $user->getPassword());

			if ($old_password_valid === false) {
				throw new InvalidParameterException("old_password" . Validators::IS_INVALID);
			}
		} else {
			// System admin can force reset passwords 
			try {
				$user = UsersDAO::FindByUsername($r["username"]);

				if (is_null($user)) {
					throw NotFoundException();
				}
			} catch (Exception $e) {
				throw new InvalidDatabaseOperationException($e);
			}
		}

		$user->setPassword(SecurityTools::hashString($r["password"]));
		UsersDAO::save($user);

		return array("status" => "ok");
	}

	/**
	 * Given a username or a email, returns the user object
	 * 
	 * @param type $userOrEmail
	 * @return User
	 * @throws ApiException
	 * @throws InvalidDatabaseOperationException
	 * @throws InvalidParameterException
	 */
	public static function resolveUser($userOrEmail) {

		Validators::isStringNonEmpty($userOrEmail, "Username or email not found");

		$user = null;

		try {
			if (!is_null($user = UsersDAO::FindByEmail($userOrEmail))
					|| !is_null($user = UsersDAO::FindByUsername($userOrEmail))) {
				return $user;
			} else {
				throw new NotFoundException("Username or email not found");
			}
		} catch (ApiException $apiException) {
			throw $apiException;
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		return $user;
	}

	/**
	 * Retunrs a random string of size $length
	 * 
	 * @param string $length
	 * @return string
	 */
	private static function randomString($length) {
		$chars = "abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789";

		$size = strlen($chars);
		for ($i = 0; $i < $length; $i++) {
			$str .= $chars[rand(0, $size - 1)];
		}

		return $str;
	}

	/**
	 * Resets the password of the OMI user and adds the user to the private 
	 * contest.
	 * If the user does not exists, we create him.
	 * 
	 * @param Request $r
	 * @param string $username
	 * @param string $password
	 */
	private static function omiPrepareUser(Request $r, $username, $password) {

		try {
			$user = UsersDAO::FindByUsername($username);
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		if (is_null($user)) {
			Logger::log("Creating user: " . $username);
			$createRequest = new Request(array(
						"username" => $username,
						"password" => $password,
						"email" => $username . "@omi.com",
					));
			self::apiCreate($createRequest);
		} else {
			$resetRequest = new Request();
			$resetRequest["auth_token"] = $r["auth_token"];
			$resetRequest["username"] = $username;
			$resetRequest["password"] = $password;
			self::apiResetPassword($resetRequest);
		}

		if (!is_null($r["contest_alias"])) {
			$addUserRequest = new Request();
			$addUserRequest["auth_token"] = $r["auth_token"];
			$addUserRequest["usernameOrEmail"] = $username;
			$addUserRequest["contest_alias"] = $r["contest_alias"];
			ContestController::apiAddUser($addUserRequest);
		}
	}

	/**
	 * 
	 * @param Request $r
	 * @return array
	 * @throws ForbiddenAccessException
	 */
	public static function apiGenerateOmiUsers(Request $r) {

		self::authenticateRequest($r);

		if (!Authorization::IsSystemAdmin($r["current_user_id"])) {
			throw new ForbiddenAccessException();
		}

		$response = array();

		// Arreglo de estados de MX
		$keys = array(
			"AGU",
			"BCN",
			"BCS",
			"CAM",
			"COA",
			"COL",
			"CHP",
			"CHH",
			"DIF",
			"DUR",
			"GUA",
			"GRO",
			"HID",
			"JAL",
			"MEX",
			"MIC",
			"MOR",
			"NAY",
			"NLE",
			"OAX",
			"PUE",
			"QUE",
			"ROO",
			"SLP",
			"SIN",
			"SON",
			"TAB",
			"TAM",
			"TLA",
			"VER",
			"YUC",
			"ZAC"
		);


		foreach ($keys as $k) {
			$n = 4;
			// El estado sede tiene 4 usuarios más
			if ($k == "MEX") {
				$n = 8;
			}
			for ($i = 1; $i <= $n; $i++) {

				$username = $k . "-" . $i;
				$password = self::randomString(8);

				self::omiPrepareUser($r, $username, $password);
				$response[$username] = $password;
				// @TODO add to private contest
			}
		}

		return $response;
	}
	
	/**
	 * Get list of contests where the user has admin priviledges
	 * 
	 * @param Request $r
	 * @return string
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiContests(Request $r) {
		
		self::authenticateRequest($r);
		
		$response = array();
		
		try {
			
			$contest_director_key = new Contests(array(
				"director_id" => $r["current_user_id"]
			));			
			$contests_director = ContestsDAO::search($contest_director_key);
			
			foreach($contests_director as $contest) {
				$response["contests"][] = $contest->asArray();
			}
			
			$contest_admin_key = new UserRoles(array(
				"user_id" => $r["current_user_id"],
				"role_id" => CONTEST_ADMIN_ROLE,
			));			
			$contests_admin = UserRolesDAO::search($contest_admin_key);
			
			foreach($contests_admin as $contest_key) {
				$contest = ContestsDAO::getByPK($contest_key->getContestId());	
				
				if (is_null($contest)) {
					Logger::error("UserRoles has a invalid contest: {$contest->getContestId()}");
					continue;
				}
				
				$response["contests"][] = $contest->asArray();
			}
			
			usort($response["contests"], function ($a, $b) {
				return ($a["contest_id"] > $b["contest_id"]) ? -1 : 1;
			});
			
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}
		
		$response["status"] = "ok";
		return $response;		
	}

}

