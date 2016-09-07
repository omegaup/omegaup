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

        $r = new Request();
        $r['auth_token'] = self::login($admin);
        $r['username'] = $user->username;
        $r['password'] = Utils::CreateRandomString();

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

        $r = new Request();
        $r['auth_token'] = self::login($user);
        $r['username'] = $user->username;
        $r['password'] = Utils::CreateRandomString();
        $r['old_password'] = $user->password;

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

        $r = new Request();
        $r['auth_token'] = self::login($user);
        $r['username'] = $user->username;
        $r['password'] = Utils::CreateRandomString();
        $r['old_password'] = 'bad old password';

        // Call api
        UserController::apiChangePassword($r);
    }
}
