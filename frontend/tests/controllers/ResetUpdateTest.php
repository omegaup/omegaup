<?php
class ResetUpdateTest extends OmegaupTestCase {
	public function testShouldRequireAllParameters() {
		$r = new Request();
		$response = ResetController::apiUpdate($r);
		$this->assertEquals(STATUS_BAD_REQUEST, $response['status']);
		// EXPERIMENTAL: What do you think about some tests like this
		// (ignoring the fact that expected message is hard coded)?
		$this->assertEquals('Missing parameters.', $response['message']);

		$r['email'] = 'user@omegaup.com';
		$response = ResetController::apiUpdate($r);
		$this->assertEquals(STATUS_BAD_REQUEST, $response['status']);
	}

	public function testShouldRefuseUnregisteredEmailAddresses() {
		$r = new Request(Array(
			'email'					=> 'user@omegaup.com',
			'reset_token'			=> ApiUtils::GetRandomString(),
			'password'				=> 'newpassword',
			'password_confirmation'	=> 'newpassword'
		));

		$response = ResetController::apiUpdate($r);
		$this->assertEquals(STATUS_BAD_REQUEST, $response['status']);
	}

	public function testShouldRefuseInvalidResetToken() {
		$username = 'user';
		$password = 'mypassword';
		$email = 'user@omegaup.com';
		$user = UserFactory::createUser($username, $password, $email);
		$r = new Request(Array(
			'email'					=> $email,
			'reset_token'			=> ApiUtils::GetRandomString(),
			'password'				=> 'newpassword',
			'password_confirmation'	=> 'newpassword'
		));

		$response = ResetController::apiUpdate($r);
		$this->assertEquals(STATUS_BAD_REQUEST, $response['status']);
	}

	public function testShouldRefusePasswordMismatch() {
		Utils::CleanupDB();
		$username = 'user';
		$password = 'mypassword';
		$email = 'user@omegaup.com';
		$user = UserFactory::createUser($username, $password, $email);

		$r = new Request(Array('email' => $email));
		$response = ResetController::apiCreate($r);
		$token = $response['token'];
		$r = new Request(Array(
			'email'					=> $email,
			'reset_token'			=> $token,
			'password'				=> 'oldpassword',
			'password_confirmation'	=> 'newpassword'
		));

		$response = ResetController::apiUpdate($r);
		$this->assertEquals(STATUS_BAD_REQUEST, $response['status']);
	}

	public function testShouldRefuseWeakPassword() {
		Utils::CleanupDB();
		$username = 'user';
		$password = 'mypassword';
		$email = 'user@omegaup.com';
		$user = UserFactory::createUser($username, $password, $email);

		$r = new Request(Array('email' => $email));
		$response = ResetController::apiCreate($r);
		$token = $response['token'];
		$r = new Request(Array(
			'email'					=> $email,
			'reset_token'			=> $token,
			'password'				=> 'a',
			'password_confirmation'	=> 'a'
		));

		$response = ResetController::apiUpdate($r);
		$this->assertEquals(STATUS_BAD_REQUEST, $response['status']);
	}

	public function testShouldRefuseExpiredReset() {
		Utils::CleanupDB();
		$username = 'user';
		$password = 'mypassword';
		$email = 'user@omegaup.com';
		$user = UserFactory::createUser($username, $password, $email);

		$r = new Request(Array('email' => $email));
		$response = ResetController::apiCreate($r);
		$token = $response['token'];
		$new_password = 'mybrandnewpassword';
		$r = new Request(Array(
			'email'					=> $email,
			'reset_token'			=> $token,
			'password'				=> $new_password,
			'password_confirmation'	=> $new_password
		));
		
		$reset_digest = hash('sha1', $token);
		// Time travel
		$reset_sent_at = ApiUtils::GetStringTime(time() - (2 * 3600 + 1));
		UsersDAO::UpdateResetInfo($user->user_id, $reset_digest,$reset_sent_at);

		$response = ResetController::apiUpdate($r);
		$this->assertEquals(STATUS_BAD_REQUEST, $response['status']);

		// Using tests like this we can be sure the the operation failed due to
		// the intended reason.
		$this->assertEquals('Token expired.', $response['message']);
	}

	public function testShouldLogInWithNewPassword() {
		Utils::CleanupDB();
		$username = 'user';
		$password = 'mypassword';
		$email = 'user@omegaup.com';
		$user = UserFactory::createUser($username, $password, $email);

		$r = new Request(Array('email' => $email));
		$response = ResetController::apiCreate($r);
		$token = $response['token'];
		$new_password = 'mybrandnewpassword';
		$r = new Request(Array(
			'email'					=> $email,
			'reset_token'			=> $token,
			'password'				=> $new_password,
			'password_confirmation'	=> $new_password
		));

		$response = ResetController::apiUpdate($r);
		$this->assertEquals(STATUS_OK, $response['status']);

		$user->password = $new_password;
		$this->login($user);
	}
}
