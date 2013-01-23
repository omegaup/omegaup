<?php

/**
 * SessionControllerTest
 *
 * @author joemmanuel
 */

require_once 'UserFactory.php';

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
}

