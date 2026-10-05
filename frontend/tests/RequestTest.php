<?php
/**
 * Tests for \OmegaUp\Request::ensureUserHasAdministrativeAccess().
 */
class RequestTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * A system admin should always be granted administrative access,
     * since admin privileges are hierarchical and imply every other
     * administrative role, including support team membership.
     */
    public function testSystemAdminIsGrantedAccess() {
        ['identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = self::login($adminIdentity);

        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]);

        // Should not throw.
        $r->ensureUserHasAdministrativeAccess();
        $this->assertTrue(true);
    }

    /**
     * A support team member (who is not a system admin) should also be
     * granted administrative access.
     */
    public function testSupportTeamMemberIsGrantedAccess() {
        ['identity' => $supportIdentity] = \OmegaUp\Test\Factories\User::createSupportUser();
        $login = self::login($supportIdentity);

        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]);

        // Should not throw.
        $r->ensureUserHasAdministrativeAccess();
        $this->assertTrue(true);
    }

    /**
     * A regular user with no administrative role should be rejected.
     */
    public function testRegularUserIsDenied() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]);

        $this->expectException(
            \OmegaUp\Exceptions\ForbiddenAccessException::class
        );
        $r->ensureUserHasAdministrativeAccess();
    }
}
