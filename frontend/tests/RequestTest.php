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
    public function testSystemAdminIsGrantedAccess(): void {
        ['identity' => $adminIdentity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = self::login($adminIdentity);

        $request = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]);
        $request->ensureUserHasAdministrativeAccess();
    }

    /**
     * A support team member (who is not a system admin) should also be
     * granted administrative access.
     */
    public function testSupportTeamMemberIsGrantedAccess(): void {
        ['identity' => $supportIdentity] = \OmegaUp\Test\Factories\User::createSupportUser();
        $login = self::login($supportIdentity);

        $request = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]);
        $request->ensureUserHasAdministrativeAccess();
    }

    /**
     * A regular user with no administrative role should be rejected.
     */
    public function testRegularUserIsDeniedAccess(): void {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        $request = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]);

        $this->expectException(
            \OmegaUp\Exceptions\ForbiddenAccessException::class
        );
        $request->ensureUserHasAdministrativeAccess();
    }

    /**
     * A user that is not logged in at all should be rejected before any
     * permission check happens.
     */
    public function testUnauthenticatedUserIsDeniedAccess(): void {
        $request = new \OmegaUp\Request([]);

        $this->expectException(
            \OmegaUp\Exceptions\UnauthorizedException::class
        );
        $request->ensureUserHasAdministrativeAccess();
    }
}
