<?php

/**
 * Description of UserResetPassword
 *
 * @author joemmanuel
 */
class UserResetPasswordTest extends OmegaupTestCase {
    /**
     * Reset my password
     */
    public function testResetMyPassword() {
        // Create an user in omegaup
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $identity->username,
            'password' => \OmegaUp\Test\Utils::createRandomString(),
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
        }

        // Set new password and try again, should succeed
        $identity->password = $r['password'];
        self::login($identity);
    }

    /**
     * Reset my password
     *
     * @expectedException \OmegaUp\Exceptions\InvalidParameterException
     */
    public function testResetMyPasswordBadOldPassword() {
        // Create an user in omegaup
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $identity->username,
            'password' => \OmegaUp\Test\Utils::createRandomString(),
            'old_password' => 'bad old password',
        ]);

        // Call api
        \OmegaUp\Controllers\User::apiChangePassword($r);
    }
}
