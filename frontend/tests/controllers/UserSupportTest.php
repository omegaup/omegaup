<?php

/**
 * Test administrative tasks for support team
 *
 * @author juan.pablo
 */
class UserSupportTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Basic test for users with support role
     */
    public function testUserHasSupportRole() {
        ['user' => $supportUser, 'identity' => $supportIdentity] = \OmegaUp\Test\Factories\User::createSupportUser();
        ['user' => $mentorUser, 'identity' => $mentorIdentity] = \OmegaUp\Test\Factories\User::createMentorIdentity();

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
        ['user' => $supportUser, 'identity' => $supportIdentity] = \OmegaUp\Test\Factories\User::createSupportUser();

        // Creates a user
        $email = \OmegaUp\Test\Utils::createRandomString() . '@mail.com';
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(new \OmegaUp\Test\Factories\UserParams([
            'email' => $email,
            'verify' => false
        ]));

        // Call api using support team member
        $supportLogin = self::login($supportIdentity);

        $response = \OmegaUp\Controllers\User::apiExtraInformation(new \OmegaUp\Request([
            'auth_token' => $supportLogin->auth_token,
            'email' => $email
        ]));

        $this->assertEquals(0, $response['verified']);

        // Call apiVerifyEmail
        \OmegaUp\Controllers\User::apiVerifyEmail(new \OmegaUp\Request([
            'auth_token' => $supportLogin->auth_token,
            'usernameOrEmail' => $email,
        ]));

        // Get user information to pick up verification changes
        $response = \OmegaUp\Controllers\User::apiExtraInformation(new \OmegaUp\Request([
            'auth_token' => $supportLogin->auth_token,
            'email' => $email
        ]));

        $this->assertEquals(1, $response['verified']);
    }

    /**
     * support generates a valid token, and users change their password
     */
    public function testUserGeneratesValidToken() {
        // Support team member will verify $user
        ['user' => $supportUser, 'identity' => $supportIdentity] = \OmegaUp\Test\Factories\User::createSupportUser();

        // Creates a user
        $email = \OmegaUp\Test\Utils::createRandomString() . '@mail.com';
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['email' => $email]
            )
        );

        // Call api using support team member
        $supportLogin = self::login($supportIdentity);

        // Support tries to generate token without a request
        $response = \OmegaUp\Controllers\User::apiExtraInformation(new \OmegaUp\Request([
            'auth_token' => $supportLogin->auth_token,
            'email' => $email
        ]));

        $this->assertEquals(0, $response['within_last_day']);

        // Now, user makes a password change request
        \OmegaUp\Controllers\Reset::apiCreate(new \OmegaUp\Request([
            'email' => $email
        ]));

        // Support can genearate token
        \OmegaUp\Controllers\User::apiExtraInformation(new \OmegaUp\Request([
            'auth_token' => $supportLogin->auth_token,
            'email' => $email
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
        ['user' => $supportUser, 'identity' => $supportIdentity] = \OmegaUp\Test\Factories\User::createSupportUser();

        // Creates a user
        $email = \OmegaUp\Test\Utils::createRandomString() . '@mail.com';
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(
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

        // Support can not genearate token because it has expired
        $response = \OmegaUp\Controllers\User::apiExtraInformation(new \OmegaUp\Request([
            'auth_token' => $supportLogin->auth_token,
            'email' => $email
        ]));

        $this->assertEquals(0, $response['within_last_day']);
    }
}
