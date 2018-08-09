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
        $r = new Request([
            'usernameOrEmail' => $user->username,
            'password' => $user->password
        ]);

        // Call the API
        $response = UserController::apiLogin($r);

        $this->assertEquals('ok', $response['status']);
        $this->assertLogin($user, $response['auth_token']);

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

        // Inflate request with user data
        $r = new Request([
            'usernameOrEmail' => $email,
            'password' => $user->password
        ]);

        $response = UserController::apiLogin($r);

        $this->assertEquals('ok', $response['status']);
        $this->assertLogin($user, $response['auth_token']);
    }

    /**
     * Test user login with invalid credentials, username and password
     *
     * @expectedException InvalidCredentialsException
     */
    public function testNativeLoginByUserInvalidPassword() {
        // Create an user in omegaup
        $user = UserFactory::createUser();

        // Inflate request with user data
        $r = new Request([
            'usernameOrEmail' => $user->username,
            'password' => 'badpasswordD:'
        ]);

        // Call the API
        $response = UserController::apiLogin($r);
    }

    /**
     * Test user login with invalid credentials, username and password
     *
     * @expectedException InvalidCredentialsException
     */
    public function testNativeLoginByUserInvalidUsername() {
        // Inflate request with user data
        $r = new Request([
            'usernameOrEmail' => 'IDontExist',
            'password' => 'badpasswordD:'
        ]);

        // Call the API
        $response = UserController::apiLogin($r);
    }

    /**
     * Test user login with invalid credentials, email and password
     *
     * @expectedException InvalidCredentialsException
     */
    public function testNativeLoginByEmailInvalidPassword() {
        // Create an user in omegaup
        $email = Utils::CreateRandomString() . '@mail.com';
        $user = UserFactory::createUser(new UserParams(['email' => $email]));

        // Inflate request with user data
        $r = new Request([
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
        $this->assertLogin($user, $response['auth_token']);
    }

    /**
     * Test 2 consecutive logins, auth tokens should be different
     *
     */
    public function test2ConsecutiveLogins() {
        // Create an user in omegaup
        $user = UserFactory::createUser();

        // Inflate request with user data
        $r = new Request([
            'usernameOrEmail' => $user->username,
            'password' => $user->password
        ]);

        // Call the API
        $response1 = UserController::apiLogin($r);
        $this->assertEquals('ok', $response1['status']);
        $this->assertLogin($user, $response1['auth_token']);

        // Call the API for 2nd time
        $response2 = UserController::apiLogin($r);
        $this->assertEquals('ok', $response2['status']);
        $this->assertLogin($user, $response2['auth_token']);

        $this->assertNotEquals($response1['auth_token'], $response2['auth_token']);
    }

    /**
     * Test user login with valid credentials, username and password
     *
     * @expectedException InvalidCredentialsException
     */
    public function testNativeLoginWithOldPassword() {
        // Create an user in omegaup
        $user = UserFactory::createUser();

        $plainPassword = $user->password;
        // Set old password
        $user->password = md5($plainPassword);
        UsersDAO::save($user);

        // Let's put back plain password
        $user->password = $plainPassword;

        // Inflate request with user data
        $r = new Request([
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
        $auth_token_dao = AuthTokensDAO::getByPK($login->auth_token);
        $auth_token_dao->create_time = date('Y-m-d H:i:s', strtotime($auth_token_dao->create_time . ' - 9 hour'));
        AuthTokensDAO::save($auth_token_dao);

        $auth_token_2 = self::login($user);

        $existingTokens = AuthTokensDAO::getByPK($login->auth_token);
        $this->assertNull($existingTokens);
    }

    /**
     * Logins with empty passwords in DB are disabled
     *
     * @expectedException LoginDisabledException
     */
    public function testLoginDisabled() {
        // User to be verified
        $user = UserFactory::createUser();

        // Force empty password
        $user->password = '';
        UsersDAO::save($user);

        self::login($user);
    }
}
