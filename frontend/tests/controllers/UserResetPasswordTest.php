<?php

/**
 * Description of UserResetPassword
 *
 * @author joemmanuel
 */
class UserResetPasswordTest extends OmegaupTestCase {
    /**
     * Reset password via admin
     */
    public function testCreateUserPositive() {
        // Create an user in omegaup
        $user = UserFactory::createUser();

        // Create the admin who will change the password
        $admin = UserFactory::createAdminUser();

        $adminLogin = self::login($admin);
        $r = new Request([
            'auth_token' => $adminLogin->auth_token,
            'username' => $user->username,
            'password' => Utils::CreateRandomString(),
        ]);

        // Call api
        UserController::apiChangePassword($r);

        // Try to login with old password, should fail
        try {
            self::login($user);
            $this->fail('Reset password failed');
        } catch (Exception $e) {
            // We are OK
        }

        // Set new password and try again, should succeed
        $user->password = $r['password'];
        self::login($user);

        // Sanity check, admin should be able to login fine
        self::login($admin);
    }

    /**
     * Reset my password
     */
    public function testResetMyPassword() {
        // Create an user in omegaup
        $user = UserFactory::createUser();

        $login = self::login($user);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'username' => $user->username,
            'password' => Utils::CreateRandomString(),
            'old_password' => $user->password,
        ]);

        // Call api
        UserController::apiChangePassword($r);

        // Try to login with old password, should fail
        try {
            self::login($user);
            $this->fail('Reset password failed');
        } catch (Exception $e) {
            // We are OK
        }

        // Set new password and try again, should succeed
        $user->password = $r['password'];
        self::login($user);
    }

    /**
     * Reset my password
     *
     * @expectedException InvalidParameterException
     */
    public function testResetMyPasswordBadOldPassword() {
        // Create an user in omegaup
        $user = UserFactory::createUser();

        $login = self::login($user);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'username' => $user->username,
            'password' => Utils::CreateRandomString(),
            'old_password' => 'bad old password',
        ]);

        // Call api
        UserController::apiChangePassword($r);
    }
}
