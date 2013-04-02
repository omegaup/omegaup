<?php

/**
 * Description of UserResetPassword
 *
 * @author joemmanuel
 */
class UserResetPassword extends OmegaupTestCase {		
	
	/**
	 * Reset password via admin
	 */
	public function testCreateUserPositive() {
		
		// Create an user in omegaup
		$user = UserFactory::createUser();		
		
		// Create the admin who will change the password
		$admin = UserFactory::createAdminUser();
		
		$r = new Request();
		$r["auth_token"] = $this->login($admin);
		$r["username"] = $user->getUsername();
		$r["password"] = Utils::CreateRandomString();
		
		// Call api
		UserController::apiResetPassword($r);
		
		// Try to login with old password, should fail
		try {
			$this->login($user);
			$this->fail("Reset password failed");
		} catch(Exception $e) {
			// We are OK
		}
		
		// Set new password and try again, should succeed
		$user->setPassword($r["password"]);
		$this->login($user);
		
		// Sanity check, admin should be able to login fine
		$this->login($admin);
	} 	
	
	
	/**
	 * Reset my password
	 */
	public function testResetMyPassword() {
		
		// Create an user in omegaup
		$user = UserFactory::createUser();						
		
		$r = new Request();
		$r["auth_token"] = $this->login($user);
		$r["username"] = $user->getUsername();
		$r["password"] = Utils::CreateRandomString();
		
		// Call api
		UserController::apiResetPassword($r);
		
		// Try to login with old password, should fail
		try {
			$this->login($user);
			$this->fail("Reset password failed");
		} catch(Exception $e) {
			// We are OK
		}
		
		// Set new password and try again, should succeed
		$user->setPassword($r["password"]);
		$this->login($user);
		
	} 
}

