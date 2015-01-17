<?php
class ResetUpdateTest extends OmegaupTestCase {
	public function testShouldRequireAllParameters() {
		try {
			$r = new Request();
			ResetController::apiUpdate($r);
		} catch (InvalidParameterException $expected) {
			// Verify that the cause of the exception was the expected.
			$message = $expected->getMessage();
		}
		$this->assertEquals('invalidParameters', $message);
	}
	
	public function testShouldRefuseUnverifiedUser() {
		try {
			$user_data = UserFactory::generateUser(false);
			$user_data['password_confirmation'] = $user_data['password'];
			$user_data['reset_token'] = Utils::CreateRandomString();
			$r = new Request($user_data);
			ResetController::apiUpdate($r);
		} catch (InvalidParameterException $expected) {
			$message = $expected->getMessage();
		}
		$this->assertEquals('unverifiedUser', $message);
	}

	public function testShouldRefuseInvalidResetToken() {
		try {
			$user_data = UserFactory::generateUser();
			$user_data['password_confirmation'] = $user_data['password'];
			$user_data['reset_token'] = 'abcde';
			$r = new Request($user_data);
			ResetController::apiUpdate($r);
		} catch (InvalidParameterException $expected) {
			$message = $expected->getMessage();
		}
		$this->assertEquals('invalidResetToken', $message);
	}

	
	public function testShouldRefusePasswordMismatch() {
		try {
			$user_data = UserFactory::generateUser();
			$r = new Request(Array('email' => $user_data['email']));
			$response = ResetController::apiCreate($r);
			$user_data['reset_token'] = $response['token'];
			$user_data['password_confirmation'] = 'abcde';
			$r = new Request($user_data);
			ResetController::apiUpdate($r);
		} catch (InvalidParameterException $expected) {
			$message = $expected->getMessage();
		}
		$this->assertEquals('passwordMismatch', $message);
	}

	public function testShouldRefuseInvalidPassword() {
		$user_data = UserFactory::generateUser();
		$r = new Request(Array('email' => $user_data['email']));
		$response = ResetController::apiCreate($r);
		$user_data['reset_token'] = $response['token'];

		$user_data['password'] = 'abcde';
		$user_data['password_confirmation'] = 'abcde';
		$r = new Request($user_data);
		try {
			ResetController::apiUpdate($r);
		} catch (InvalidParameterException $expected) {
			$message = $expected->getMessage();
		}
		$this->assertEquals('parameterStringTooShort', $message);

		$user_data['password'] = str_pad('', 73, 'a');
		$user_data['password_confirmation'] = str_pad('', 73, 'a');
		$r = new Request($user_data);
		try {
			ResetController::apiUpdate($r);
		} catch (InvalidParameterException $expected) {
			$message = $expected->getMessage();
		}
		$this->assertEquals('parameterStringTooLong', $message);
	}

	public function testShouldRefuseExpiredReset() {
		$user_data = UserFactory::generateUser();
		$r = new Request(array('email' => $user_data['email']));
		$response = ResetController::apiCreate($r);
		$user_data['password_confirmation'] = $user_data['password'];
		$user_data['reset_token'] = $response['token'];

		// Time travel
		$reset_sent_at = ApiUtils::GetStringTime(time() - PASSWORD_RESET_TIMEOUT - 1);
		$user = UsersDAO::FindByEmail($user_data['email']);
		$user->setResetSentAt($reset_sent_at);
		UsersDAO::save($user);

		try {
			$r = new Request($user_data);
			$response = ResetController::apiUpdate($r);
		} catch (InvalidParameterException $expected) {
			$message = $expected->getMessage();
		}
		$this->assertEquals('passwordResetResetExpired', $message);
	}

	public function testShouldLogInWithNewPassword() {
		$user_data = UserFactory::generateUser();
		$r = new Request(array('email' => $user_data['email']));
		$create_response = ResetController::apiCreate($r);
		$reset_token = $create_response['token'];
		$user_data['reset_token'] = $reset_token;

		$new_password = 'newpassword';
		$user_data['password'] = $new_password;
		$user_data['password_confirmation'] = $new_password;
		$r = new Request($user_data);

		$user = UsersDAO::FindByEmail($user_data['email']);
		ResetController::apiUpdate($r);
		$user->password = $new_password;
		$this->login($user);
	}
}
