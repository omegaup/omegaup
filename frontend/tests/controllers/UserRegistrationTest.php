<?php

/**
 * Testing new user special cases
 *
 * @author alanboy@omegaup.com
 */
class UserRegistrationTest extends OmegaupTestCase {

	/*
	 *  Scenario:
	 *		user A creates a new native account :
	 *			username=A email=A@example.com
	 *
	 *		user B logs in with fb/google:
	 *			email=A@gmail.com
	 */
	public function testUserData() {

		$salt = time();

		// Test users should not exist
		$this->assertNull(UsersDAO::FindByUsername("A".$salt));
		$this->assertNull(UsersDAO::FindByUsername("A".$salt."1"));

		// Create collision
		UserFactory::createUser("A".$salt);
		SessionController::LoginViaGoogle("A".$salt."@gmail.com");

		$this->assertNotNull(UsersDAO::FindByUsername("A".$salt));
		$this->assertNotNull(UsersDAO::FindByUsername("A".$salt."1"));
	}
}


