<?php

/**
 *  UserController
 *
 * @author joemmanuel
 */
require_once 'SessionController.php';

class UserController extends Controller {

	public static $sendEmailOnVerify = true;
	public static $redirectOnVerify = true;

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
					"verified" => 0,
					"verification_id" => self::randomString(50),
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

		Logger::log("User " . $user->getUsername() . " created, sending verification mail");

		$r["user"] = $user;
		self::sendVerificationEmail($r);

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
	 * Send the mail with verification link to the user in the Request
	 * 
	 * @param Request $r
	 * @throws InvalidDatabaseOperationException
	 * @throws EmailVerificationSendException
	 */
	private static function sendVerificationEmail(Request $r) {
		if (!OMEGAUP_EMAIL_SEND_EMAILS) return;

		try {
			$email = EmailsDAO::getByPK($r["user"]->getMainEmailId());
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		Logger::log("Sending email to user.");
		if (self::$sendEmailOnVerify) {
			$mail = new PHPMailer();
			$mail->IsSMTP();
			$mail->Host = OMEGAUP_EMAIL_SMTP_HOST;
			$mail->SMTPAuth = true;
			$mail->Password = OMEGAUP_EMAIL_SMTP_PASSWORD;
			$mail->From = OMEGAUP_EMAIL_SMTP_FROM;
			$mail->Port = 465;
			$mail->SMTPSecure = 'ssl';
			$mail->Username = OMEGAUP_EMAIL_SMTP_FROM;

			$mail->FromName = OMEGAUP_EMAIL_SMTP_FROM;
			$mail->AddAddress($email->getEmail());
			$mail->isHTML(true);
			$mail->Subject = "Bienvenido a Omegaup!";
			$mail->Body = 'Bienvenido a Omegaup! Por favor ingresa a la siguiente dirección para hacer login y verificar tu email: <a href="https://omegaup.com/api/user/verifyemail/id/' . $r["user"]->getVerificationId() . '"> https://omegaup.com/api/user/verifyemail/id/' . $r["user"]->getVerificationId() . '</a>';

			if (!$mail->Send()) {
				Logger::error("Failed to send mail: " . $mail->ErrorInfo);
				throw new EmailVerificationSendException();
			}
		}
	}

	/**
	 * Check if email of user in request has been verified
	 * 
	 * @param Request $r
	 * @throws EmailNotVerifiedException
	 */
	public static function checkEmailVerification(Request $r) {

		if (OMEGAUP_FORCE_EMAIL_VERIFICATION) {
			// Check if he has been verified				
			if ($r["user"]->getVerified() == '0') {
				Logger::log("User not verified.");

				if ($r["user"]->getVerificationId() == null) {

					Logger::log("User does not have verification id. Generating.");

					try {
						$r["user"]->setVerificationId(self::randomString(50));
						UsersDAO::save($r["user"]);
					} catch (Exception $e) {
						// best effort, eat exception
					}

					self::sendVerificationEmail($r);
				}

				throw new EmailNotVerifiedException();
			} else {
				Logger::log("User already verified.");
			}
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
	 * Changes the password of a user
	 * 
	 * @param Request $rﬁ
	 * @return array
	 * @throws ForbiddenAccessException
	 */
	public static function apiChangePassword(Request $r) {

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
					throw new NotFoundException("User does not exists");
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
	 * Verifies the user given its verification id
	 * 
	 * @param Request $r
	 * @return type
	 * @throws ApiException
	 * @throws InvalidDatabaseOperationException
	 * @throws NotFoundException
	 */
	public static function apiVerifyEmail(Request $r) {

		Validators::isStringNonEmpty($r["id"], "id");

		try {
			$users = UsersDAO::search(new Users(array(
								"verification_id" => $r["id"]
							)));

			$user = $users[0];
			if (is_null($user)) {
				throw new NotFoundException("Verification id is invalid.");
			}

			$user->setVerified(1);
			$user->setVerificationId('');
			UsersDAO::save($user);
		} catch (ApiException $e) {
			throw $e;
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		Logger::log("User verification complete.");

		if (self::$redirectOnVerify) {
			die(header('Location: /login.php'));
		}
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
		$str = "";
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

			UserController::$sendEmailOnVerify = false;
			self::apiCreate($createRequest);
		} else {
			$resetRequest = new Request();
			$resetRequest["auth_token"] = $r["auth_token"];
			$resetRequest["username"] = $username;
			$resetRequest["password"] = $password;
			self::apiChangePassword($resetRequest);
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
		$response["contests"] = array();

		try {

			$contest_director_key = new Contests(array(
						"director_id" => $r["current_user_id"]
					));
			$contests_director = ContestsDAO::search($contest_director_key);

			foreach ($contests_director as $contest) {
				$response["contests"][] = $contest->asArray();
			}

			$contest_admin_key = new UserRoles(array(
						"user_id" => $r["current_user_id"],
						"role_id" => CONTEST_ADMIN_ROLE,
					));
			$contests_admin = UserRolesDAO::search($contest_admin_key);

			foreach ($contests_admin as $contest_key) {
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

	/**
	 * Get list of my editable problems
	 * 
	 * @param Request $r
	 * @return string
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiProblems(Request $r) {

		self::authenticateRequest($r);

		$response = array();
		$response["problems"] = array();

		try {
			$problems_key = new Problems(array(
						"author_id" => $r["current_user_id"]
					));

			$problems = ProblemsDAO::search($problems_key);

			foreach ($problems as $problem) {
				$response["problems"][] = $problem->asArray();
			}

			usort($response["problems"], function ($a, $b) {
						return ($a["problem_id"] > $b["problem_id"]) ? -1 : 1;
					});
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		$response["status"] = "ok";
		return $response;
	}
	
	/**
	 * Resolves the target user for the API. If a username is provided in
	 * the request, then we use that one. Otherwise, we use currently logged user
	 * 
	 * @param Request $r
	 * @return Users
	 * @throws InvalidDatabaseOperationException
	 * @throws NotFoundException
	 */
	private static function resolveTargetUser(Request $r) {
		
		// By default use current user		
		$user = $r["current_user"];	 
		
		if (!is_null($r["username"])) {
			
			Validators::isStringNonEmpty($r["username"], "username");
			
			try {
				$user = UsersDAO::FindByUsername($r["username"]);

				if (is_null($user)) {
					throw new NotFoundException("User does not exists");
				}
			} catch (Exception $e) {
				throw new InvalidDatabaseOperationException($e);
			}			
		}
		
		return $user;
	}

	/**
	 * Get general user info
	 * 
	 * @param Request $r
	 * @return response array with user info
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiProfile(Request $r) {

		self::authenticateRequest($r);
				
		$user = self::resolveTargetUser($r);

		$response = array();
		$response["userinfo"] = array();
		$response["problems"] = array();
		
		$response["userinfo"]["username"] = $user->getUsername();		
		$response["userinfo"]["name"] = $user->getName();
		$response["userinfo"]["solved"] = $user->getSolved();
		$response["userinfo"]["submissions"] = $user->getSubmissions();
		$response["userinfo"]["birth_date"] = is_null($user->getBirthDate()) ? null : strtotime($user->getBirthDate());
		$response["userinfo"]["graduation_date"] = is_null($user->getGraduationDate()) ? null : strtotime($user->getGraduationDate());
		$response["userinfo"]["scholar_degree"] = $user->getScholarDegree();

		try {
			$response["userinfo"]["email"] = EmailsDAO::getByPK($user->getMainEmailId())->getEmail();
			
			$country = CountriesDAO::getByPK($user->getCountryId());
			$response["userinfo"]["country"] = is_null($country) ? null : $country->getName();
			$response["userinfo"]["country_id"] = $user->getCountryId();
			
			$state = StatesDAO::getByPK($user->getStateId());
			$response["userinfo"]["state"] = is_null($state) ? null : $state->getName();
			$response["userinfo"]["state_id"] = $user->getStateId();
			
			$school = SchoolsDAO::getByPK($user->getSchoolId());
			$response["userinfo"]["school_id"] = $user->getSchoolId();
			$response["userinfo"]["school"] = is_null($school) ? null : $school->getName();
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}
		
		$response["userinfo"]["gravatar_92"] = 'https://secure.gravatar.com/avatar/' . md5($response["userinfo"]["email"]) . '?s=92';
		
		$response["status"] = "ok";
		return $response;
	}

	/**
	 * Get Contests which a certain user has participated in
	 * 
	 * @param Request $r
	 * @return Contests array
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiContestUsers(Request $r) {

		self::authenticateRequest($r);

		$response = array();
		$response["contests"] = array();

		$user = self::resolveTargetUser($r);
		
		$contest_user_key = new ContestsUsers();
		$contest_user_key->setUserId($user->getUserId());

		try {
			$db_results = ContestsUsersDAO::search($contest_user_key, "contest_id", 'DESC');
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		$contests = array();
		foreach ($db_results as $result) {
			
			// Get contest data
			$contest_id = $result->getContestId();
			$contest = ContestsDAO::getByPK($contest_id);
			$contests[$contest->getAlias()]["data"] = $contest->asArray();
			
			// Get user ranking
			$scoreboardR = new Request(array("auth_token" => $r["auth_token"], "contest_alias" => $contest->getAlias()));
			$scoreboardResponse = ContestController::apiScoreboard($scoreboardR);
			
			// TODO: sanking logic shoud go into the scoreboard itself
			$currentPoints = -1;
			$currentPenalty = -1;
			$place = 1;
			$draws = 1;
			foreach($scoreboardResponse["ranking"] as $userData) {
				
				if ($currentPoints === -1) {
					$currentPoints = $userData["total"]["points"];
					$currentPenalty = $userData["total"]["penalty"];
				} else {
					// If not in draw
					if ($userData["total"]["points"] < $currentPoints || $userData["total"]["penalty"] > $currentPenalty) {
						$currentPoints = $userData["total"]["points"];
						$currentPenalty = $userData["total"]["penalty"];
													
						$place += $draws;
						$draws = 1;
						
					} else if ($userData["total"]["points"] == $currentPoints && $userData["total"]["penalty"] == $currentPenalty) {							
						$draws++;
					}
				}

				if ($userData["username"] == $user->getUsername()) {
					break;
				}
				
			}
			
			$contests[$contest->getAlias()]["place"] = $place;			
		}

		$response["contests"] = $contests;
		$response["status"] = "ok";
		return $response;
	}

	/**
	 * Get Problems solved by user
	 * 
	 * @param Request $r
	 * @return Problems array
	 * @throws InvalidDatabaseOperationException
	 */
	public static function apiProblemsSolved(Request $r) {

		self::authenticateRequest($r);

		$response = array();
		$response["runs"] = array();

		$user = $r["current_user"];
		try {
			$response["runs"] = RunsDAO::GetRunsByUser($user->getUserId());
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		$response["status"] = "ok";
		return $response;
	}

	/**
	 * Gets a list of users 
	 * 
	 * @param Request $r
	 */
	public static function apiList(Request $r) {

		self::authenticateRequest($r);

		Validators::isStringNonEmpty($r["term"], "term");

		try {
			$users = UsersDAO::FindByUsernameOrName($r["term"]);
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}

		$response = array();
		foreach ($users as $user) {
			$entry = array("label" => $user->getUsername(), "value" => $user->getUsername());
			array_push($response, $entry);
		}

		return $response;
	}
	
	/**
	 * Get stats 
	 * 
	 * @param Request $r
	 */
	public static function apiStats(Request $r) {
		
		self::authenticateRequest($r);
					
		$user = self::resolveTargetUser($r);
		
		try {
			
			$totalRunsCount = RunsDAO::CountTotalRunsOfUser($user->getUserId());
			
			// List of veredicts			
			$veredict_counts = array();
			
			foreach (self::$veredicts as $veredict) {
				$veredict_counts[$veredict] = RunsDAO::CountTotalRunsOfUserByVeredict($user->getUserId(), $veredict);
			}			
			
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}
		
		return array(
			"veredict_counts" => $veredict_counts,
			"total_runs" => $totalRunsCount,
			"status" => "ok"
		);
	}
	
	/**
	 * Update user profile
	 * 
	 * @param Request $r
	 * @return array
	 * @throws InvalidDatabaseOperationException
	 * @throws InvalidParameterException
	 */
	public static function apiUpdate(Request $r) {
		
		self::authenticateRequest($r);
		
		Validators::isStringNonEmpty($r["name"], "name", false);
		Validators::isStringNonEmpty($r["country_id"], "country_id", false);
		
		if (!is_null($r["country_id"])) {
			try {
				$r["country"] = CountriesDAO::getByPK($r["country_id"]);	
			} catch(Exception $e) {
				throw new InvalidDatabaseOperationException($e);
			}
			
			if (is_null($r["country"])) {
				throw new InvalidParameterException("Country not found");
			}
		}
		
		if ($r["state_id"] === 'null') {
			$r["state_id"] = null;
		}
		
		Validators::isNumber($r["state_id"], "state_id", false);
		
		if (!is_null($r["state_id"])) {
			try {
				$r["state"] = StatesDAO::getByPK($r["state_id"]);
			} catch (Exception $e) { 
				throw new InvalidDatabaseOperationException($e);
			}
			
			if (is_null($r["state"])) {
				throw new InvalidParameterException("State not found");
			}
		}
		
		if (!is_null($r["school_id"])) {
			
			if ($r["school_id"] == -1) {
				// UI sets -1 if school does not exists.
				try {
					$schoolR = new Request(array("name" => $r["school_name"], "state_id" => $r["state_id"], "auth_token" => $r["auth_token"]));
					$response = SchoolController::apiCreate($schoolR);
					$r["school_id"] = $response["school_id"];
				} catch (Exception $e) {
					throw new InvalidParameterException("School creation failed.", $e);
				}
			} else if ($r["school_id"] == "") {
				$r["school_id"] = null;
			} else {			
				try {
					$r["school"] = SchoolsDAO::getByPK($r["school_id"]);	
				} catch(Exception $e) {
					throw new InvalidDatabaseOperationException($e);
				}

				if (is_null($r["school"])) {
					throw new InvalidParameterException("School not found");
				}
			}
		}
		
		Validators::isStringNonEmpty($r["scholar_degree"], "scholar_degree", false);
		Validators::isDate($r["graduation_date"], "graduation_date", false);
		Validators::isDate($r["birth_date"], "birth_date", false);
		
		if (!is_null($r["name"])) {
			$r["current_user"]->setName($r["name"]);
		}
		
		if (!is_null($r["country_id"])) {
			$r["current_user"]->setCountryId($r["country_id"]);
		}
		
		if (!is_null($r["state_id"])) {
			$r["current_user"]->setStateId($r["state_id"]);
		}
		
		if (!is_null($r["scholar_degree"])) {
			$r["current_user"]->setScholarDegree($r["scholar_degree"]);
		}
			
		if (!is_null($r["school_id"])) {			
			$r["current_user"]->setSchoolId($r["school_id"]);
		}
		
		if (!is_null($r["graduation_date"])) {			
			$r["current_user"]->setGraduationDate(gmdate('Y-m-d', $r["graduation_date"]));
		}
		
		if (!is_null($r["birth_date"])) {
			$r["current_user"]->setBirthDate(gmdate('Y-m-d', $r["birth_date"]));
		}
		
		try {
			UsersDAO::save($r["current_user"]);			
		} catch(Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}
		
		return array("status" => "ok");
	}

}

