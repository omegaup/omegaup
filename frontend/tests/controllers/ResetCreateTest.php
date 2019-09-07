<?php
class ResetCreateTest extends OmegaupTestCase {
    /**
     * @expectedException \OmegaUp\Exceptions\InvalidParameterException
     */
    public function testShouldRequireEmailParameter() {
        $r = new \OmegaUp\Request();
        $response = \OmegaUp\Controllers\Reset::apiCreate($r);
    }

    /**
     * @expectedException \OmegaUp\Exceptions\InvalidParameterException
     */
    public function testShouldRefuseNotRegisteredEmailAddresses() {
        $email = Utils::CreateRandomString() . '@mail.com';
        $r = new \OmegaUp\Request();
        $response = \OmegaUp\Controllers\Reset::apiCreate($r);
    }

    public function testShouldRefuseUnverifiedUser() {
        $message = null;
        try {
            $user_data = UserFactory::generateUser(false);
            $r = new \OmegaUp\Request($user_data);
            \OmegaUp\Controllers\Reset::apiCreate($r);
        } catch (\OmegaUp\Exceptions\InvalidParameterException $expected) {
            $message = $expected->getMessage();
        }
        $this->assertEquals('unverifiedUser', $message);
    }

    public function testShouldRefuseMultipleRequestsInShortInterval() {
        $user_data = UserFactory::generateUser();
        $r = new \OmegaUp\Request(['email' => $user_data['email']]);
        $response = \OmegaUp\Controllers\Reset::apiCreate($r);

        try {
            \OmegaUp\Controllers\Reset::apiCreate($r);
        } catch (\OmegaUp\Exceptions\InvalidParameterException $expected) {
            $message = $expected->getMessage();
        }
        $this->assertEquals('passwordResetMinWait', $message);

        // time travel
        $reset_sent_at = \OmegaUp\ApiUtils::getStringTime(
            \OmegaUp\Time::get() - PASSWORD_RESET_MIN_WAIT - 1
        );
        $user = \OmegaUp\DAO\Users::findByEmail($user_data['email']);
        $user->reset_sent_at = $reset_sent_at;
        \OmegaUp\DAO\Users::update($user);

        \OmegaUp\Controllers\Reset::apiCreate($r);
    }
}
