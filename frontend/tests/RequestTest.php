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

    /**
     * @return array<string, array{0: string}>
     */
    public static function invalidJsonStringListProvider(): array {
        return [
            'malformed json' => ['invalid-json'],
            'json object' => ['{"a":"b"}'],
            'json scalar' => ['"abc"'],
            'null element' => ['[null]'],
            'object element' => ['[{}]'],
            'number element' => ['[1]'],
            'nested list' => ['[["a"]]'],
        ];
    }

    /**
     * @dataProvider invalidJsonStringListProvider
     */
    public function testEnsureJsonStringListRejectsInvalidInput(
        string $value
    ): void {
        $request = new \OmegaUp\Request(['problems' => $value]);

        try {
            $request->ensureJsonStringList('problems');
            $this->fail('Expected InvalidParameterException was not thrown');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterInvalid', $e->getMessage());
            $this->assertSame('problems', $e->parameter);
        }
    }

    public function testEnsureJsonStringListRejectsMissingParameter(): void {
        $request = new \OmegaUp\Request([]);

        try {
            $request->ensureJsonStringList('problems');
            $this->fail('Expected InvalidParameterException was not thrown');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterEmpty', $e->getMessage());
        }
    }

    public function testEnsureJsonStringListAcceptsValidLists(): void {
        $request = new \OmegaUp\Request([
            'problems' => '["alias-1","alias-2"]',
            'empty' => '[]',
        ]);

        $this->assertSame(
            ['alias-1', 'alias-2'],
            $request->ensureJsonStringList('problems')
        );
        $this->assertSame([], $request->ensureJsonStringList('empty'));
    }

    public function testEnsureJsonStringListAppliesValidatorToEachElement(): void {
        $request = new \OmegaUp\Request([
            'problems' => '["good","bad"]',
        ]);

        try {
            $request->ensureJsonStringList(
                'problems',
                fn (string $alias): bool => $alias !== 'bad'
            );
            $this->fail('Expected InvalidParameterException was not thrown');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterInvalid', $e->getMessage());
            $this->assertSame('problems', $e->parameter);
        }
    }
}
