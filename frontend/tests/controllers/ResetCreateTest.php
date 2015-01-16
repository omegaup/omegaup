<?php
class ResetCreateTest extends OmegaupTestCase {
	public function testShouldRequireEmailParameter() {
		$this->setExpectedException('InvalidParameterException');
		$r = new Request();
		$response = ResetController::apiCreate($r);
	}

	public function testShouldRefuseNotRegisteredEmailAddresses() {
		$this->setExpectedException('InvalidParameterException');
		$email = Utils::CreateRandomString()."@mail.com";
		$r = new Request();
		$response = ResetController::apiCreate($r);
	}
}
