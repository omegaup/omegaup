<?php

class AdminRoleTest extends \OmegaUp\Test\ControllerTestCase {
    private static function request(
        \OmegaUp\DAO\VO\Identities $identity,
        array $params = []
    ): \OmegaUp\Request {
        $login = self::login($identity);
        return new \OmegaUp\Request(
            ['auth_token' => $login->auth_token] + $params
        );
    }

    private static function adminRoleName(): string {
        return \OmegaUp\DAO\Roles::getByPK(
            \OmegaUp\Authorization::ADMIN_ROLE
        )->name;
    }

    public function testListIsForbiddenForRegularUsers(): void {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        try {
            \OmegaUp\Controllers\Admin::apiGetRoles(self::request($identity));
            $this->fail('Regular users should not list roles');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    public function testListIsAllowedForSupportAndAdmin(): void {
        ['identity' => $support] = \OmegaUp\Test\Factories\User::createSupportUser();
        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createAdminUser();

        foreach ([$support, $admin] as $identity) {
            $response = \OmegaUp\Controllers\Admin::apiGetRoles(
                self::request($identity)
            );
            $this->assertNotEmpty($response['roles']);
            foreach ($response['roles'] as $role) {
                $this->assertArrayHasKey('name', $role);
                $this->assertArrayHasKey('users_count', $role);
                $this->assertArrayHasKey('groups_count', $role);
                $this->assertArrayHasKey('permissions_count', $role);
            }
        }
    }

    public function testListCountsDirectUsersOfTheAdminRole(): void {
        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createAdminUser();

        $response = \OmegaUp\Controllers\Admin::apiGetRoles(
            self::request($admin)
        );

        $adminRole = null;
        foreach ($response['roles'] as $role) {
            if ($role['role_id'] === \OmegaUp\Authorization::ADMIN_ROLE) {
                $adminRole = $role;
            }
        }
        $this->assertNotNull($adminRole);
        $this->assertGreaterThanOrEqual(1, $adminRole['users_count']);
    }

    public function testDetailsReturnsTheRoleAndItsPermissions(): void {
        ['identity' => $support] = \OmegaUp\Test\Factories\User::createSupportUser();

        $response = \OmegaUp\Controllers\Admin::apiGetRoleDetails(
            self::request($support, ['role' => self::adminRoleName()])
        );

        $this->assertSame(
            \OmegaUp\Authorization::ADMIN_ROLE,
            $response['role']['role_id']
        );
        $this->assertIsArray($response['permissions']);
    }

    public function testDetailsReturnsTheResourceTypeOfEachPermission(): void {
        ['identity' => $support] = \OmegaUp\Test\Factories\User::createSupportUser();

        $response = \OmegaUp\Controllers\Admin::apiGetRoleDetails(
            self::request($support, ['role' => self::adminRoleName()])
        );

        $this->assertNotEmpty($response['permissions']);
        $resourceTypes = [];
        foreach ($response['permissions'] as $permission) {
            $this->assertContains(
                $permission['resource_type'],
                ['entrypoint', 'endpoint', 'capability']
            );
            $resourceTypes[$permission['resource_type']] = true;
        }
        $this->assertArrayHasKey('entrypoint', $resourceTypes);
        $this->assertArrayHasKey('endpoint', $resourceTypes);
        $this->assertArrayHasKey('capability', $resourceTypes);
    }

    public function testDetailsWithUnknownRoleFails(): void {
        ['identity' => $support] = \OmegaUp\Test\Factories\User::createSupportUser();

        try {
            \OmegaUp\Controllers\Admin::apiGetRoleDetails(
                self::request($support, ['role' => 'no-such-role'])
            );
            $this->fail('An unknown role should fail');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterNotFound', $e->getMessage());
        }
    }

    public function testAssignmentsAreForbiddenForRegularUsers(): void {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $this->expectException(
            \OmegaUp\Exceptions\ForbiddenAccessException::class
        );
        \OmegaUp\Controllers\Admin::apiGetRoleAssignments(
            self::request($identity, [
                'role' => self::adminRoleName(),
                'assignment_type' => 'user',
            ])
        );
    }

    public function testAssignmentsRejectAnInvalidType(): void {
        ['identity' => $support] = \OmegaUp\Test\Factories\User::createSupportUser();

        try {
            \OmegaUp\Controllers\Admin::apiGetRoleAssignments(
                self::request($support, [
                    'role' => self::adminRoleName(),
                    'assignment_type' => 'team',
                ])
            );
            $this->fail('An invalid assignment type should fail');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterInvalid', $e->getMessage());
        }
    }

    public function testUserAssignmentsCanBeFilteredByUsername(): void {
        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createAdminUser();
        ['identity' => $support] = \OmegaUp\Test\Factories\User::createSupportUser();

        $response = \OmegaUp\Controllers\Admin::apiGetRoleAssignments(
            self::request($support, [
                'role' => self::adminRoleName(),
                'assignment_type' => 'user',
                'query' => $admin->username,
            ])
        );

        $this->assertGreaterThanOrEqual(1, $response['total_rows']);
        $match = null;
        foreach ($response['assignments'] as $assignment) {
            if ($assignment['name'] === $admin->username) {
                $match = $assignment;
            }
        }
        $this->assertNotNull($match);
        $this->assertSame(\OmegaUp\Authorization::SYSTEM_ACL, $match['acl_id']);
        $this->assertSame('system', $match['resource_type']);
    }

    public function testAssignmentsRespectThePageSize(): void {
        ['identity' => $support] = \OmegaUp\Test\Factories\User::createSupportUser();
        \OmegaUp\Test\Factories\User::createAdminUser();
        \OmegaUp\Test\Factories\User::createAdminUser();

        $response = \OmegaUp\Controllers\Admin::apiGetRoleAssignments(
            self::request($support, [
                'role' => self::adminRoleName(),
                'assignment_type' => 'user',
                'page' => 1,
                'page_size' => 1,
            ])
        );

        $this->assertCount(1, $response['assignments']);
        $this->assertGreaterThanOrEqual(2, $response['total_rows']);
    }

    public function testGroupAssignmentsReturnTheExpectedShape(): void {
        ['identity' => $support] = \OmegaUp\Test\Factories\User::createSupportUser();

        $response = \OmegaUp\Controllers\Admin::apiGetRoleAssignments(
            self::request($support, [
                'role' => self::adminRoleName(),
                'assignment_type' => 'group',
                'query' => 'no-such-group-alias',
            ])
        );

        $this->assertSame([], $response['assignments']);
        $this->assertSame(0, $response['total_rows']);
    }

    public function testUserAssignmentsResolveContestResources(): void {
        ['identity' => $support] = \OmegaUp\Test\Factories\User::createSupportUser();
        ['identity' => $user] = \OmegaUp\Test\Factories\User::createUser();
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        \OmegaUp\Test\Factories\Contest::addAdminUser($contestData, $user);

        $response = \OmegaUp\Controllers\Admin::apiGetRoleAssignments(
            self::request($support, [
                'role' => self::adminRoleName(),
                'assignment_type' => 'user',
                'query' => $user->username,
            ])
        );

        $match = null;
        foreach ($response['assignments'] as $assignment) {
            if ($assignment['name'] === $user->username) {
                $match = $assignment;
            }
        }
        $this->assertNotNull($match);
        $this->assertSame('contest', $match['resource_type']);
        $this->assertSame(
            $contestData['request']['alias'],
            $match['resource_alias']
        );
    }

    /**
     * @return array{role_id: int, name: string, description: string, users_count: int, groups_count: int, permissions_count: int}
     */
    private static function adminRoleSummary(
        \OmegaUp\DAO\VO\Identities $identity
    ): array {
        $response = \OmegaUp\Controllers\Admin::apiGetRoles(
            self::request($identity)
        );
        foreach ($response['roles'] as $role) {
            if ($role['role_id'] === \OmegaUp\Authorization::ADMIN_ROLE) {
                return $role;
            }
        }
        throw new \RuntimeException('The admin role was not found');
    }

    private static function userAssignmentsTotal(
        \OmegaUp\DAO\VO\Identities $identity
    ): int {
        return \OmegaUp\Controllers\Admin::apiGetRoleAssignments(
            self::request($identity, [
                'role' => self::adminRoleName(),
                'assignment_type' => 'user',
            ])
        )['total_rows'];
    }

    public function testDetailsMatchTheSummaryOfTheList(): void {
        ['identity' => $support] = \OmegaUp\Test\Factories\User::createSupportUser();

        $details = \OmegaUp\Controllers\Admin::apiGetRoleDetails(
            self::request($support, ['role' => self::adminRoleName()])
        );

        $this->assertSame(self::adminRoleSummary($support), $details['role']);
        $this->assertCount(
            $details['role']['permissions_count'],
            $details['permissions']
        );
    }

    public function testDetailsOfARoleWithoutPermissions(): void {
        ['identity' => $support] = \OmegaUp\Test\Factories\User::createSupportUser();
        $roleName = \OmegaUp\DAO\Roles::getByPK(
            \OmegaUp\Authorization::CONTESTANT_ROLE
        )->name;

        $details = \OmegaUp\Controllers\Admin::apiGetRoleDetails(
            self::request($support, ['role' => $roleName])
        );

        $this->assertSame(0, $details['role']['permissions_count']);
        $this->assertSame([], $details['permissions']);
    }

    public function testUserAssignmentsIgnoreSecondaryIdentities(): void {
        ['identity' => $support] = \OmegaUp\Test\Factories\User::createSupportUser();
        ['identity' => $user] = \OmegaUp\Test\Factories\User::createUser();
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        \OmegaUp\Test\Factories\Contest::addAdminUser($contestData, $user);
        // A second identity of the same user, with the same username prefix.
        \OmegaUp\MySQLConnection::getInstance()->Execute(
            'INSERT INTO Identities (username, user_id, name) VALUES (?, ?, ?);',
            [$user->username . '-second', $user->user_id, 'Secondary identity']
        );

        $response = \OmegaUp\Controllers\Admin::apiGetRoleAssignments(
            self::request($support, [
                'role' => self::adminRoleName(),
                'assignment_type' => 'user',
                'query' => $user->username,
            ])
        );

        $this->assertSame(1, $response['total_rows']);
        $this->assertCount(1, $response['assignments']);
        $this->assertSame(
            $user->username,
            $response['assignments'][0]['name']
        );
    }

    public function testCountsDistinguishUsersFromAssignments(): void {
        ['identity' => $support] = \OmegaUp\Test\Factories\User::createSupportUser();
        ['identity' => $user] = \OmegaUp\Test\Factories\User::createUser();
        $usersBefore = self::adminRoleSummary($support)['users_count'];
        $assignmentsBefore = self::userAssignmentsTotal($support);

        // The same user is an admin of two different contests.
        foreach ([1, 2] as $_) {
            $contestData = \OmegaUp\Test\Factories\Contest::createContest();
            \OmegaUp\Test\Factories\Contest::addAdminUser($contestData, $user);
        }

        $this->assertSame(
            $usersBefore + 1,
            self::adminRoleSummary($support)['users_count']
        );
        $this->assertSame(
            $assignmentsBefore + 2,
            self::userAssignmentsTotal($support)
        );
    }

    public function testAssignmentsQueryEscapesWildcards(): void {
        ['identity' => $support] = \OmegaUp\Test\Factories\User::createSupportUser();

        $response = \OmegaUp\Controllers\Admin::apiGetRoleAssignments(
            self::request($support, [
                'role' => self::adminRoleName(),
                'assignment_type' => 'user',
                'query' => '%',
            ])
        );

        $this->assertSame(0, $response['total_rows']);
        $this->assertSame([], $response['assignments']);
    }
}
