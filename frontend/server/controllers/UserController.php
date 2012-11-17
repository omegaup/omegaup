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
			$user = UsersDAO::getByUsername($r["username"]);
			$userByEmail = UsersDAO::searchUserByEmail($r["email"]);
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
}

