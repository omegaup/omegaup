<?php

/**
 * CreateUserTest
 *
 * @author joemmanuel
 */


class CreateUserTest extends OmegaupTestCase {		
	
	/**
	 * Creates an omegaup user happily :)
	 */
	public function testCreateUserPositive() {
		
		// Inflate request 
		$r = new Request(array(
			"username" => Utils::CreateRandomString(),
			"password" => Utils::CreateRandomString(),
			"email" => Utils::CreateRandomString()."@".Utils::CreateRandomString().".com"
		));
		
		// Call API		
		$response = UserController::apiCreate($r);
		
		// Check response
		$this->assertEquals("ok", $response["status"]);
		
		// Verify DB		
		$user = UsersDAO::FindByUsername($r["username"]);
		$this->assertNotNull($user);		
	}
	
	/**
	 * Try to create 2 users with same username, should fail.
	 * 
	 * @expectedException DuplicatedEntryInDatabaseException
	 */
	public function testDuplicatedUsernames() {
		
		// Inflate request 
		$r = new Request(array(
			"username" => Utils::CreateRandomString(),
			"password" => Utils::CreateRandomString(),
			"email" => Utils::CreateRandomString()."@".Utils::CreateRandomString().".com"
		));
		
		// Call API		
		$response = UserController::apiCreate($r);
		
		// Randomize email again
		$r["email"] = Utils::CreateRandomString()."@".Utils::CreateRandomString().".com";
		
		// Call api
		$response = UserController::apiCreate($r);								
	}
	
	
	/**
	 * Test create 2 users with same email (diff username) should fail
	 * 
	 * @expectedException DuplicatedEntryInDatabaseException
	 */
	public function testDuplicatedEmails() {
		
		// Inflate request 
		$r = new Request(array(
			"username" => Utils::CreateRandomString(),
			"password" => Utils::CreateRandomString(),
			"email" => Utils::CreateRandomString()."@".Utils::CreateRandomString().".com"
		));
		
		// Call API
		$response = UserController::apiCreate($r);
		
		// Randomize email again
		$r["username"] = Utils::CreateRandomString();
		
		// Call api				
		$response = UserController::apiCreate($r);						
		
	}
	
	
	/** 
	 * Creating a user without password
	 * 
	 * @expectedException InvalidParameterException
	 */
	public function testNoPassword() {
		
		// Inflate request 
		$r = new Request(array(
			"username" => Utils::CreateRandomString(),
			"email" => Utils::CreateRandomString()."@".Utils::CreateRandomString().".com"
		));
		
		// Call API
		$response = UserController::apiCreate($r);						
	}
	
	/**
	 * Creating a user without email
	 * 
	 * @expectedException InvalidParameterException
	 */
	public function testNoEmail() {
		
		// Inflate request 
		$r = new Request(array(
			"username" => Utils::CreateRandomString(),
			"password" => Utils::CreateRandomString()
		));
		
		// Call API
		$response = UserController::apiCreate($r);		
	}
	
	/**
	 * Create a user without username...
	 * 
	 * @expectedException InvalidParameterException
	 */
	public function testNoUser() {
		
		// Inflate request 
		$r = new Request(array(
			"password" => Utils::CreateRandomString(),
			"email" => Utils::CreateRandomString()."@".Utils::CreateRandomString().".com"
		));
		
		// Call API
		UserController::apiCreate($r);
		
	}
	
	/**
	 * Tests Create User API happy path excercising the httpEntryPoint
	 */
	public function testCreateUserPositiveViahttpEntryPoint() {
		
		// Set context
		$_REQUEST["username"] = Utils::CreateRandomString();
		$_REQUEST["password"] = Utils::CreateRandomString();
		$_REQUEST["email"] = Utils::CreateRandomString()."@".Utils::CreateRandomString().".com";

		// Override session_start, phpunit doesn't like it, but we still validate that it is called once
		$this->mockSessionManager();
		
		// Call api
		$_SERVER["REQUEST_URI"] = "/api/user/create";		
		$response = json_decode(ApiCallerMock::httpEntryPoint(), true);	
		
		$this->assertEquals("ok", $response["status"]);
		
		// Verify DB		
		$user = UsersDAO::FindByUsername($_REQUEST["username"]);
		$this->assertNotNull($user);
		
	}
	
	/**
	 * Tests usernames with invalid chars. Exception is expected
	 * 
	 * @expectedException InvalidParameterException
	 */
	public function testUsernameWithInvalidChars() {
		
		// Inflate request 
		$r = new Request(array(
			"username" => "ínvalid username",
			"password" => Utils::CreateRandomString(),
			"email" => Utils::CreateRandomString()."@".Utils::CreateRandomString().".com"
		));
		
		// Call API		
		$response = UserController::apiCreate($r);				
	}
	
	
	/**
	 * Admin can verify users only with username
	 */
	public function testUsernameVerificationByAdmin() {
	
		// User to be verified
		$user = UserFactory::createUser(null, null, null, false /*not verified*/);
		
		// Admin will verify $user
		$admin = UserFactory::createAdminUser();
		
		// Call api using admin 		
		$response = UserController::apiVerifyEmail(new Request(array(
			"auth_token" => $this->login($admin),
			"usernameOrEmail" => $user->getUsername()
		)));
		
		// Get user from db again to pick up verification changes
		$userdb = UsersDAO::FindByUsername($user->getUsername());
		
		$this->assertEquals(1, $userdb->getVerified());
		$this->assertEquals("ok", $response["status"]);
	}
	
	/**
	 * Admin can verify users only with username
	 * Testing invalid username
	 * 
	 * @expectedException NotFoundException
	 */
	public function testUsernameVerificationByAdminInvalidUsername() {
			
		
		// Admin will verify $user
		$admin = UserFactory::createAdminUser();
		
		// Call api using admin 		
		$response = UserController::apiVerifyEmail(new Request(array(
			"auth_token" => $this->login($admin),
			"usernameOrEmail" => Utils::CreateRandomString()
		)));		
	}
	
	/**
	 * Normal user trying to go the admin path
	 * 
	 * @expectedException ForbiddenAccessException
	 */
	public function testUsernameVerificationByAdminNotAdmin() {
		
		// User to be verified
		$user = UserFactory::createUser(null, null, null, false /*not verified*/);
		
		// Another user will try to verify $user
		$user2 = UserFactory::createUser();
		
		// Call api using admin 		
		$response = UserController::apiVerifyEmail(new Request(array(
			"auth_token" => $this->login($user2),
			"usernameOrEmail" => $user->getUsername()
		)));
		
	}
}

