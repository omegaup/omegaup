<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
class ResetCreateTest extends \OmegaUp\Test\ControllerTestCase {
    public function testShouldRequireEmailParameter() {
        try {
            \OmegaUp\Controllers\Reset::apiCreate(new \OmegaUp\Request());
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterEmpty', $e->getMessage());
            $this->assertSame('email', $e->parameter);
        }
    }

    public function testShouldRefuseNotRegisteredEmailAddresses() {
        try {
            \OmegaUp\Controllers\Reset::apiCreate(new \OmegaUp\Request([
                'email' => \OmegaUp\Test\Utils::createRandomString() . '@mail.com',
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('invalidUser', $e->getMessage());
        }
    }

    public function testShouldRefuseUnverifiedUser() {
        try {
            $userData = \OmegaUp\Test\Factories\User::generateUser(false);
            \OmegaUp\Controllers\Reset::apiCreate(
                new \OmegaUp\Request(
                    $userData
                )
            );
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('unverifiedUser', $e->getMessage());
        }
    }

    public function testShouldRefuseMultipleRequestsInShortInterval() {
        $userData = \OmegaUp\Test\Factories\User::generateUser();
        $response = \OmegaUp\Controllers\Reset::apiCreate(new \OmegaUp\Request([
            'email' => $userData['email'],
        ]));

        try {
            \OmegaUp\Controllers\Reset::apiCreate(new \OmegaUp\Request([
                'email' => $userData['email'],
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('passwordResetMinWait', $e->getMessage());
        }

        // time travel
        $reset_sent_at = \OmegaUp\ApiUtils::getStringTime(
            \OmegaUp\Time::get() - PASSWORD_RESET_MIN_WAIT - 1
        );
        $user = \OmegaUp\DAO\Users::findByEmail($userData['email']);
        $user->reset_sent_at = $reset_sent_at;
        \OmegaUp\DAO\Users::update($user);

        \OmegaUp\Controllers\Reset::apiCreate(new \OmegaUp\Request([
            'email' => $userData['email'],
        ]));
    }
}
