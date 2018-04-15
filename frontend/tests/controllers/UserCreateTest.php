<?php

/**
 * CreateUserTest
 *
 * @author joemmanuel
 */

class CreateUserTest extends OmegaupTestCase {
    /**
     * Creates an omegaup user happily :)
     */
    public function testCreateUserPositive() {
        // Inflate request
        UserController::$permissionKey = uniqid();
        $r = new Request([
            'username' => Utils::CreateRandomString(),
            'password' => Utils::CreateRandomString(),
            'email' => Utils::CreateRandomString().'@'.Utils::CreateRandomString().'.com',
            'permission_key' => UserController::$permissionKey
        ]);

        // Call API
        $response = UserController::apiCreate($r);

        // Check response
        $this->assertEquals('ok', $response['status']);

        // Verify DB
        $user = UsersDAO::FindByUsername($r['username']);
        $this->assertNotNull($user);

        // Verify that users are opt'd out of the recruitment after being created
        $this->assertNull($user->recruitment_optin);

        // Verify users are not in mailing list by default
        $this->assertEquals(0, $user->in_mailing_list);
    }

    /**
     * Try to create 2 users with same username, should fail.
     *
     * @expectedException DuplicatedEntryInDatabaseException
     */
    public function testDuplicatedUsernames() {
        UserController::$permissionKey = uniqid();

        // Inflate request
        $r = new Request([
            'username' => Utils::CreateRandomString(),
            'password' => Utils::CreateRandomString(),
            'email' => Utils::CreateRandomString().'@'.Utils::CreateRandomString().'.com',
            'permission_key' => UserController::$permissionKey
        ]);

        // Call API
        $response = UserController::apiCreate($r);

        // Randomize email again
        $r['email'] = Utils::CreateRandomString().'@'.Utils::CreateRandomString().'.com';

        // Call api
        $response = UserController::apiCreate($r);
    }

    /**
     * Test create 2 users with same email (diff username) should fail
     *
     * @expectedException DuplicatedEntryInDatabaseException
     */
    public function testDuplicatedEmails() {
        UserController::$permissionKey = uniqid();

        // Inflate request
        $r = new Request([
            'username' => Utils::CreateRandomString(),
            'password' => Utils::CreateRandomString(),
            'email' => Utils::CreateRandomString().'@'.Utils::CreateRandomString().'.com',
            'permission_key' => UserController::$permissionKey
        ]);

        // Call API
        $response = UserController::apiCreate($r);

        // Randomize email again
        $r['username'] = Utils::CreateRandomString();

        // Call api
        $response = UserController::apiCreate($r);
    }

    /**
     * Creating a user without password
     *
     * @expectedException InvalidParameterException
     */
    public function testNoPassword() {
        UserController::$permissionKey = uniqid();

        // Inflate request
        $r = new Request([
            'username' => Utils::CreateRandomString(),
            'email' => Utils::CreateRandomString().'@'.Utils::CreateRandomString().'.com',
            'permission_key' => UserController::$permissionKey
        ]);

        // Call API
        $response = UserController::apiCreate($r);
    }

    /**
     * Creating a user without email
     *
     * @expectedException InvalidParameterException
     */
    public function testNoEmail() {
        UserController::$permissionKey = uniqid();

        // Inflate request
        $r = new Request([
            'username' => Utils::CreateRandomString(),
            'password' => Utils::CreateRandomString(),
            'permission_key' => UserController::$permissionKey
        ]);

        // Call API
        $response = UserController::apiCreate($r);
    }

    /**
     * Create a user without username...
     *
     * @expectedException InvalidParameterException
     */
    public function testNoUser() {
        UserController::$permissionKey = uniqid();

        // Inflate request
        $r = new Request([
            'password' => Utils::CreateRandomString(),
            'email' => Utils::CreateRandomString().'@'.Utils::CreateRandomString().'.com',
            'permission_key' => UserController::$permissionKey
        ]);

        // Call API
        UserController::apiCreate($r);
    }

    /**
     * Tests Create User API happy path excercising the httpEntryPoint
     */
    public function testCreateUserPositiveViahttpEntryPoint() {
        UserController::$permissionKey = uniqid();

        // Set context
        $_REQUEST['username'] = Utils::CreateRandomString();
        $_REQUEST['password'] = Utils::CreateRandomString();
        $_REQUEST['email'] = Utils::CreateRandomString().'@'.Utils::CreateRandomString().'.com';
        $_REQUEST['permission_key'] = UserController::$permissionKey;

        // Override session_start, phpunit doesn't like it, but we still validate that it is called once
        $this->mockSessionManager();

        // Call api
        $_SERVER['REQUEST_URI'] = '/api/user/create';
        $response = json_decode(ApiCallerMock::httpEntryPoint(), true);

        $this->assertEquals('ok', $response['status']);

        // Verify DB
        $user = UsersDAO::FindByUsername($_REQUEST['username']);
        $this->assertNotNull($user);
    }

    /**
     * Tests usernames with invalid chars. Exception is expected
     *
     * @expectedException InvalidParameterException
     */
    public function testUsernameWithInvalidChars() {
        UserController::$permissionKey = uniqid();

        // Inflate request
        $r = new Request([
            'username' => 'ínvalid username',
            'password' => Utils::CreateRandomString(),
            'email' => Utils::CreateRandomString().'@'.Utils::CreateRandomString().'.com',
            'permission_key' => UserController::$permissionKey
        ]);

        // Call API
        $response = UserController::apiCreate($r);
    }

    /**
     * Admin can verify users only with username
     */
    public function testUsernameVerificationByAdmin() {
        // User to be verified
        $user = UserFactory::createUser(new UserParams(['verify' => false]));

        // Admin will verify $user
        $admin = UserFactory::createAdminUser();

        // Call api using admin
        $adminLogin = self::login($admin);
        $response = UserController::apiVerifyEmail(new Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $user->username,
        ]));

        // Get user from db again to pick up verification changes
        $userdb = UsersDAO::FindByUsername($user->username);

        $this->assertEquals(1, $userdb->verified);
        $this->assertEquals('ok', $response['status']);
    }

    /**
     * Admin can verify users only with username
     * Testing invalid username
     *
     * @expectedException NotFoundException
     */
    public function testUsernameVerificationByAdminInvalidUsername() {
        // Admin will verify $user
        $admin = UserFactory::createAdminUser();

        // Call api using admin
        $adminLogin = self::login($admin);
        $response = UserController::apiVerifyEmail(new Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => Utils::CreateRandomString(),
        ]));
    }

    /**
     * Normal user trying to verify herself through the admin path
     *
     * @expectedException ForbiddenAccessException
     */
    public function testUsernameVerificationByAdminNotAdmin() {
        // User to be verified
        $user = UserFactory::createUser(new UserParams(['verify' => false]));

        // Another user will try to verify $user
        $user2 = UserFactory::createUser();

        // Call api using admin
        $login = self::login($user2);
        $response = UserController::apiVerifyEmail(new Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $user->username,
        ]));
    }

    /**
     * Normal user trying to backfill mailing list
     *
     * @expectedException ForbiddenAccessException
     */
    public function testMailingListBackfillNotAdmin() {
        $user = UserFactory::createUser();

        $login = self::login($user);
        $response = UserController::apiMailingListBackfill(new Request([
            'auth_token' => $login->auth_token,
        ]));
    }

    /**
     * Test a user not in the mailing list (recently created) is backfilled
     * into Sendy.
     */
    public function testMailingtListBackfill() {
        $userUnregistered = UserFactory::createUser();

        $urlHelperMock = $this->getMockBuilder('UrlHelper')->getMock();
        $urlHelperMock->expects($this->atLeastOnce())
            ->method('fetchUrl')
            ->will($this->returnValue(UserController::SENDY_SUCCESS));

        UserController::$urlHelper = $urlHelperMock;

        $adminLogin = self::login(UserFactory::createAdminUser());
        $response = UserController::apiMailingListBackfill(new Request([
            'auth_token' => $adminLogin->auth_token,
        ]));

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(true, $response['users'][$userUnregistered->username]);
    }

    /**
     * Test only verified users are backfilled into Sendy
     */
    public function testMailingListBackfillOnlyVerified() {
        $userNotVerified = UserFactory::createUser(new UserParams(['verify' => false]));

        $urlHelperMock = $this->getMockBuilder('UrlHelper')->getMock();
        $urlHelperMock->expects($this->atLeastOnce())
            ->method('fetchUrl')
            ->will($this->returnValue(UserController::SENDY_SUCCESS));

        UserController::$urlHelper = $urlHelperMock;

        $adminLogin = self::login(UserFactory::createAdminUser());
        $response = UserController::apiMailingListBackfill(new Request([
            'auth_token' => $adminLogin->auth_token,
        ]));

        // Check user was not added into the mailing list
        $this->assertEquals('ok', $response['status']);
        $this->assertArrayNotHasKey($userNotVerified->username, $response['users']);
    }
}
