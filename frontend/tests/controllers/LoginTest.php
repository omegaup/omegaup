<?php

/**
 * SessionControllerTest
 *
 * @author joemmanuel
 */

class LoginTest extends OmegaupTestCase {	
		
	/**
	 * Test user login with valid credentials, username and password
	 * 
	 */	
	public function testNativeLoginByUserPositive() {
		
		// Create an user in omegaup
		$user = UserFactory::createUser();
		
		// Inflate request with user data
		$r = new Request(array(
			"usernameOrEmail" => $user->getUsername(),
			"password" => $user->getPassword()
		));
		
		// Call the API
		$response = UserController::apiLogin($r);
						
		$this->assertEquals("ok", $response["status"]);
		$this->assertLogin($user, $response["auth_token"]);
	}
	
	/**
	 * Test user login with valid credentials, email and password
	 * 
	 */	
	public function testNativeLoginByEmailPositive() {
		
		// Create an user in omegaup
		$email = Utils::CreateRandomString()."@mail.com";
		$user = UserFactory::createUser(null, null, $email);
		
		// Inflate request with user data
		$r = new Request(array(
			"usernameOrEmail" => $email,
			"password" => $user->getPassword()
		));
		
		// Call the API
		$response = UserController::apiLogin($r);
						
		$this->assertEquals("ok", $response["status"]);
		$this->assertLogin($user, $response["auth_token"]);
	}
	
	/**
	 * Test user login with invalid credentials, username and password
	 * 
	 * @expectedException InvalidCredentialsException
	 */	
	public function testNativeLoginByUserInvalidPassword() {
		
		// Create an user in omegaup
		$user = UserFactory::createUser();
		
		// Inflate request with user data
		$r = new Request(array(
			"usernameOrEmail" => $user->getUsername(),
			"password" => "badpasswordD:"
		));
		
		// Call the API
		$response = UserController::apiLogin($r);
								
	}
	
	/**
	 * Test user login with invalid credentials, username and password
	 * 
	 * @expectedException InvalidCredentialsException
	 */	
	public function testNativeLoginByUserInvalidUsername() {				
		
		// Inflate request with user data
		$r = new Request(array(
			"usernameOrEmail" => "IDontExist",
			"password" => "badpasswordD:"
		));
		
		// Call the API
		$response = UserController::apiLogin($r);
								
	}
	
	/**
	 * Test user login with invalid credentials, email and password
	 * 
	 * @expectedException InvalidCredentialsException
	 */	
	public function testNativeLoginByEmailInvalidPassword() {
		
		// Create an user in omegaup
		$email = Utils::CreateRandomString()."@mail.com";
		$user = UserFactory::createUser(null, null, $email);
		
		// Inflate request with user data
		$r = new Request(array(
			"usernameOrEmail" => $email,
			"password" => "badpasswordD:"
		));
		
		// Call the API
		$response = UserController::apiLogin($r);
								
	}
		
	/**
	 * Test login E2E via HTTP entry point
	 * 
	 * 
	 */
	public function testNativeLoginPositiveViaHttp() {
		
		// Create an user
		$user = UserFactory::createUser();
		
		// Set required context
		$_REQUEST["usernameOrEmail"] = $user->getUsername();
		$_REQUEST["password"] = $user->getPassword();		
		
		// Turn on flag to return auth_token in response, just to validate it
		$_REQUEST["returnAuthToken"] = true;
		
		// Call api
		$_SERVER["REQUEST_URI"] = "/api/user/login";		
		$response = json_decode(ApiCallerMock::httpEntryPoint(), true);	
				
		// Validate output
		$this->assertEquals("ok", $response["status"]);
		$this->assertLogin($user, $response["auth_token"]);
	}	
	
	/**
	 * Test 2 consecutive logins, auth tokens should be different
	 * 
	 */
	public function test2ConsecutiveLogins() {
		
		// Create an user in omegaup
		$user = UserFactory::createUser();
		
		// Inflate request with user data
		$r = new Request(array(
			"usernameOrEmail" => $user->getUsername(),
			"password" => $user->getPassword()
		));
		
		// Call the API
		$response1 = UserController::apiLogin($r);						
		$this->assertEquals("ok", $response1["status"]);
		$this->assertLogin($user, $response1["auth_token"]);
		
		// Call the API for 2nd time
		$response2 = UserController::apiLogin($r);		
		$this->assertEquals("ok", $response2["status"]);
		$this->assertLogin($user, $response2["auth_token"]);
		
		$this->assertNotEquals($response1["auth_token"], $response2["auth_token"]);
		
	}
	
	/**
	 * Test user login with valid credentials, username and password
	 * 
	 */	
	public function testNativeLoginWithOldPassword() {
		
		DAO::$useDAOCache = false;
		
		// Create an user in omegaup
		$user = UserFactory::createUser();
		
		$plainPassword = $user->getPassword();
		// Set old password		
		$user->setPassword(md5($plainPassword));
		UsersDAO::save($user);
		
		// Let's put back plain password
		$user->setPassword($plainPassword);		
		
		// Inflate request with user data
		$r = new Request(array(
			"usernameOrEmail" => $user->getUsername(),
			"password" => $user->getPassword()
		));
		
		// Call the API
		$response = UserController::apiLogin($r);
						
		$this->assertEquals("ok", $response["status"]);
		$this->assertLogin($user, $response["auth_token"]);
		
		$response = UserController::apiLogin($r);
		
		$this->assertEquals("ok", $response["status"]);
		$this->assertLogin($user, $response["auth_token"]);
	}
}

