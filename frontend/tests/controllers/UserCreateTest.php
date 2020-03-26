<?php

/**
 * UserCreateTest
 *
 * @author joemmanuel
 */

class UserCreateTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Creates an omegaup user happily :)
     */
    public function testCreateUserPositive() {
        // Inflate request
        \OmegaUp\Controllers\User::$permissionKey = uniqid();
        $r = new \OmegaUp\Request([
            'username' => \OmegaUp\Test\Utils::createRandomString(),
            'password' => \OmegaUp\Test\Utils::createRandomString(),
            'email' => \OmegaUp\Test\Utils::createRandomString() . '@' . \OmegaUp\Test\Utils::createRandomString() . '.com',
            'permission_key' => \OmegaUp\Controllers\User::$permissionKey
        ]);

        // Call API
        $response = \OmegaUp\Controllers\User::apiCreate($r);

        // Check response
        $this->assertEquals($r['username'], $response['username']);

        // Verify DB
        $user = \OmegaUp\DAO\Users::FindByUsername($r['username']);
        $this->assertNotNull($user);

        // Verify users are not in mailing list by default
        $this->assertEquals(0, $user->in_mailing_list);
    }

    /**
     * Creates an omegaup user then tries to create it again.
     */
    public function testCreateUserIdempotent() {
        // Inflate request
        \OmegaUp\Controllers\User::$permissionKey = uniqid();
        $r = new \OmegaUp\Request([
            'username' => \OmegaUp\Test\Utils::createRandomString(),
            'password' => \OmegaUp\Test\Utils::createRandomString(),
            'email' => \OmegaUp\Test\Utils::createRandomString() . '@' . \OmegaUp\Test\Utils::createRandomString() . '.com',
            'permission_key' => \OmegaUp\Controllers\User::$permissionKey
        ]);

        // Call API twice.
        $response = \OmegaUp\Controllers\User::apiCreate($r);
        $this->assertEquals($r['username'], $response['username']);

        $response = \OmegaUp\Controllers\User::apiCreate($r);
        $this->assertEquals($r['username'], $response['username']);

        $r['password'] = 'a wrong password';
        try {
            \OmegaUp\Controllers\User::apiCreate($r);
            $this->fail('User creation should have failed');
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            $this->assertEquals('mailInUse', $e->getMessage());
        }
    }

    /**
     * Try to create 2 users with same username, should fail.
     */
    public function testDuplicatedUsernames() {
        \OmegaUp\Controllers\User::$permissionKey = uniqid();

        $r = new \OmegaUp\Request([
            'username' => \OmegaUp\Test\Utils::createRandomString(),
            'password' => \OmegaUp\Test\Utils::createRandomString(),
            'email' => \OmegaUp\Test\Utils::createRandomString() . '@' . \OmegaUp\Test\Utils::createRandomString() . '.com',
            'permission_key' => \OmegaUp\Controllers\User::$permissionKey
        ]);

        // Call API
        \OmegaUp\Controllers\User::apiCreate($r);

        // Randomize email again
        $r['email'] = \OmegaUp\Test\Utils::createRandomString() . '@' . \OmegaUp\Test\Utils::createRandomString() . '.com';

        try {
            \OmegaUp\Controllers\User::apiCreate($r);
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            $this->assertEquals('usernameInUse', $e->getMessage());
        }
    }

    /**
     * Test create 2 users with same email (diff username) should fail
     */
    public function testDuplicatedEmails() {
        \OmegaUp\Controllers\User::$permissionKey = uniqid();

        // Inflate request
        $r = new \OmegaUp\Request([
            'username' => \OmegaUp\Test\Utils::createRandomString(),
            'password' => \OmegaUp\Test\Utils::createRandomString(),
            'email' => \OmegaUp\Test\Utils::createRandomString() . '@' . \OmegaUp\Test\Utils::createRandomString() . '.com',
            'permission_key' => \OmegaUp\Controllers\User::$permissionKey
        ]);

        // Call API
        $response = \OmegaUp\Controllers\User::apiCreate($r);

        // Randomize email again
        $r['username'] = \OmegaUp\Test\Utils::createRandomString();

        try {
            \OmegaUp\Controllers\User::apiCreate($r);
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            $this->assertEquals('mailInUse', $e->getMessage());
        }
    }

    /**
     * Creating a user without password
     */
    public function testNoPassword() {
        \OmegaUp\Controllers\User::$permissionKey = uniqid();

        try {
            \OmegaUp\Controllers\User::apiCreate(new \OmegaUp\Request([
                'username' => \OmegaUp\Test\Utils::createRandomString(),
                'email' => \OmegaUp\Test\Utils::createRandomString() . '@' . \OmegaUp\Test\Utils::createRandomString() . '.com',
                'permission_key' => \OmegaUp\Controllers\User::$permissionKey
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals('parameterStringTooShort', $e->getMessage());
            $this->assertEquals('password', $e->parameter);
        }
    }

    /**
     * Creating a user without email
     */
    public function testNoEmail() {
        \OmegaUp\Controllers\User::$permissionKey = uniqid();

        try {
            \OmegaUp\Controllers\User::apiCreate(new \OmegaUp\Request([
                'username' => \OmegaUp\Test\Utils::createRandomString(),
                'password' => \OmegaUp\Test\Utils::createRandomString(),
                'permission_key' => \OmegaUp\Controllers\User::$permissionKey
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals('parameterEmpty', $e->getMessage());
            $this->assertEquals('email', $e->parameter);
        }
    }

    /**
     * Create a user without username...
     */
    public function testNoUsername() {
        \OmegaUp\Controllers\User::$permissionKey = uniqid();

        try {
            \OmegaUp\Controllers\User::apiCreate(new \OmegaUp\Request([
                'password' => \OmegaUp\Test\Utils::createRandomString(),
                'email' => \OmegaUp\Test\Utils::createRandomString() . '@' . \OmegaUp\Test\Utils::createRandomString() . '.com',
                'permission_key' => \OmegaUp\Controllers\User::$permissionKey
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals('parameterEmpty', $e->getMessage());
            $this->assertEquals('username', $e->parameter);
        }
    }

    /**
     * Tests Create User API happy path excercising the httpEntryPoint
     */
    public function testCreateUserPositiveViahttpEntryPoint() {
        \OmegaUp\Controllers\User::$permissionKey = uniqid();

        // Set context
        $_REQUEST['username'] = \OmegaUp\Test\Utils::createRandomString();
        $_REQUEST['password'] = \OmegaUp\Test\Utils::createRandomString();
        $_REQUEST['email'] = \OmegaUp\Test\Utils::createRandomString() . '@' . \OmegaUp\Test\Utils::createRandomString() . '.com';
        $_REQUEST['permission_key'] = \OmegaUp\Controllers\User::$permissionKey;

        // Override session_start, phpunit doesn't like it, but we still validate that it is called once
        $this->mockSessionManager();

        // Call api
        $_SERVER['REQUEST_URI'] = '/api/user/create';
        $response = json_decode(
            \OmegaUp\Test\ApiCallerMock::httpEntryPoint(),
            true
        );

        $this->assertEquals('ok', $response['status']);

        // Verify DB
        $user = \OmegaUp\DAO\Users::FindByUsername($_REQUEST['username']);
        $this->assertNotNull($user);
    }

    /**
     * Tests usernames with invalid chars. Exception is expected
     */
    public function testUsernameWithInvalidChars() {
        \OmegaUp\Controllers\User::$permissionKey = uniqid();

        try {
            \OmegaUp\Controllers\User::apiCreate(new \OmegaUp\Request([
                'username' => 'ínvalid username',
                'password' => \OmegaUp\Test\Utils::createRandomString(),
                'email' => \OmegaUp\Test\Utils::createRandomString() . '@' . \OmegaUp\Test\Utils::createRandomString() . '.com',
                'permission_key' => \OmegaUp\Controllers\User::$permissionKey
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals('parameterInvalidAlias', $e->getMessage());
            $this->assertEquals('username', $e->parameter);
        }
    }

    /**
     * Tests usernames with invalid chars. Exception is expected
     */
    public function testUsernameWithInvalidChar() {
        \OmegaUp\Controllers\User::$permissionKey = uniqid();

        try {
            \OmegaUp\Controllers\User::apiCreate(new \OmegaUp\Request([
                'username' => 'invalid:username',
                'password' => \OmegaUp\Test\Utils::createRandomString(),
                'email' => \OmegaUp\Test\Utils::createRandomString() . '@' . \OmegaUp\Test\Utils::createRandomString() . '.com',
                'permission_key' => \OmegaUp\Controllers\User::$permissionKey,
            ]));

            $this->fail('Expected because of the invalid group name');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals('parameterInvalidAlias', $e->getMessage());
            $this->assertEquals('username', $e->parameter);
        }
    }

    /**
     * Admin can verify users only with username
     */
    public function testUsernameVerificationByAdmin() {
        // User to be verified
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(['verify' => false])
        );

        // Admin will verify $user
        ['user' => $admin, 'identity' => $identityAdmin] = \OmegaUp\Test\Factories\User::createAdminUser();

        // Call api using admin
        $adminLogin = self::login($identityAdmin);
        $response = \OmegaUp\Controllers\User::apiVerifyEmail(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $identity->username,
        ]));

        // Get user from db again to pick up verification changes
        $userdb = \OmegaUp\DAO\Users::FindByUsername($identity->username);

        $this->assertEquals(1, $userdb->verified);
        $this->assertEquals('ok', $response['status']);
    }

    /**
     * Admin can verify users only with username
     * Testing invalid username
     */
    public function testUsernameVerificationByAdminInvalidUsername() {
        // Admin will verify $user
        ['user' => $admin, 'identity' => $identityAdmin] = \OmegaUp\Test\Factories\User::createAdminUser();

        // Call api using admin
        $adminLogin = self::login($identityAdmin);
        try {
            \OmegaUp\Controllers\User::apiVerifyEmail(new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'usernameOrEmail' => \OmegaUp\Test\Utils::createRandomString(),
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            $this->assertEquals('userOrMailNotFound', $e->getMessage());
        }
    }

    /**
     * Normal user trying to verify herself through the admin path
     */
    public function testUsernameVerificationByAdminNotAdmin() {
        // User to be verified
        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(['verify' => false])
        );

        // Another user will try to verify $user
        ['user' => $user2, 'identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();

        // Call api using admin
        $login = self::login($identity2);
        try {
            \OmegaUp\Controllers\User::apiVerifyEmail(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'usernameOrEmail' => $identity->username,
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Normal user trying to backfill mailing list
     */
    public function testMailingListBackfillNotAdmin() {
        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        try {
            \OmegaUp\Controllers\User::apiMailingListBackfill(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Test a user not in the mailing list (recently created) is backfilled
     * into Sendy.
     */
    public function testMailingtListBackfill() {
        ['user' => $unregisteredUser, 'identity' => $unregisteredIdentity] = \OmegaUp\Test\Factories\User::createUser();

        $urlHelperMock = $this
            ->getMockBuilder('\\OmegaUp\\UrlHelper')
            ->getMock();
        $urlHelperMock->expects($this->atLeastOnce())
            ->method('fetchUrl')
            ->will(
                $this->returnValue(
                    \OmegaUp\Controllers\User::SENDY_SUCCESS
                )
            );

        \OmegaUp\Controllers\User::$urlHelper = $urlHelperMock;
        ['user' => $admin, 'identity' => $identityAdmin] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($identityAdmin);
        $response = \OmegaUp\Controllers\User::apiMailingListBackfill(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
        ]));

        $this->assertEquals(
            true,
            $response['users'][$unregisteredIdentity->username]
        );
    }

    /**
     * Test only verified users are backfilled into Sendy
     */
    public function testMailingListBackfillOnlyVerified() {
        ['user' => $userNotVerified, 'identity' => $identityNotVerified] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['verify' => false]
            )
        );

        $urlHelperMock = $this
            ->getMockBuilder('\\OmegaUp\\UrlHelper')
            ->getMock();
        $urlHelperMock->expects($this->atLeastOnce())
            ->method('fetchUrl')
            ->will(
                $this->returnValue(
                    \OmegaUp\Controllers\User::SENDY_SUCCESS
                )
            );

        \OmegaUp\Controllers\User::$urlHelper = $urlHelperMock;
        ['user' => $admin, 'identity' => $identityAdmin] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($identityAdmin);
        $response = \OmegaUp\Controllers\User::apiMailingListBackfill(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
        ]));

        // Check user was not added into the mailing list
        $this->assertArrayNotHasKey(
            $identityNotVerified->username,
            $response['users']
        );
    }
}
