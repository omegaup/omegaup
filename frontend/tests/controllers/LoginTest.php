<?php

/**
 * SessionControllerTest
 *
 * @author joemmanuel
 */
class LoginTest extends OmegaupTestCase {
    /**
     * Test user login with valid credentials, username and password
     *
     */
    public function testNativeLoginByUserPositive() {
        // Create an user in omegaup
        $user = UserFactory::createUser();
        $identity = IdentitiesDAO::getByPK($user->main_identity_id);

        // Assert the log is empty.
        $this->assertEquals(0, count(IdentityLoginLogDAO::getByIdentity($identity->identity_id)));

        // Inflate request with user data
        $r = new \OmegaUp\Request([
            'usernameOrEmail' => $user->username,
            'password' => $user->password
        ]);

        // Call the API
        $response = UserController::apiLogin($r);

        $this->assertEquals('ok', $response['status']);
        $this->assertLogin($identity, $response['auth_token']);

        // Assert the log is not empty.
        $this->assertEquals(1, count(IdentityLoginLogDAO::getByIdentity($identity->identity_id)));
    }

    /**
     * Test user login with valid credentials, email and password
     *
     */
    public function testNativeLoginByEmailPositive() {
        $email = Utils::CreateRandomString() . '@mail.com';
        $user = UserFactory::createUser(new UserParams(['email' => $email]));
        $identity = IdentitiesDAO::getByPK($user->main_identity_id);

        // Inflate request with user data
        $r = new \OmegaUp\Request([
            'usernameOrEmail' => $email,
            'password' => $user->password
        ]);

        $response = UserController::apiLogin($r);

        $this->assertEquals('ok', $response['status']);
        $this->assertLogin($identity, $response['auth_token']);
    }

    /**
     * Test user login with invalid credentials, username and password
     *
     * @expectedException \OmegaUp\Exceptions\InvalidCredentialsException
     */
    public function testNativeLoginByUserInvalidPassword() {
        // Create an user in omegaup
        $user = UserFactory::createUser();

        // Inflate request with user data
        $r = new \OmegaUp\Request([
            'usernameOrEmail' => $user->username,
            'password' => 'badpasswordD:'
        ]);

        // Call the API
        $response = UserController::apiLogin($r);
    }

    /**
     * Test user login with invalid credentials, username and password
     *
     * @expectedException \OmegaUp\Exceptions\InvalidCredentialsException
     */
    public function testNativeLoginByUserInvalidUsername() {
        // Inflate request with user data
        $r = new \OmegaUp\Request([
            'usernameOrEmail' => 'IDontExist',
            'password' => 'badpasswordD:'
        ]);

        // Call the API
        $response = UserController::apiLogin($r);
    }

    /**
     * Test user login with invalid credentials, email and password
     *
     * @expectedException \OmegaUp\Exceptions\InvalidCredentialsException
     */
    public function testNativeLoginByEmailInvalidPassword() {
        // Create an user in omegaup
        $email = Utils::CreateRandomString() . '@mail.com';
        $user = UserFactory::createUser(new UserParams(['email' => $email]));

        // Inflate request with user data
        $r = new \OmegaUp\Request([
            'usernameOrEmail' => $email,
            'password' => 'badpasswordD:'
        ]);

        // Call the API
        $response = UserController::apiLogin($r);
    }

    /**
     * Test login E2E via HTTP entry point
     *
     *
     */
    public function testNativeLoginPositiveViaHttp() {
        // Create an user
        $user = UserFactory::createUser();
        $identity = IdentitiesDAO::getByPK($user->main_identity_id);

        // Set required context
        $_REQUEST['usernameOrEmail'] = $user->username;
        $_REQUEST['password'] = $user->password;

        // Turn on flag to return auth_token in response, just to validate it
        $_REQUEST['returnAuthToken'] = true;

        // Override session_start, phpunit doesn't like it, but we still validate that it is called once
        $this->mockSessionManager();

        // Call api
        $_SERVER['REQUEST_URI'] = '/api/user/login';
        $response = json_decode(ApiCallerMock::httpEntryPoint(), true);

        // Validate output
        $this->assertEquals('ok', $response['status']);
        $this->assertLogin($identity, $response['auth_token']);
    }

    /**
     * Test 2 consecutive logins, auth tokens should be different
     *
     */
    public function test2ConsecutiveLogins() {
        // Create an user in omegaup
        $user = UserFactory::createUser();
        $identity = IdentitiesDAO::getByPK($user->main_identity_id);

        // Inflate request with user data
        $r = new \OmegaUp\Request([
            'usernameOrEmail' => $user->username,
            'password' => $user->password
        ]);

        // Call the API
        $response1 = UserController::apiLogin($r);
        $this->assertEquals('ok', $response1['status']);
        $this->assertLogin($identity, $response1['auth_token']);

        // Call the API for 2nd time
        $response2 = UserController::apiLogin($r);
        $this->assertEquals('ok', $response2['status']);
        $this->assertLogin($identity, $response2['auth_token']);

        $this->assertNotEquals($response1['auth_token'], $response2['auth_token']);
    }

    /**
     * Test user login with valid credentials, username and password
     *
     * @expectedException \OmegaUp\Exceptions\InvalidCredentialsException
     */
    public function testNativeLoginWithOldPassword() {
        // Create an user in omegaup
        $user = UserFactory::createUser();

        $plainPassword = $user->password;
        // Set old password
        $user->password = md5($plainPassword);
        UsersDAO::update($user);
        $identity = IdentitiesDAO::getByPK($user->main_identity_id);
        $identity->password = $user->password;
        IdentitiesDAO::update($identity);

        // Let's put back plain password
        $user->password = $plainPassword;

        // Inflate request with user data
        $r = new \OmegaUp\Request([
            'usernameOrEmail' => $user->username,
            'password' => $user->password
        ]);

        // Call the API
        $response = UserController::apiLogin($r);
    }

    public function testDeleteTokenExpired() {
        // Create an user in omegaup
        $user = UserFactory::createUser();

        $login = self::login($user);

        // Expire token manually
        $authToken = AuthTokensDAO::getByPK($login->auth_token);
        $authToken->create_time -= 9 * 3600;  // 9 hours
        AuthTokensDAO::update($authToken);

        $login2 = self::login($user);

        $existingTokens = AuthTokensDAO::getByPK($login->auth_token);
        $this->assertNull($existingTokens);
    }

    /**
     * Logins with empty passwords in DB are disabled
     */
    public function testLoginDisabled() {
        // User to be verified
        $user = UserFactory::createUser();

        // Force empty password
        $user->password = '';
        UsersDAO::update($user);
        $identity = IdentitiesDAO::getByPK($user->main_identity_id);
        $identity->password = $user->password;
        IdentitiesDAO::update($identity);

        try {
            $user->password = 'foo';
            self::login($user);
            $this->fail('User should have not been able to log in');
        } catch (\OmegaUp\Exceptions\LoginDisabledException $e) {
            $this->assertEquals('loginDisabled', $e->getMessage());
        }
    }
}
