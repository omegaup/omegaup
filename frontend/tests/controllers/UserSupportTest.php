<?php
/**
 * Test administrative tasks for support team
 */
class UserSupportTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Basic test for users with support role
     */
    public function testUserHasSupportRole() {
        ['identity' => $supportIdentity] = \OmegaUp\Test\Factories\User::createSupportUser();
        ['identity' => $mentorIdentity] = \OmegaUp\Test\Factories\User::createMentorIdentity();

        // Asserting that user belongs to the support group
        $this->assertTrue(
            \OmegaUp\Authorization::isSupportTeamMember(
                $supportIdentity
            )
        );

        // Asserting that user doesn't belong to the support group
        $this->assertFalse(
            \OmegaUp\Authorization::isSupportTeamMember(
                $mentorIdentity
            )
        );
    }

    /**
     * Tests user verification with support role
     */
    public function testVerifyUser() {
        // Support team member will verify $user
        [
            'identity' => $supportIdentity,
        ] = \OmegaUp\Test\Factories\User::createSupportUser();

        // Creates a user
        $email = \OmegaUp\Test\Utils::createRandomString() . '@mail.com';
        \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams([
                'email' => $email,
                'verify' => false,
            ])
        );

        // Call api using support team member
        $supportLogin = self::login($supportIdentity);

        $response = \OmegaUp\Controllers\User::apiExtraInformation(
            new \OmegaUp\Request([
                'auth_token' => $supportLogin->auth_token,
                'usernameOrEmail' => $email,
            ])
        );

        $this->assertFalse($response['verified']);

        // Call apiVerifyEmail
        \OmegaUp\Controllers\User::apiVerifyEmail(new \OmegaUp\Request([
            'auth_token' => $supportLogin->auth_token,
            'usernameOrEmail' => $email,
        ]));

        // Get user information to pick up verification changes
        $response = \OmegaUp\Controllers\User::apiExtraInformation(
            new \OmegaUp\Request([
                'auth_token' => $supportLogin->auth_token,
                'usernameOrEmail' => $email,
            ])
        );

        $this->assertTrue($response['verified']);
    }

    public function testVerifyUserViaUrl() {
        // Creates a user
        $email = \OmegaUp\Test\Utils::createRandomString() . '@mail.com';
        \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams([
                'email' => $email,
                'verify' => false,
            ])
        );

        $user = \OmegaUp\DAO\Users::findByEmail($email);
        $this->assertFalse($user->verified);

        $payload = \OmegaUp\Controllers\User::getLoginDetailsViaVerifyEmailForTypeScript(
            new \OmegaUp\Request([
                'id' => $user->verification_id,
            ])
        )['templateProperties']['payload'];

        $this->assertArrayHasKey('verifyEmailSuccessfully', $payload);
        $this->assertArrayNotHasKey('statusError', $payload);

        $user = \OmegaUp\DAO\Users::findByEmail($email);

        $this->assertTrue($user->verified);
    }

    public function testVerifyUserViaUrlWithWrongVerficationId() {
        // Creates a user
        $email = \OmegaUp\Test\Utils::createRandomString() . '@mail.com';
        \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams([
                'email' => $email,
                'verify' => false,
            ])
        );

        $user = \OmegaUp\DAO\Users::findByEmail($email);
        $this->assertFalse($user->verified);

        $payload = \OmegaUp\Controllers\User::getLoginDetailsViaVerifyEmailForTypeScript(
            new \OmegaUp\Request([
                'id' => 'wrong_verification_id',
            ])
        )['templateProperties']['payload'];

        $this->assertArrayNotHasKey('verifyEmailSuccessfully', $payload);
        $this->assertArrayHasKey('statusError', $payload);

        $user = \OmegaUp\DAO\Users::findByEmail($email);

        $this->assertFalse($user->verified);
    }

    public function testUpdateMainEmailAsASupportTeamMember() {
        // Creates a user with role of support team member
        [
            'identity' => $supportIdentity,
        ] = \OmegaUp\Test\Factories\User::createSupportUser();

        $originalEmail = 'original_mail@omegaup.com';
        $identityPassword = \OmegaUp\Test\Utils::createRandomString();
        // Creates a common user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(
                [
                    'email' => $originalEmail,
                    'password' => $identityPassword,
                ]
            )
        );

        $loginResponse = \OmegaUp\Controllers\User::apiLogin(
            new \OmegaUp\Request([
                'usernameOrEmail' => $originalEmail,
                'password' => $identityPassword,
            ])
        );

        $this->assertLogin($identity, $loginResponse['auth_token']);

        $login = self::login($supportIdentity);

        $newEmail = 'new_mail@omegaup.com';
        \OmegaUp\Controllers\User::apiUpdateMainEmail(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'email' => $newEmail,
                'originalEmail' => $originalEmail,
            ])
        );

        $loginResponse = \OmegaUp\Controllers\User::apiLogin(
            new \OmegaUp\Request([
                'usernameOrEmail' => $newEmail,
                'password' => $identityPassword,
            ])
        );

        $this->assertLogin($identity, $loginResponse['auth_token']);

        try {
            // Identity is no longer able to login with original email
            $loginResponse = \OmegaUp\Controllers\User::apiLogin(
                new \OmegaUp\Request([
                    'usernameOrEmail' => $originalEmail,
                    'password' => $identityPassword,
                ])
            );
        } catch (\OmegaUp\Exceptions\InvalidCredentialsException $e) {
            $this->assertSame('usernameOrPassIsWrong', $e->getMessage());
        }
    }

    public function testUpdateMainEmailFromAnyoneElseAsACommonUser() {
        // Creates a user to try update email
        [
            'identity' => $fakeSupportIdentity,
        ] = \OmegaUp\Test\Factories\User::createUser();

        // Creates a common user
        \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['email' => 'original@email.com']
            )
        );

        $login = self::login($fakeSupportIdentity);

        try {
            // Only users with support team member role can perform this action
            \OmegaUp\Controllers\User::apiUpdateMainEmail(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'email' => 'new@email.com',
                    'originalEmail' => 'original@email.com',
                ])
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * support generates a valid token, and users change their password
     */
    public function testUserGeneratesValidToken() {
        // Support team member will verify $user
        ['identity' => $supportIdentity] = \OmegaUp\Test\Factories\User::createSupportUser();

        // Creates a user
        $email = \OmegaUp\Test\Utils::createRandomString() . '@mail.com';
        \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['email' => $email]
            )
        );

        // Call api using support team member
        $supportLogin = self::login($supportIdentity);

        // Support tries to generate token without a request
        $response = \OmegaUp\Controllers\User::apiExtraInformation(new \OmegaUp\Request([
            'auth_token' => $supportLogin->auth_token,
            'usernameOrEmail' => $email
        ]));

        $this->assertFalse($response['within_last_day']);

        // Now, user makes a password change request
        \OmegaUp\Controllers\Reset::apiCreate(new \OmegaUp\Request([
            'email' => $email
        ]));

        // Support can generate token
        \OmegaUp\Controllers\User::apiExtraInformation(new \OmegaUp\Request([
            'auth_token' => $supportLogin->auth_token,
            'usernameOrEmail' => $email
        ]));
        $response = \OmegaUp\Controllers\Reset::apiGenerateToken(new \OmegaUp\Request([
            'auth_token' => $supportLogin->auth_token,
            'email' => $email,
        ]));

        // Finally, users can update their password with the generated token
        $reset_token = explode('reset_token=', $response['link'])[1];
        $password = \OmegaUp\Test\Utils::createRandomString();
        $response = \OmegaUp\Controllers\Reset::apiUpdate(new \OmegaUp\Request([
            'email' => $email,
            'reset_token' => $reset_token,
            'password' => $password,
            'password_confirmation' => $password
        ]));
    }

    /**
     * support tries to generate an expired token
     */
    public function testUserGeneratesExpiredToken() {
        // Support team member will verify $user
        ['identity' => $supportIdentity] = \OmegaUp\Test\Factories\User::createSupportUser();

        // Creates a user
        $email = \OmegaUp\Test\Utils::createRandomString() . '@mail.com';
        ['user' => $user] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['email' => $email]
            )
        );

        // Call api using support team member
        $supportLogin = self::login($supportIdentity);

        // time travel
        $reset_sent_at = \OmegaUp\ApiUtils::getStringTime(
            \OmegaUp\Time::get() - PASSWORD_RESET_MIN_WAIT - (60 * 60 * 24)
        );
        $user = \OmegaUp\DAO\Users::findByEmail($email);
        $user->reset_sent_at = $reset_sent_at;
        \OmegaUp\DAO\Users::update($user);

        // Support can not generate token because it has expired
        $response = \OmegaUp\Controllers\User::apiExtraInformation(new \OmegaUp\Request([
            'auth_token' => $supportLogin->auth_token,
            'usernameOrEmail' => $email
        ]));

        $this->assertFalse($response['within_last_day']);
    }

    public function testAddRemoveRolesAsSupportTeamMember() {
        $username = 'testuserroles';
        ['identity' => $support] = \OmegaUp\Test\Factories\User::createSupportUser();
        \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['username' => $username]
            )
        );

        $login = self::login($support);
        // Call to API Add Role
        // Support team member should not be able to add the admin role since this
        // action should be performed by a sys-admin
        try {
            \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $username,
                'role' => 'Admin'
            ]));
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame($e->getMessage(), 'userNotAllowed');
        }
        \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $username,
            'role' => 'Reviewer'
        ]));
        \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $username,
            'role' => 'Mentor'
        ]));
        \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $username,
            'role' => 'CertificateGenerator'
        ]));
    }
}
