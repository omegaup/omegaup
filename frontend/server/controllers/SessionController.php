<?php

/**
 * Description:
 *     Session controller handles sessions.
 *
 * Author:
 *     Alan Gonzalez alanboy@alanboy.net
 *
 * */
class SessionController extends Controller {

	const AUTH_TOKEN_ENTROPY_SIZE = 15;

	private static $current_session;
	private static $_facebook;
	public static $_sessionManager;
	public static $setCookieOnRegisterSession = true;

	public static function getSessionManagerInstance() {
		if (is_null(self::$_sessionManager)) {
			self::$_sessionManager = new SessionManager();
		}
		return self::$_sessionManager;
	}

	/**
	 * @param string nombre Este es el nombre del dude
	 *
	 * */
	private static function getFacebookInstance() {
		if (is_null(self::$_facebook)) {
			self::$_facebook = new Facebook(array(
						'appId' => OMEGAUP_FB_APPID,
						'secret' => OMEGAUP_FB_SECRET
					));
		}
		return self::$_facebook;
	}

	public static function getFacebookLoginUrl() {
		$facebook = self::getFacebookInstance();

		return $facebook->getLoginUrl(array("scope" => "email"));
	}

	private static function isAuthTokenValid($s_AuthToken) {
		//do some other basic testing on s_AuthToken
		return true;
	}

	public static function CurrentSessionAvailable() {
		$a_CurrentSession = self::apiCurrentSession();
		return $a_CurrentSession["valid"];
	}

	/**
	 * Returns associative array with information about current session.
	 *
	 * */
	public static function apiCurrentSession() {
		$SessionM = self::getSessionManagerInstance();
		$SessionM->sessionStart();
		$s_AuthToken = $SessionM->getCookie(OMEGAUP_AUTH_TOKEN_COOKIE_NAME);
		$vo_CurrentUser = NULL;
				
		//cookie contains an auth token
		if (!is_null($s_AuthToken) && self::isAuthTokenValid($s_AuthToken)) {
			$vo_CurrentUser = AuthTokensDAO::getUserByToken($s_AuthToken);
		} else if (isset($_REQUEST[OMEGAUP_AUTH_TOKEN_COOKIE_NAME])
				&& self::isAuthTokenValid($s_AuthToken = $_REQUEST[OMEGAUP_AUTH_TOKEN_COOKIE_NAME])) {			
			$vo_CurrentUser = AuthTokensDAO::getUserByToken($_REQUEST[OMEGAUP_AUTH_TOKEN_COOKIE_NAME]);
		} else {
			return array(
				"valid" => false,
				"id" => NULL,
				"name" => NULL,
				"username" => NULL,
				"email" => NULL,
				"email_md5" => NULL,
				"auth_token" => NULL,
				"is_admin" => false,
				"login_url" => "/login.php"
			);
		}

		if (is_null($vo_CurrentUser)) {
			// Means user has auth token, but at
			// does not exist in DB

			return array(
				"valid" => false,
				"id" => NULL,
				"name" => NULL,
				"username" => NULL,
				"email" => NULL,
				"email_md5" => NULL,
				"auth_token" => NULL,
				"is_admin" => false,
				"login_url" => "/login.php"
			);
		}

		// Get email via his id
		$vo_Email = EmailsDAO::getByPK($vo_CurrentUser->getMainEmailId());

		$_SESSION['omegaup_user'] = array(
			'name' => $vo_CurrentUser->getUsername(),
			'email' => !is_null($vo_Email) ? $vo_Email->getEmail() : ''
		);

		return array(
			'valid' => true,
			'id' => $vo_CurrentUser->getUserId(),
			'name' => $vo_CurrentUser->getName(),
			'email' => !is_null($vo_Email) ? $vo_Email->getEmail() : '',
			'email_md5' => !is_null($vo_Email) ? md5($vo_Email->getEmail()) : '',
			'username' => $vo_CurrentUser->getUsername(),
			'auth_token' => $s_AuthToken,
			'is_email_verified' => $vo_CurrentUser->getVerified(),
			'is_admin' => Authorization::IsSystemAdmin($vo_CurrentUser->getUserId()),
			'private_contests_count' => ContestsDAO::getPrivateContestsCount($vo_CurrentUser),
			'private_problems_count' => ProblemsDAO::getPrivateCount($vo_CurrentUser)
		);
	}

	/**
	 *
	 *
	 * */
	public function UnRegisterSession() {
		$a_CurrentSession = self::apiCurrentSession();
		$vo_AuthT = new AuthTokens(array("token" => $a_CurrentSession["auth_token"]));

		try {
			AuthTokensDAO::delete($vo_AuthT);
		} catch (Exception $e) {
			
		}

		unset($_SESSION['omegaup_user']);
		setcookie(OMEGAUP_AUTH_TOKEN_COOKIE_NAME, 'deleted', 1, '/');
	}

	private function RegisterSession(Users $vo_User, $b_ReturnAuthTokenAsString = false) {
		//find if this user has older sessions
		$vo_AuthT = new AuthTokens();
		$vo_AuthT->setUserId($vo_User->getUserId());

		
		//erase expired tokens
		try {
			$tokens_erased = AuthTokensDAO::expireAuthTokens($vo_User->getUserId());
		} catch (Exception $e) {			
			// Best effort
			GLogger::error("Failed to delete expired tokens: $e->getMessage()");
		}
		

		// Create the new token
		$entropy = bin2hex(mcrypt_create_iv(SessionController::AUTH_TOKEN_ENTROPY_SIZE, MCRYPT_DEV_URANDOM));
		$s_AuthT = $entropy . "-" . $vo_User->getUserId() . "-" . hash("sha256", OMEGAUP_MD5_SALT . $vo_User->getUserId() . $entropy);

		$vo_AuthT = new AuthTokens();
		$vo_AuthT->setUserId($vo_User->getUserId());
		$vo_AuthT->setToken($s_AuthT);

		try {
			AuthTokensDAO::save($vo_AuthT);
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
		}
			
		if (self::$setCookieOnRegisterSession) {
			$sm = $this->getSessionManagerInstance();
			$sm->setCookie(OMEGAUP_AUTH_TOKEN_COOKIE_NAME, $s_AuthT, time() + 60 * 60 * 24, '/');		
		}		
		
		if ($b_ReturnAuthTokenAsString) {
			return $s_AuthT;
		}
	}

	private static function getUniqueUsernameFromEmail($s_Email) {
		$idx = strpos($s_Email, '@');
		$username = substr($s_Email, 0, $idx);

		try{
			Validators::isValidUsername($username, "username");
		} catch (InvalidParameterException $e) {
			// How can we know whats wrong with the username?
			// Things that could go wrong:
			//		generated email is too short
			$username = "OmegaupUser";
		}

		$suffix = "";
		for (;;) {
			// Maybe we can bring all records from db 
			// with prefix $username, beacuse this:
			$userexists = UsersDAO::FindByUsername($username . $suffix);
			// will query db every single time probably.

			if (empty($userexists)) {
				break;
			}

			if (empty($suffix)) {
				$suffix = 1;
			}else{
				$suffix++;
			}
		}
		$username .= $suffix;

		return $username;
	}

	public function LoginViaGoogle($s_Email) {
		// We trust this user's identity
		$vo_User = UsersDAO::FindByEmail($s_Email);

		if (is_null($vo_User)) {
			// This email does not exist in omegaup
			GLogger::log("LoginViaGoogle: Creating new user for $s_Email");

			$username = self::getUniqueUsernameFromEmail($s_Email);

			$r = new Request(
							array(
								"name" => $username,
								"username" => $username,
								"email" => $s_Email,
								"password" => NULL,
								"ignore_password" => true
							)
						);

			$res = UserController::apiCreate($r);

		} else {
			//user has been here before, lets just register the session
			$this->RegisterSession($vo_User);
		}
	}

	public function LoginViaFacebook( ) {
		//ok, the user does not have any auth token
		//if he wants to test facebook login
		//Facebook must send me the state=something
		//query, so i dont have to be testing 
		//facebook sessions on every single petition
		//made from the front-end
		if (!isset($_GET["state"])) {
			return false;
		}

		//if that is not true, may still be logged with
		//facebook, lets test that
		$facebook = self::getFacebookInstance();

		// Get User ID
		$fb_user = $facebook->getUser();

		if($fb_user == 0) {
			GLogger::log("FB session unavailable.");
			return false;
		}

		// We may or may not have this data based on whether the user is logged in.
		// If we have a $fb_user id here, it means we know the user is logged into
		// Facebook, but we don't know if the access token is valid. An access
		// token is invalid if the user logged out of Facebook.

		try {
			// Proceed knowing you have a logged in user who's authenticated.
			$fb_user_profile = $facebook->api('/me');

		} catch (FacebookApiException $e) {
			$fb_user = null;
			GLogger::error("FacebookException:" . $e);
			return false;

		}

		//ok we know the user is logged in,
		//lets look for his information on the database
		//if there is none, it means that its the first
		//time the user has been here, lets register his info
		GLogger::log("User is logged in via facebook !!");

		$results = UsersDAO::FindByEmail( $fb_user_profile["email"] );	

		if (!is_null($results)) {
			//user has been here before with facebook!
			$vo_User = $results;
		} else {
				// The user has never been here before, let's register him

				// I have a problem with this:
				$username = self::getUniqueUsernameFromEmail($fb_user_profile["email"]);
				// Even if the user gave us his/her email, we should not
				// just go ahead and assume its ok to share with the world
				// maybe we could do:
				// $username = str_replace(" ", "_", $fb_user_profile["name"] ),

				$r = new Request(
								array(
									"name" => $fb_user_profile["name"],
									"username" => $username,
									"email" => $fb_user_profile["email"],
									"facebook_user_id" => $fb_user_profile["id"],
									"password" => NULL,
									"ignore_password" => true
								)
							);
				$res = UserController::apiCreate($r);
				$vo_User = UsersDAO::getByPK($res["user_id"]);
		}

		//since we got here, this user does not have
		//any auth token, lets give him one
		//so we dont have to call facebook to see
		//if he is still logged in, and he can call
		//the api 
		$this->RegisterSession( $vo_User );

	}

	/**
	 * Does login for a user given username or email and password.
	 * Expects in request:
	 * usernameOrEmail
	 * password
	 * 
	 * @param Request $r
	 * @return boolean
	 */
	public function NativeLogin(Request $r) {
		GLogger::log("Testing native login for " . $r["usernameOrEmail"]);

		$c_Users = new UserController();
		$vo_User = null;

		if (isset($r["returnAuthToken"])) {
			$returnAuthToken = $r["returnAuthToken"];
		} else {
			$returnAuthToken = false;
		}

		try {			
			$vo_User = UserController::resolveUser($r["usernameOrEmail"]);			
			$r["user_id"] = $vo_User->getUserId();
			$r["user"] = $vo_User;
		} catch (ApiException $e) {
			GLogger::warn("User " . $r["usernameOrEmail"] . " not found.");
			return false;
		}

		$b_Valid = $c_Users->TestPassword($r);

		if (!$b_Valid) {
			GLogger::warn("User " . $r["usernameOrEmail"] . " has introduced invalid credentials.");
			return false;
		}

		GLogger::log("User " . $r["usernameOrEmail"] . " has loged in natively.");
		
		UserController::checkEmailVerification($r);
		
		try {
			return $this->RegisterSession($vo_User, $returnAuthToken);
		} catch (Exception $e) {
			return false;
			//@TODO actuar en base a la exception
		}
	}

}
