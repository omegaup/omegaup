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
        $mentor = UserFactory::createMentorIdentity();

        $is_support_member = Authorization::isSupportTeamMember($support->user_id);
        // Asserting that user belongs to the support group
        $this->assertEquals(1, $is_support_member);

        $is_support_member = Authorization::isSupportTeamMember($mentor->user_id);
        // Asserting that user doesn't belong to the support group
        $this->assertNotEquals(1, $is_support_member);
    }
}
