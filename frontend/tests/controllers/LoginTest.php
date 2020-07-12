<?php

/**
 * SessionControllerTest
 *
 * @author joemmanuel
 */
class LoginTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Test identity login with valid credentials, username and password
     *
     */
    public function testNativeLoginByUserPositive() {
        // Create an user in omegaup
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Assert the log is empty.
        $this->assertEquals(
            0,
            count(
                \OmegaUp\DAO\IdentityLoginLog::getByIdentity(
                    $identity->identity_id
                )
            )
        );

        // Inflate request with identity data
        $r = new \OmegaUp\Request([
            'usernameOrEmail' => $identity->username,
            'password' => $identity->password
        ]);

        // Call the API
        $response = \OmegaUp\Controllers\User::apiLogin($r);

        $this->assertLogin($identity, $response['auth_token']);

        // Assert the log is not empty.
        $this->assertEquals(
            1,
            count(
                \OmegaUp\DAO\IdentityLoginLog::getByIdentity(
                    $identity->identity_id
                )
            )
        );
    }

    /**
     * Test identity login with valid credentials, email and password
     */
    public function testNativeLoginByEmailPositive() {
        $email = \OmegaUp\Test\Utils::createRandomString() . '@mail.com';
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(['email' => $email])
        );

        // Inflate request with identity data
        $response = \OmegaUp\Controllers\User::apiLogin(new \OmegaUp\Request([
            'usernameOrEmail' => $email,
            'password' => $identity->password
        ]));

        $this->assertLogin($identity, $response['auth_token']);
    }

    /**
     * Test user login with invalid credentials, username and password
     */
    public function testNativeLoginByUserInvalidPassword() {
        // Create an user in omegaup
        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();

        try {
            \OmegaUp\Controllers\User::apiLogin(new \OmegaUp\Request([
                'usernameOrEmail' => $identity->username,
                'password' => 'badpasswordD:'
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidCredentialsException $e) {
            $this->assertEquals('usernameOrPassIsWrong', $e->getMessage());
        }
    }

    /**
     * Test user login with invalid credentials, username and password
     */
    public function testNativeLoginByUserInvalidUsername() {
        try {
            \OmegaUp\Controllers\User::apiLogin(new \OmegaUp\Request([
                'usernameOrEmail' => 'IDontExist',
                'password' => 'badpasswordD:'
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidCredentialsException $e) {
            $this->assertEquals('usernameOrPassIsWrong', $e->getMessage());
        }
    }

    /**
     * Test user login with invalid credentials, email and password
     */
    public function testNativeLoginByEmailInvalidPassword() {
        // Create an user in omegaup
        $email = \OmegaUp\Test\Utils::createRandomString() . '@mail.com';
        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(['email' => $email])
        );

        try {
            \OmegaUp\Controllers\User::apiLogin(new \OmegaUp\Request([
                'usernameOrEmail' => $email,
                'password' => 'badpasswordD:'
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidCredentialsException $e) {
            $this->assertEquals('usernameOrPassIsWrong', $e->getMessage());
        }
    }

    /**
     * Test login E2E via HTTP entry point
     */
    public function testNativeLoginPositiveViaHttp() {
        // Create an user
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Set required context
        $_REQUEST['usernameOrEmail'] = $identity->username;
        $_REQUEST['password'] = $identity->password;

        // Turn on flag to return auth_token in response, just to validate it
        $_REQUEST['returnAuthToken'] = true;

        // Override session_start, phpunit doesn't like it, but we still validate that it is called once
        $this->mockSessionManager();

        // Call api
        $_SERVER['REQUEST_URI'] = '/api/user/login';
        $response = json_decode(
            \OmegaUp\Test\ApiCallerMock::httpEntryPoint(),
            true
        );

        // Validate output
        $this->assertLogin($identity, $response['auth_token']);
    }

    /**
     * Test 2 consecutive logins, auth tokens should be different
     *
     */
    public function test2ConsecutiveLogins() {
        // Create an identity in omegaup
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Inflate request with identity data
        $r = new \OmegaUp\Request([
            'usernameOrEmail' => $identity->username,
            'password' => $identity->password
        ]);

        // Call the API
        $response1 = \OmegaUp\Controllers\User::apiLogin($r);
        $this->assertLogin($identity, $response1['auth_token']);

        // Call the API for 2nd time
        $response2 = \OmegaUp\Controllers\User::apiLogin($r);
        $this->assertLogin($identity, $response2['auth_token']);

        $this->assertNotEquals(
            $response1['auth_token'],
            $response2['auth_token']
        );
    }

    /**
     * Test identity login with valid credentials, username and password
     */
    public function testNativeLoginWithOldPassword() {
        // Create an user in omegaup
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $plainPassword = $identity->password;
        // Set old password
        \OmegaUp\DAO\Identities::update($identity);

        // Let's put back plain password
        $identity->password = $plainPassword;

        try {
            \OmegaUp\Controllers\User::apiLogin(new \OmegaUp\Request([
                'usernameOrEmail' => $identity->username,
                'password' => $identity->password
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidCredentialsException $e) {
            $this->assertEquals('usernameOrPassIsWrong', $e->getMessage());
        }
    }

    public function testDeleteTokenExpired() {
        // Create an user in omegaup
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);

        // Expire token manually
        $authToken = \OmegaUp\DAO\AuthTokens::getByPK($login->auth_token);
        $authToken->create_time->time -= 9 * 3600;  // 9 hours
        \OmegaUp\DAO\AuthTokens::update($authToken);

        $login2 = self::login($identity);

        $existingTokens = \OmegaUp\DAO\AuthTokens::getByPK($login->auth_token);
        $this->assertNull($existingTokens);
    }

    /**
     * Logins with empty passwords in DB are disabled
     */
    public function testLoginDisabled() {
        // User to be verified
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Force empty password
        $identity->password = '';
        \OmegaUp\DAO\Identities::update($identity);

        try {
            $identity->password = 'foo';
            self::login($identity);
            $this->fail('User should have not been able to log in');
        } catch (\OmegaUp\Exceptions\LoginDisabledException $e) {
            $this->assertEquals('loginDisabled', $e->getMessage());
        }
    }
}
