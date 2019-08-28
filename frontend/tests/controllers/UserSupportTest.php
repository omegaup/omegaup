<?php

/**
 * Test administrative tasks for support team
 *
 * @author juan.pablo
 */
class UserSupportTest extends OmegaupTestCase {
    /**
     * Basic test for users with support role
     */
    public function testUserHasSupportRole() {
        [, $supportIdentity] = UserFactory::createSupportUser();
        [, $mentorIdentity] = UserFactory::createMentorIdentity();

        // Asserting that user belongs to the support group
        $this->assertTrue(\OmegaUp\Authorization::isSupportTeamMember($supportIdentity));

        // Asserting that user doesn't belong to the support group
        $this->assertFalse(\OmegaUp\Authorization::isSupportTeamMember($mentorIdentity));
    }

    /**
     * Tests user verification with support role
     */
    public function testVerifyUser() {
        // Support team member will verify $user
        [$supportUser,] = UserFactory::createSupportUser();

        // Creates a user
        $email = Utils::CreateRandomString().'@mail.com';
        $user = UserFactory::createUser(new UserParams([
            'email' => $email,
            'verify' => false
        ]));

        // Call api using support team member
        $supportLogin = self::login($supportUser);

        $response = UserController::apiExtraInformation(new \OmegaUp\Request([
            'auth_token' => $supportLogin->auth_token,
            'email' => $email
        ]));

        $this->assertEquals(0, $response['verified']);

        // Call apiVerifyEmail
        UserController::apiVerifyEmail(new \OmegaUp\Request([
            'auth_token' => $supportLogin->auth_token,
            'usernameOrEmail' => $email,
        ]));

        // Get user information to pick up verification changes
        $response = UserController::apiExtraInformation(new \OmegaUp\Request([
            'auth_token' => $supportLogin->auth_token,
            'email' => $email
        ]));

        $this->assertEquals(1, $response['verified']);
        $this->assertEquals('ok', $response['status']);
    }

    /**
     * support generates a valid token, and users change their password
     */
    public function testUserGeneratesValidToken() {
        // Support team member will verify $user
        [$supportUser,] = UserFactory::createSupportUser();

        // Creates a user
        $email = Utils::CreateRandomString().'@mail.com';
        $user = UserFactory::createUser(new UserParams(['email' => $email]));

        // Call api using support team member
        $supportLogin = self::login($supportUser);

        // Support tries to generate token without a request
        $response = UserController::apiExtraInformation(new \OmegaUp\Request([
            'auth_token' => $supportLogin->auth_token,
            'email' => $email
        ]));

        $this->assertEquals(0, $response['within_last_day']);

        // Now, user makes a password change request
        ResetController::apiCreate(new \OmegaUp\Request([
            'email' => $email
        ]));

        // Support can genearate token
        UserController::apiExtraInformation(new \OmegaUp\Request([
            'auth_token' => $supportLogin->auth_token,
            'email' => $email
        ]));
        $response = ResetController::apiGenerateToken(new \OmegaUp\Request([
            'auth_token' => $supportLogin->auth_token,
            'email' => $email,
        ]));

        // Finally, users can update their password with the generated token
        $reset_token = explode('reset_token=', $response['link'])[1];
        $password = Utils::CreateRandomString();
        $response = ResetController::apiUpdate(new \OmegaUp\Request([
            'email' => $email,
            'reset_token' => $reset_token,
            'password' => $password,
            'password_confirmation' => $password
        ]));

        $this->assertContains('ok', $response['status']);
    }

    /**
     * support tries to generate an expired token
     */
    public function testUserGeneratesExpiredToken() {
        // Support team member will verify $user
        [$supportUser, ] = UserFactory::createSupportUser();

        // Creates a user
        $email = Utils::CreateRandomString().'@mail.com';
        $user = UserFactory::createUser(new UserParams(['email' => $email]));

        // Call api using support team member
        $supportLogin = self::login($supportUser);

        // time travel
        $reset_sent_at = \OmegaUp\ApiUtils::getStringTime(
            \OmegaUp\Time::get() - PASSWORD_RESET_MIN_WAIT - (60 * 60 * 24)
        );
        $user = UsersDAO::FindByEmail($email);
        $user->reset_sent_at = $reset_sent_at;
        UsersDAO::update($user);

        // Support can not genearate token because it has expired
        $response = UserController::apiExtraInformation(new \OmegaUp\Request([
            'auth_token' => $supportLogin->auth_token,
            'email' => $email
        ]));

        $this->assertEquals(0, $response['within_last_day']);
    }
}
