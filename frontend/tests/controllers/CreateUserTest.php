<?php

/**
 * CreateUserTest
 *
 * @author joemmanuel
 */
class CreateUserTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * Before each test..
	 */
	public function setUp() {		
		//Clean $_REQUEST before each test
		unset($_REQUEST);				
	}
	
	/**
	 * Tests Create User API happy path
	 */
	public function testCreateUserPositive() {
		
		// Set context
		$_REQUEST["username"] = Utils::CreateRandomString();
		$_REQUEST["password"] = Utils::CreateRandomString();
		$_REQUEST["email"] = Utils::CreateRandomString()."@".Utils::CreateRandomString().".com";
		
		// Call api
		$_SERVER["REQUEST_URI"] = "/api/user/create";		
		$response = json_decode(ApiCallerMock::httpEntryPoint(), true);	
		
		$this->assertEquals("ok", $response["status"]);
		
		// Verify DB		
		$user = UsersDAO::FindByUsername($_REQUEST["username"]);
		$this->assertNotNull($user);
		
	}
	
	public function testDuplicatedUsernames() {
		
		// Set context
		$_REQUEST["username"] = Utils::CreateRandomString();
		$_REQUEST["password"] = Utils::CreateRandomString();
		$_REQUEST["email"] = Utils::CreateRandomString()."@".Utils::CreateRandomString().".com";
		
		// Call api
		$_SERVER["REQUEST_URI"] = "/api/user/create";		
		$response = json_decode(ApiCallerMock::httpEntryPoint(), true);
		
		// Randomize email again
		$_REQUEST["email"] = Utils::CreateRandomString()."@".Utils::CreateRandomString().".com";
		
		// Call api
		$_SERVER["REQUEST_URI"] = "/api/user/create";		
		$response = json_decode(ApiCallerMock::httpEntryPoint(), true);

		$this->assertEquals("error", $response["status"]);
	}
	
	public function testDuplicatedEmails() {
		
		// Set context
		$_REQUEST["username"] = Utils::CreateRandomString();
		$_REQUEST["password"] = Utils::CreateRandomString();
		$_REQUEST["email"] = Utils::CreateRandomString()."@".Utils::CreateRandomString().".com";
		
		// Call api
		$_SERVER["REQUEST_URI"] = "/api/user/create";		
		$response = json_decode(ApiCallerMock::httpEntryPoint(), true);
		
		// Randomize username again
		$_REQUEST["username"] = Utils::CreateRandomString();
		
		// Call api
		$_SERVER["REQUEST_URI"] = "/api/user/create";		
		$response = json_decode(ApiCallerMock::httpEntryPoint(), true);
		
		$this->assertEquals("error", $response["status"]);
		
	}
	
	public function testNoPassword() {
		
		// Set context
		$_REQUEST["username"] = Utils::CreateRandomString();		
		$_REQUEST["email"] = Utils::CreateRandomString()."@".Utils::CreateRandomString().".com";
		
		// Call api
		$_SERVER["REQUEST_URI"] = "/api/user/create";		
		$response = json_decode(ApiCallerMock::httpEntryPoint(), true);	
		
		$this->assertEquals("error", $response["status"]);
	}
	
	public function testNoEmail() {
		
		// Set context
		$_REQUEST["username"] = Utils::CreateRandomString();
		$_REQUEST["password"] = Utils::CreateRandomString();
		
		// Call api
		$_SERVER["REQUEST_URI"] = "/api/user/create";		
		$response = json_decode(ApiCallerMock::httpEntryPoint(), true);	
		
		$this->assertEquals("error", $response["status"]);
	}
	
	public function testNoUser() {
		
		// Set context		
		$_REQUEST["password"] = Utils::CreateRandomString();
		$_REQUEST["email"] = Utils::CreateRandomString()."@".Utils::CreateRandomString().".com";
		
		// Call api
		$_SERVER["REQUEST_URI"] = "/api/user/create";		
		$response = json_decode(ApiCallerMock::httpEntryPoint(), true);	
		
		$this->assertEquals("error", $response["status"]);
		
	}
	
}

