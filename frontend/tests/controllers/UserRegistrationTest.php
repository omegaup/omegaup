<?php
/**
 * Testing new user special cases
 */
class UserRegistrationTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     *  Scenario:
     *      user A creates a new native account :
     *          username=A email=A@example.com
     *
     *      user B logs in with fb/google:
     *          email=A@gmail.com
     */
    public function testUserNameCollision() {
        $salt = \OmegaUp\Time::get();

        // Test users should not exist
        $this->assertNull(\OmegaUp\DAO\Users::FindByUsername("A{$salt}"));
        $this->assertNull(\OmegaUp\DAO\Users::FindByUsername("A{$salt}1"));
        $this->assertNull(\OmegaUp\DAO\Users::FindByUsername("A{$salt}2"));

        ob_start();
        try {
            // Create collision
            \OmegaUp\Controllers\Session::loginViaGoogleEmail(
                "A{$salt}@isp1.com"
            );
        } catch (\OmegaUp\Exceptions\ExitException $e) {
            // This is expected.
        }
        try {
            \OmegaUp\Controllers\Session::loginViaGoogleEmail(
                "A{$salt}@isp2.com"
            );
        } catch (\OmegaUp\Exceptions\ExitException $e) {
            // This is expected.
        }
        try {
            \OmegaUp\Controllers\Session::loginViaGoogleEmail(
                "A{$salt}@isp3.com"
            );
        } catch (\OmegaUp\Exceptions\ExitException $e) {
            // This is expected.
        }
        ob_end_clean();

        $this->assertNotNull(\OmegaUp\DAO\Users::FindByUsername("A{$salt}"));
        $this->assertNotNull(\OmegaUp\DAO\Users::FindByUsername("A{$salt}1"));
        $this->assertNotNull(\OmegaUp\DAO\Users::FindByUsername("A{$salt}2"));
    }

    /**
     * User logged via google, try log in with native mode
     */
    public function testUserLoggedViaGoogleAndThenNativeMode() {
        $time = \OmegaUp\Time::get();
        $username = "X{$time}";
        $password = \OmegaUp\Test\Utils::createRandomString();

        try {
            \OmegaUp\Controllers\Session::loginViaGoogleEmail(
                "{$username}@isp.com"
            );
        } catch (\OmegaUp\Exceptions\ExitException $e) {
            // This is expected.
        }

        $identity = \OmegaUp\DAO\Identities::findByUsername($username);

        // Users logged via google, facebook
        $this->assertNull($identity->password);

        // Inflate request
        \OmegaUp\Controllers\User::$permissionKey = uniqid();

        try {
            // Try to create new user
            \OmegaUp\Controllers\User::apiCreate(new \OmegaUp\Request([
                'username' => $username,
                'password' => $password,
                'email' => "{$username}@isp.com",
                'permission_key' => \OmegaUp\Controllers\User::$permissionKey
            ]));
            $this->fail(
                'User should have not been able to be created because the email already exists in the data base'
            );
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            $this->assertSame('mailInUse', $e->getMessage());
        }
    }

    public function testGitHubLoginNewUser(): void {
        $email = 'newuser' . \OmegaUp\Time::get() . '@github.com';

        ob_start();
        try {
            \OmegaUp\Controllers\Session::loginViaGitHubEmail(
                $email,
                'Test User'
            );
        } catch (\OmegaUp\Exceptions\ExitException $e) {
            //expected.
        }
        ob_end_clean();

        $identity = \OmegaUp\DAO\Identities::findByEmail($email);
        $this->assertNotNull($identity);
    }

    public function testGitHubLoginExistingUser(): void {
        ['identity' => $identity, 'user' => $user] = \OmegaUp\Test\Factories\User::createUser();
        $this->assertNotNull($identity->username);
        $this->assertNotNull($identity->user_id);
        $this->assertNotNull($user->main_email_id);

        $email = \OmegaUp\DAO\Emails::getByPK(intval($user->main_email_id));
        $this->assertNotNull($email);
        $emailAddress = strval($email->email);

        ob_start();
        try {
            \OmegaUp\Controllers\Session::loginViaGitHubEmail(
                $emailAddress,
                'GitHub User'
            );
        } catch (\OmegaUp\Exceptions\ExitException $e) {
            //expected.
        }
        ob_end_clean();

        $githubIdentity = \OmegaUp\DAO\Identities::findByEmail($emailAddress);
        $this->assertNotNull($githubIdentity);
        $this->assertSame($identity->identity_id, $githubIdentity->identity_id);
    }

    public function testGitHubCSRFValidation(): void {
        $originalSessionManager = \OmegaUp\Controllers\Session::getSessionManagerInstance();
        /** @var \OmegaUp\SessionManager&\PHPUnit\Framework\MockObject\MockObject */
        $sessionManagerMock = $this->getMockBuilder(
            \OmegaUp\SessionManager::class
        )
            ->onlyMethods(['getCookie'])
            ->getMock();
        $sessionManagerMock->expects($this->once())
            ->method('getCookie')
            ->with('github_oauth_state')
            ->willReturn('expected-state');

        \OmegaUp\Controllers\Session::setSessionManagerForTesting(
            $sessionManagerMock
        );

        try {
            $this->expectException(
                \OmegaUp\Exceptions\InvalidParameterException::class
            );
            $this->expectExceptionMessage('loginGitHubInvalidCSRFToken');
            \OmegaUp\Controllers\Session::loginViaGithub(
                'dummy-code',
                'different-state'
            );
        } finally {
            \OmegaUp\Controllers\Session::setSessionManagerForTesting(
                $originalSessionManager
            );
        }
    }

    /**
     * Test GitHub login creates new identity when email doesn't exist
     */
    public function testGitHubLoginCreatesNewIdentity(): void {
        $email = 'newgithubuser' . uniqid() . '@example.com';
        $name = 'GitHub Test User';

        ob_start();
        try {
            \OmegaUp\Controllers\Session::loginViaGitHubEmail($email, $name);
        } catch (\OmegaUp\Exceptions\ExitException $e) {
            // Expected
        }
        ob_end_clean();

        $identity = \OmegaUp\DAO\Identities::findByEmail($email);
        $this->assertNotNull($identity);
        $this->assertStringContainsString($name, $identity->name);
    }

    /**
     * Test GitHub login with existing user associates properly
     */
    public function testGitHubLoginExistingUserAssociation(): void {
        ['identity' => $identity, 'user' => $user] = \OmegaUp\Test\Factories\User::createUser();
        $email = \OmegaUp\DAO\Emails::getByPK($user->main_email_id);

        ob_start();
        try {
            \OmegaUp\Controllers\Session::loginViaGitHubEmail(
                $email->email,
                'GitHub Name'
            );
        } catch (\OmegaUp\Exceptions\ExitException $e) {
            // Expected
        }
        ob_end_clean();

        $githubIdentity = \OmegaUp\DAO\Identities::findByEmail($email->email);
        $this->assertNotNull($githubIdentity);
        $this->assertEquals(
            $identity->identity_id,
            $githubIdentity->identity_id
        );
    }

    /**
     * Test GitHub state management in login payload
     */
    public function testGitHubStateManagementInLoginPage(): void {
        $r = new \OmegaUp\Request([]);

        $response = \OmegaUp\Controllers\User::getLoginDetailsForTypeScript($r);

        $this->assertArrayHasKey(
            'githubClientId',
            $response['templateProperties']['payload']
        );
        $this->assertArrayHasKey(
            'githubState',
            $response['templateProperties']['payload']
        );
        $this->assertIsString(
            $response['templateProperties']['payload']['githubState']
        );
    }

    /**
     * Test GitHub CSRF validation catches mismatched states
     */
    public function testGitHubCSRFMismatchedStateThrowsException(): void {
        $originalSessionManager = \OmegaUp\Controllers\Session::getSessionManagerInstance();
        /** @var \OmegaUp\SessionManager&\PHPUnit\Framework\MockObject\MockObject */
        $sessionManagerMock = $this->getMockBuilder(
            \OmegaUp\SessionManager::class
        )
            ->onlyMethods(['getCookie'])
            ->getMock();
        $sessionManagerMock->expects($this->once())
            ->method('getCookie')
            ->with('github_oauth_state')
            ->willReturn('stored-state');

        \OmegaUp\Controllers\Session::setSessionManagerForTesting(
            $sessionManagerMock
        );

        try {
            $this->expectException(
                \OmegaUp\Exceptions\InvalidParameterException::class
            );
            $this->expectExceptionMessage('loginGitHubInvalidCSRFToken');
            \OmegaUp\Controllers\Session::loginViaGithub(
                'dummy-code',
                'different-state'
            );
        } finally {
            \OmegaUp\Controllers\Session::setSessionManagerForTesting(
                $originalSessionManager
            );
        }
    }

    /**
     * Test GitHub login with missing stored state throws exception
     */
    public function testGitHubLoginWithMissingStoredStateThrowsException(): void {
        $originalSessionManager = \OmegaUp\Controllers\Session::getSessionManagerInstance();
        /** @var \OmegaUp\SessionManager&\PHPUnit\Framework\MockObject\MockObject */
        $sessionManagerMock = $this->getMockBuilder(
            \OmegaUp\SessionManager::class
        )
            ->onlyMethods(['getCookie'])
            ->getMock();
        $sessionManagerMock->expects($this->once())
            ->method('getCookie')
            ->with('github_oauth_state')
            ->willReturn(null);

        \OmegaUp\Controllers\Session::setSessionManagerForTesting(
            $sessionManagerMock
        );

        try {
            $this->expectException(
                \OmegaUp\Exceptions\InvalidParameterException::class
            );
            $this->expectExceptionMessage('loginGitHubInvalidCSRFToken');
            \OmegaUp\Controllers\Session::loginViaGithub(
                'dummy-code',
                'any-state'
            );
        } finally {
            \OmegaUp\Controllers\Session::setSessionManagerForTesting(
                $originalSessionManager
            );
        }
    }

    /**
     * User logged via google, try log in with native mode, and
     * different username
     */
    public function testUserLoggedViaGoogleAndThenNativeModeWithDifferentUsername() {
        $time = \OmegaUp\Time::get();
        $username = "Y{$time}";
        $email = "{$username}@isp.com";

        try {
            \OmegaUp\Controllers\Session::loginViaGoogleEmail($email);
        } catch (\OmegaUp\Exceptions\ExitException $e) {
            // This is expected.
        }

        $user = \OmegaUp\DAO\Users::FindByUsername($username);
        $identity = \OmegaUp\DAO\Identities::FindByUserId($user->user_id);
        $email_user = \OmegaUp\DAO\Emails::getByPK($user->main_email_id);

        // Asserts that user has the initial username and email
        $this->assertSame($identity->username, $username);
        $this->assertSame($email, $email_user->email);

        // Inflate request
        \OmegaUp\Controllers\User::$permissionKey = uniqid();
        $r = new \OmegaUp\Request([
            'username' => "Z{$username}",
            'password' => \OmegaUp\Test\Utils::createRandomString(),
            'email' => $email,
            'permission_key' => \OmegaUp\Controllers\User::$permissionKey
        ]);

        try {
            // Call API
            \OmegaUp\Controllers\User::apiCreate($r);
            $this->fail(
                'User should have not been able to be created because the email already exists in the data base'
            );
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            $this->assertSame('mailInUse', $e->getMessage());
        }
    }

     /**
     * user13 logged in and a parental Token is generated
     *
     */
    public function testUser13ToGenerateParentalTokenAtTimeOfRegistration() {
        // Verify that the token is generated.
        $under13BirthDateTimestamp = strtotime('-10 years');
        $randomString = \OmegaUp\Test\Utils::createRandomString();
        \OmegaUp\Controllers\User::apiCreate(
            new \OmegaUp\Request([
                'username' => $randomString,
                'password' => $randomString,
                'parent_email' => "{$randomString}@{$randomString}.com",
                'birth_date' => $under13BirthDateTimestamp,
            ]),
            $this->assertNotNull($under13BirthDateTimestamp)
        );
        $response = \OmegaUp\DAO\Users::FindByUsername($randomString);

        $this->assertNotNull($response->parental_verification_token);
    }

    /**
     * User logged in and a parental Token is not generated
     *
     */
    public function testUserDoToGenerateParentalTokenAtTimeOfRegistration() {
        // Verify that the token is not generated.
        $over13BirthDateTimestamp = strtotime('-15 years');
        $randomString = \OmegaUp\Test\Utils::createRandomString();
        \OmegaUp\Controllers\User::apiCreate(
            new \OmegaUp\Request([
                 'username' => $randomString,
                 'password' => $randomString,
                 'email' => "{$randomString}@{$randomString}.com",
                 'birth_date' => $over13BirthDateTimestamp,
            ]),
            $this->assertNotNull($over13BirthDateTimestamp)
        );

        $response = \OmegaUp\DAO\Users::FindByUsername($randomString);

        $this->assertNull($response->parental_verification_token);
    }

    /**
     * User under 13 creates an account with the function in the factory, where
     * the account does not register an email
     */
    public function testUserUnder13CreatesAnAccount() {
        $defaultDate = strtotime('2022-01-01T00:00:00Z');
        \OmegaUp\Time::setTimeForTesting($defaultDate);
        // Creates a 10 years-old user
        ['user' => $user] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams([
                'birthDate' => strtotime('2012-01-01T00:00:00Z'),
            ]),
        );

        $this->assertNull($user->main_email_id);
        $this->assertNotNull($user->parental_verification_token);
        $this->assertNotNull($user->parent_email_verification_initial);
        $this->assertNotNull($user->parent_email_verification_deadline);

        // Creates a normal user
        ['user' => $user] = \OmegaUp\Test\Factories\User::createUser();

        $this->assertNotNull($user->main_email_id);
        $this->assertNull($user->parental_verification_token);
        $this->assertNull($user->parent_email_verification_initial);
        $this->assertNull($user->parent_email_verification_deadline);
    }

    /**
     *  User registration fails due to both email address were provided
     *
     */
    public function testUserParentalTokenNotGeneratedDueInvalidParameters() {
        $over13BirthDateTimestamp = strtotime('-15 years');
        $randomString = \OmegaUp\Test\Utils::createRandomString();
        try {
            \OmegaUp\Controllers\User::apiCreate(
                new \OmegaUp\Request([
                    'username' => $randomString,
                    'password' => $randomString,
                    'email' => "{$randomString}@{$randomString}.com",
                    'parent_email' => "{$randomString}@{$randomString}.com",
                    'birth_date' => $over13BirthDateTimestamp,
                ])
            );
            $this->fail(
                'User should have not been able to be created because it is not valid provide both email and parent_email'
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterInvalid', $e->getMessage());
        }
    }

    /**
     * User 13 and its account link to the parent account
     * on verification of parental token
     */
    public function testUse13LinkedToParentAccountWhenTokenVerificationDone() {
        $defaultDate = strtotime('2022-01-01T00:00:00Z');
        \OmegaUp\Time::setTimeForTesting($defaultDate);
        // Create a 10 years-old user
        ['user' => $user] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams([
                'birthDate' => strtotime('2012-01-01T00:00:00Z'),
            ]),
        );
        [
            'user' => $parentUser,
            'identity' => $parentIdentity,
        ] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($parentIdentity);

        $payload = \OmegaUp\Controllers\User::getVerificationParentalTokenDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'parental_verification_token' => $user->parental_verification_token,
            ])
        )['templateProperties']['payload'];

        $this->assertTrue($payload['hasParentalVerificationToken']);

        $updatedUser = \OmegaUp\DAO\Users::getByPK($user->user_id);

        // Assert both accounts are linked by email_id
        $this->assertSame(
            $updatedUser->parent_email_id,
            $parentUser->main_email_id
        );
    }
}
