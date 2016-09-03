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
        $r = new Request(array(
            'username' => Utils::CreateRandomString(),
            'password' => Utils::CreateRandomString(),
            'email' => Utils::CreateRandomString().'@'.Utils::CreateRandomString().'.com',
            'permission_key' => UserController::$permissionKey
        ));

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
        $r = new Request(array(
            'username' => Utils::CreateRandomString(),
            'password' => Utils::CreateRandomString(),
            'email' => Utils::CreateRandomString().'@'.Utils::CreateRandomString().'.com',
            'permission_key' => UserController::$permissionKey
        ));

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
        $r = new Request(array(
            'username' => Utils::CreateRandomString(),
            'password' => Utils::CreateRandomString(),
            'email' => Utils::CreateRandomString().'@'.Utils::CreateRandomString().'.com',
            'permission_key' => UserController::$permissionKey
        ));

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
        $r = new Request(array(
            'username' => Utils::CreateRandomString(),
            'email' => Utils::CreateRandomString().'@'.Utils::CreateRandomString().'.com',
            'permission_key' => UserController::$permissionKey
        ));

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
        $r = new Request(array(
            'username' => Utils::CreateRandomString(),
            'password' => Utils::CreateRandomString(),
            'permission_key' => UserController::$permissionKey
        ));

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
        $r = new Request(array(
            'password' => Utils::CreateRandomString(),
            'email' => Utils::CreateRandomString().'@'.Utils::CreateRandomString().'.com',
            'permission_key' => UserController::$permissionKey
        ));

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
        $r = new Request(array(
            'username' => 'ínvalid username',
            'password' => Utils::CreateRandomString(),
            'email' => Utils::CreateRandomString().'@'.Utils::CreateRandomString().'.com',
            'permission_key' => UserController::$permissionKey
        ));

        // Call API
        $response = UserController::apiCreate($r);
    }

    /**
     * Admin can verify users only with username
     */
    public function testUsernameVerificationByAdmin() {
        // User to be verified
        $user = UserFactory::createUser(null, null, null, false /*not verified*/);

        // Admin will verify $user
        $admin = UserFactory::createAdminUser();

        // Call api using admin
        $response = UserController::apiVerifyEmail(new Request(array(
            'auth_token' => self::login($admin),
            'usernameOrEmail' => $user->username
        )));

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
        $response = UserController::apiVerifyEmail(new Request(array(
            'auth_token' => self::login($admin),
            'usernameOrEmail' => Utils::CreateRandomString()
        )));
    }

    /**
     * Normal user trying to verify herself through the admin path
     *
     * @expectedException ForbiddenAccessException
     */
    public function testUsernameVerificationByAdminNotAdmin() {
        // User to be verified
        $user = UserFactory::createUser(null, null, null, false /*not verified*/);

        // Another user will try to verify $user
        $user2 = UserFactory::createUser();

        // Call api using admin
        $response = UserController::apiVerifyEmail(new Request(array(
            'auth_token' => self::login($user2),
            'usernameOrEmail' => $user->username
        )));
    }

    /**
     * Normal user trying to backfill mailing list
     *
     * @expectedException ForbiddenAccessException
     */
    public function testMailingListBackfillNotAdmin() {
        $enableSendyInThisScope = new AutoEnableFlag(UserController::$enableSendyOverride);

        $user = UserFactory::createUser();

        $response = UserController::apiMailingListBackfill(new Request(array(
            'auth_token' => self::login($user)
        )));
    }

    /**
     * Test a user not in the mailing list (recently created) is backfilled
     * into Sendy.
     */
    public function testMailingtListBackfill() {
        $enableSendyInThisScope = new AutoEnableFlag(UserController::$enableSendyOverride);

        $userUnregistered = UserFactory::createUser();

        $urlHelperMock = $this->getMock('UrlHelper', array('fetchUrl'));
        $urlHelperMock->expects($this->atLeastOnce())
            ->method('fetchUrl')
            ->will($this->returnValue('1'));

        UserController::setUrlHelper($urlHelperMock);

        $response = UserController::apiMailingListBackfill(new Request(array(
            'auth_token' => self::login(UserFactory::createAdminUser())
        )));

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(true, $response['users'][$userUnregistered->username]);
    }

    /**
     * Test only verified users are backfilled into Sendy
     */
    public function testMailingListBackfillOnlyVerified() {
        $enableSendyInThisScope = new AutoEnableFlag(UserController::$enableSendyOverride);

        $userNotVerified = UserFactory::createUser(null /*username*/, null /*password*/, null /*email*/, false /*verified*/);

        $urlHelperMock = $this->getMock('UrlHelper', array('fetchUrl'));
        $urlHelperMock->expects($this->atLeastOnce())
            ->method('fetchUrl')
            ->will($this->returnValue('1'));

        UserController::setUrlHelper($urlHelperMock);

        $response = UserController::apiMailingListBackfill(new Request(array(
            'auth_token' => self::login(UserFactory::createAdminUser())
        )));

        // Check user was not added into the mailing list
        $this->assertEquals('ok', $response['status']);
        $this->assertArrayNotHasKey($userNotVerified->username, $response['users']);
    }
}
