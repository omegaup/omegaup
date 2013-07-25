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
	
}

