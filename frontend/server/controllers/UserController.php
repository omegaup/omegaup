<?php

/**
 *  UserController
 *
 * @author joemmanuel
 */
class UserController extends Controller {

	public static function apiCreate(Request $r) {

		// Validate request
		Validators::isStringOfMinLength($r["username"], "username", 2);
		Validators::isEmail($r["email"], "email");
		
		// Setting max passwd length to 72 to avoid DoS attacks
		Validators::isStringOfMinLength($r["password"], "password", 8);
		Validators::isStringOfMaxLength($r["password"], "password", 72);
		
		// Check password
		SecurityTools::testStrongPassword($r["password"]);
		
		// Does user or email already exists?
		try {
			$user = UsersDAO::FindByUsername($r["username"]);
			$userByEmail = UsersDAO::FindByEmail($r["email"]);
		} catch (Exception $e) {
			throw new InvalidDatabaseOperation($e);
		}
		
		if (!(is_null($user) && is_null($userByEmail))) {
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
			throw new InvalidDatabaseOperation($e);
		}
		
		return array ("status" => "ok");
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
	 **/
	public function TestPassword(Request $r) {
		$user_id = $email = $username = $password = null;

		if(isset($r["user_id"])) {
			$user_id = $r["user_id"];
		}

		if(isset($r["email"])) {
			$email = $r["email"];
		}

		if(isset($r["username"])) {
			$username = $r["username"];
		}

		if(isset($r["password"])) {
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

		return SecurityTools::CompareEncryptedStrings(
				SecurityTools::hashString($password),
				$vo_UserToTest->getPassword());
	}

}

