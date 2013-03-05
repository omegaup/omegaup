<?php

/**
  * Description:
  *     Session controller handles sessions.
  *
  * Author:
  *     Alan Gonzalez alanboy@alanboy.net
  *
  **/
class SessionController extends Controller {
	const AUTH_TOKEN_ENTROPY_SIZE = 15;
	
	private static $current_session;
	private static $_facebook;
	private static $_sessionManager;

	public static function getSessionManagerInstance() {
	        if (is_null(self::$_sessionManager)) {
			self::$_sessionManager = new SessionManager();
		}
	        return self::$_sessionManager;
	}

	/**
	 * @param string nombre Este es el nombre del dude
	 *
	 **/
	private static function getFacebookInstance() {
		if (is_null(self::$_facebook)) {
			self::$_facebook = new Facebook(array(
			'appId'  => OMEGAUP_FB_APPID,
			'secret' => OMEGAUP_FB_SECRET
			));
		}
		return self::$_facebook;
	}

	private static function isAuthTokenValid($s_AuthToken) {
		//do some other basic testing on s_AuthToken
		return true;
	}

	public static function CurrentSessionAvailable() {
		$a_CurrentSession = self::apiCurrentSession();
		return $a_CurrentSession[ "valid" ] ;
	}

	/**
	 * Returns associative array with information about current session.
	 *
	 **/
	public static function apiCurrentSession() {
		$SessionM = self::getSessionManagerInstance();
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
				"is_admin" => false
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
				"is_admin" => false
			);
		}

		// Get email via his id
		$vo_Email = EmailsDAO::getByPK($vo_CurrentUser->getMainEmailId());

		return array(
			'valid' => true,
			'id' => $vo_CurrentUser->getUserId(),
			'name' => $vo_CurrentUser->getName(),
			'email' => $vo_Email->getEmail(),
			'email_md5' => md5($vo_Email->getEmail()),
			'username' => $vo_CurrentUser->getUsername(),
			'auth_token' => $s_AuthToken,
			'is_admin' => true//$vo_CurrentUser->isAdmin()
		);
	}

	/**
	 *
	 *
	 **/
	public function UnRegisterSession() {
	        $a_CurrentSession = self::apiCurrentSession();
	        $vo_AuthT = new AuthTokens(array("token" => $a_CurrentSession["auth_token"]));

	        try {
			AuthTokensDAO::delete($vo_AuthT);
	        } catch (Exception $e){
	        }

		setcookie(OMEGAUP_AUTH_TOKEN_COOKIE_NAME, 'deleted', 1, '/');
	}


	private function RegisterSession(Users $vo_User, $b_ReturnAuthTokenAsString = false) {
		//find if this user has older sessions
		$vo_AuthT = new AuthTokens();
		$vo_AuthT->setUserId($vo_User->getUserId());

		//erase them
		try {
			$existingTokens = AuthTokensDAO::search($vo_AuthT);
			
			if ($existingTokens !== null) {				
				foreach ($existingTokens as $token) {					
					AuthTokensDAO::delete($token);
				}
			}
			
		} catch (Exception $e) {
			throw new InvalidDatabaseOperationException($e);
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
		
		if ($b_ReturnAuthTokenAsString) {
			return $s_AuthT;
		} else {
			$sm = $this->getSessionManagerInstance();
			$sm->setCookie(OMEGAUP_AUTH_TOKEN_COOKIE_NAME, $s_AuthT, time()+60*60*24, '/');
		}
	}

	public function LoginViaGoogle($s_Email) {
		// We trust this user's identity
		$vo_User = UsersDAO::FindByEmail($s_Email);

		if (is_null($vo_User)) {
			//user has never logged in before
			Logger::log("LoginViaGoogle: Creating new user for $s_Email");
		} else {
			//user has been here before, lets just register his session
			$this->RegisterSession($vo_User);
        	}
	}


	public function LoginViaFacebook($s_Email, $s_FacebookId) {
		//ok, the user does not have any auth token
		//if he wants to test facebook login
		//Facebook must send me the state=something
		//query, so i dont have to be testing 
		//facebook sessions on every single petition
		//made from the front-end
		if (!isset($_GET["state"])) {
			Logger::log("Not logged in and no need to check for fb session");
			return false;
		}
		Logger::log("There is no auth_token cookie, testing for facebook session.");

		//if that is not true, may still be logged with
		//facebook, lets test that
		$facebook = self::getFacebookInstance();
		// Get User ID
		$fb_user = $facebook->getUser();

		// We may or may not have this data based on whether the user is logged in.
		//
		// If we have a $fb_user id here, it means we know the user is logged into
		// Facebook, but we don't know if the access token is valid. An access
		// token is invalid if the user logged out of Facebook.
		if ($fb_user) {
			try {
				// Proceed knowing you have a logged in user who's authenticated.
				$fb_user_profile = $facebook->api('/me');
			} catch (FacebookApiException $e) {
				$fb_user = null;
				Logger::error("FacebookException:" . $e);
			}
		}

		// Now we know if the user is authenticated via facebook
		if (is_null($fb_user)) {
			Logger::log("No facebook session... ");
			return false;
		}

		//ok we know the user is logged in,
		//lets look for his information on the database
		//if there is none, it means that its the first
		//time the user has been here, lets register his info
		Logger::log("User is logged in via facebook !!");

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
		Logger::log("Testing native login for " . $r["usernameOrEmail"]);

		$c_Users = new UserController();
		$vo_User = null;
				
		if (isset($r["returnAuthToken"])) {
			$returnAuthToken = $r["returnAuthToken"];
		} else {
			$returnAuthToken = false;
		}		

		if (!is_null($vo_User = UsersDAO::FindByEmail($r["usernameOrEmail"]))
			|| !is_null($vo_User = UsersDAO::FindByUsername($r["usernameOrEmail"]))) {
			//found user
			$r["user_id"] = $vo_User->getUserId();
		} else {
			Logger::warn("User " . $r["usernameOrEmail"] . " not found.");
			return false;
		}
		
		$b_Valid = $c_Users->TestPassword($r);
		
		if (!$b_Valid) {
			Logger::warn("User " . $r["usernameOrEmail"] . " has introduced invalid credentials.");
			return false;
		}

		Logger::log("User " . $r["usernameOrEmail"] . " has loged in natively.");
		
		try {
			return $this->RegisterSession($vo_User, $returnAuthToken);
		} catch (Exception $e) {
			return false;
			//@TODO actuar en base a la exception
		}
	}
}
