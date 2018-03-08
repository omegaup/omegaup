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
        $support = UserFactory::createSupportUser();
        $mentor = UserFactory::createMentorUser();

        $is_support_member = Authorization::isSupportTeamMember($support->user_id);
        // Asserting that user belongs to the support group
        $this->assertEquals(1, $is_support_member);

        $is_support_member = Authorization::isSupportTeamMember($mentor->user_id);
        // Asserting that user doesn't belong to the support group
        $this->assertNotEquals(1, $is_support_member);
    }

    /**
     *
     */
    public function testUserGeneratesToken() {
        // Support team member will verify $user
        $support = UserFactory::createSupportUser();

        // Creates no verified user
        $user = UserFactory::createUser();

        // Call api using support team member
        $supportLogin = self::login($support);
        $response = ResetController::apiGenerateToken(new Request([
            'auth_token' => $supportLogin->auth_token,
            'email' => $user->username,
        ]));

        $this->assertContains($user->username, $response['link']);
    }
}
