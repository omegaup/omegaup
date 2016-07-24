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

        // Assert the log is empty.
        $this->assertEquals(0, count(UserLoginLogDAO::search(array(
            'user_id' => $user->user_id,
        ))));

        // Inflate request with user data
        $r = new Request(array(
                    'usernameOrEmail' => $user->getUsername(),
                    'password' => $user->getPassword()
                ));

        // Call the API
        $response = UserController::apiLogin($r);

        $this->assertEquals('ok', $response['status']);
        $this->assertLogin($user, $response['auth_token']);

        // Assert the log is not empty.
        $this->assertEquals(1, count(UserLoginLogDAO::search(array(
            'user_id' => $user->user_id,
        ))));
    }

    /**
     * Test user login with valid credentials, email and password
     *
     */
    public function testNativeLoginByEmailPositive() {
        $email = Utils::CreateRandomString() . '@mail.com';
        $user = UserFactory::createUser(null, null, $email);

        // Inflate request with user data
        $r = new Request(array(
                    'usernameOrEmail' => $email,
                    'password' => $user->getPassword()
                ));

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
        $r = new Request(array(
                    'usernameOrEmail' => $user->getUsername(),
                    'password' => 'badpasswordD:'
                ));

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
        $r = new Request(array(
                    'usernameOrEmail' => 'IDontExist',
                    'password' => 'badpasswordD:'
                ));

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
        $user = UserFactory::createUser(null, null, $email);

        // Inflate request with user data
        $r = new Request(array(
                    'usernameOrEmail' => $email,
                    'password' => 'badpasswordD:'
                ));

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
        $_REQUEST['usernameOrEmail'] = $user->getUsername();
        $_REQUEST['password'] = $user->getPassword();

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
        $r = new Request(array(
                    'usernameOrEmail' => $user->getUsername(),
                    'password' => $user->getPassword()
                ));

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

        $plainPassword = $user->getPassword();
        // Set old password
        $user->setPassword(md5($plainPassword));
        UsersDAO::save($user);

        // Let's put back plain password
        $user->setPassword($plainPassword);

        // Inflate request with user data
        $r = new Request(array(
                    'usernameOrEmail' => $user->getUsername(),
                    'password' => $user->getPassword()
                ));

        // Call the API
        $response = UserController::apiLogin($r);
    }

    public function testDeleteTokenExpired() {
        // Create an user in omegaup
        $user = UserFactory::createUser();

        $auth_token = self::login($user);

        // Expire token manually
        $auth_token_dao = AuthTokensDAO::getByPK($auth_token);
        $auth_token_dao->setCreateTime(date('Y-m-d H:i:s', strtotime($auth_token_dao->getCreateTime() . ' - 9 hour')));
        AuthTokensDAO::save($auth_token_dao);

        $auth_token_2 = self::login($user);

        $existingTokens = AuthTokensDAO::getByPK($auth_token);
        $this->assertNull($existingTokens);
    }

    /**
     * Test SessionController::apiCurrentSession private_contests_count
     * when there's 1 private contest count
     */
    public function testSessionControlerPrivateContestsCount() {
        // Create private contest
        $contestData = ContestsFactory::createContest(null, 0 /*public*/);
        $user = $contestData['director'];

        $this->mockSessionManager();

        // Login
        $auth_token = $this->login($user);

        // Prepare COOKIE as SessionMannager->getCookie expects
        $_COOKIE[OMEGAUP_AUTH_TOKEN_COOKIE_NAME] = $auth_token;

        // Call CurrentSession api
        $response = SessionController::apiCurrentSession();

        $this->assertEquals(1, $response['private_contests_count']);
    }

    /**
     * Test SessionController::apiCurrentSession private_contests_count
     * when there's 1 public contest
     */
    public function testSessionControlerPrivateContestsCountWithPublicContest() {
        // Create private contest
        $contestData = ContestsFactory::createContest(null, 1 /*public*/);
        $user = $contestData['director'];

        $this->mockSessionManager();

        // Login
        $auth_token = $this->login($user);

        // Prepare COOKIE as SessionMannager->getCookie expects
        $_COOKIE[OMEGAUP_AUTH_TOKEN_COOKIE_NAME] = $auth_token;

        // Call CurrentSession api
        $response = SessionController::apiCurrentSession();

        $this->assertEquals(0, $response['private_contests_count']);
    }

    /**
     * Test SessionController::apiCurrentSession private_contests_count
     * when there's 0 contests created
     */
    public function testSessionControlerPrivateContestsCountWithNoContests() {
        $user = UserFactory::createUser();

        $this->mockSessionManager();

        // Login
        $auth_token = $this->login($user);

        // Prepare COOKIE as SessionMannager->getCookie expects
        $_COOKIE[OMEGAUP_AUTH_TOKEN_COOKIE_NAME] = $auth_token;

        // Call CurrentSession api
        $response = SessionController::apiCurrentSession();

        $this->assertEquals(0, $response['private_contests_count']);
    }

    /**
     * Test SessionController::apiCurrentSession private_problems_count
     * when there's 1 private problem
     */
    public function testSessionControlerPrivateProblemsCount() {
        // Create private problem
        $problemData = ProblemsFactory::createProblem(null, null, 0 /*public*/);
        $user = $problemData['author'];

        $this->mockSessionManager();

        // Login
        $auth_token = $this->login($user);

        // Prepare COOKIE as SessionMannager->getCookie expects
        $_COOKIE[OMEGAUP_AUTH_TOKEN_COOKIE_NAME] = $auth_token;

        // Call CurrentSession api
        $response = SessionController::apiCurrentSession();

        $this->assertEquals(1, $response['private_problems_count']);
    }

    /**
     * Test SessionController::apiCurrentSession private_problems_count
     * when there's 1 public problem
     */
    public function testSessionControlerPrivateProblemsCountWithPublicProblem() {
        // Create public problem
        $problemData = ProblemsFactory::createProblem(null, null, 1 /*public*/);
        $user = $problemData['author'];

        $this->mockSessionManager();

        // Login
        $auth_token = $this->login($user);

        // Prepare COOKIE as SessionMannager->getCookie expects
        $_COOKIE[OMEGAUP_AUTH_TOKEN_COOKIE_NAME] = $auth_token;

        // Call CurrentSession api
        $response = SessionController::apiCurrentSession();

        $this->assertEquals(0, $response['private_problems_count']);
    }

    /**
     * Test SessionController::apiCurrentSession private_problems_count
     * when there's 0 problems
     */
    public function testSessionControlerPrivateProblemsCountWithNoProblems() {
        $user = UserFactory::createUser();

        $this->mockSessionManager();

        // Login
        $auth_token = $this->login($user);

        // Prepare COOKIE as SessionMannager->getCookie expects
        $_COOKIE[OMEGAUP_AUTH_TOKEN_COOKIE_NAME] = $auth_token;

        // Call CurrentSession api
        $response = SessionController::apiCurrentSession();

        $this->assertEquals(0, $response['private_problems_count']);
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
        $user->setPassword('');
        UsersDAO::save($user);

        $this->login($user);
    }
}
