<?php
class ResetCreateTest extends OmegaupTestCase {
	public function testShouldRequireEmailParameter() {
		$r = new Request();
		$response = ResetController::apiCreate($r);
		$this->assertEquals(STATUS_BAD_REQUEST, $response['status']);
	}

	public function testShouldRefuseNotRegisteredEmailAddresses() {
		$users = Array();
		for ($i = 0; $i < 3; $i++) {
			$email = "user$i@omegaup.com";
			$userRequests[] = new Request(Array('email' => $email));
		}

		for ($i = 0; $i < 3; $i++) {
			$email = $userRequests[$i]['email'];
			$r = new Request(Array('email' => $email));
			$response = ResetController::apiCreate($r);
			$this->assertEquals(STATUS_BAD_REQUEST, $response['status']);
		}

		for ($i = 0; $i < 3; $i++) {
			$username = Utils::CreateRandomString();
			$password = Utils::CreateRandomString();
			$userRequests[$i]['name']		= $username;
			$userRequests[$i]['username']	= $username;
			$userRequests[$i]['password']	= $password;
			UserController::apiCreate($userRequests[$i]);
		}

		for ($i = 0; $i < 3; $i++) {
			$email = $userRequests[$i]['email'];
			$r = new Request(Array('email' => $email));
			$response = ResetController::apiCreate($r);
			$this->assertEquals(STATUS_OK, $response['status']);
		}
	}
}
