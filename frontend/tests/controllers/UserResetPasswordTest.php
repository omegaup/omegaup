<?php
/**
 * Description of UserResetPassword
 */
class UserResetPasswordTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Reset my password
     */
    public function testResetMyPassword() {
        // Create an user in omegaup
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $identity->username,
            'password' => \OmegaUp\Test\Utils::createRandomPassword(),
            'old_password' => $identity->password,
        ]);

        // Call api
        \OmegaUp\Controllers\User::apiChangePassword($r);

        // Try to login with old password, should fail
        try {
            self::login($identity);
            $this->fail('Reset password failed');
        } catch (Exception $e) {
            // We are OK
            $this->assertSame('usernameOrPassIsWrong', $e->getMessage());
        }

        // Set new password and try again, should succeed
        $identity->password = $r['password'];
        self::login($identity);
    }

    /**
     * Reset my password
     */
    public function testResetMyPasswordBadOldPassword() {
        // Create an user in omegaup
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\User::apiChangePassword(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $identity->username,
                'password' => \OmegaUp\Test\Utils::createRandomPassword(),
                'old_password' => 'bad old password',
            ]));
            $this->fail('should have failed due to bad old password');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterInvalid', $e->getMessage());
            $this->assertSame('old_password', $e->parameter);
        }
    }
}
