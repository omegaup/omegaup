<?php
/**
 * Test autogenerating privileges user
 */
class UserPrivilegesTest extends \OmegaUp\Test\ControllerTestCase {
    /*
     * Test for the functions add/remove roles
     */
    public function testAddRemoveRoles() {
        $username = 'testuserrole';
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['username' => $username]
            )
        );

        $login = self::login($identity);
        // Call to API Add Role
        // User has already the admin role
        try {
            \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $username,
                'role' => 'Admin'
            ]));
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            $this->assertSame($e->getMessage(), 'userAlreadyHasSelectedRole');
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

        $selectedRolesMapping = ['CertificateGenerator','Admin', 'Reviewer', 'Mentor'];

        $systemRoles = \OmegaUp\DAO\UserRoles::getSystemRoles($user->user_id);
        foreach ($selectedRolesMapping as $role) {
            $this->assertContains($role, $systemRoles);
        }

        $payload = \OmegaUp\Controllers\User::getUserRolesForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['templateProperties']['payload'];

        foreach ($payload['userSystemRoles'] as $userRole) {
            $this->assertSame(
                $userRole['value'],
                in_array(
                    $userRole['name'],
                    $selectedRolesMapping
                )
            );
        }

        // Call to API Remove Role
        \OmegaUp\Controllers\User::apiRemoveRole(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $username,
            'role' => 'Mentor'
        ]));
        $systemRoles = \OmegaUp\DAO\UserRoles::getSystemRoles($user->user_id);
        $this->assertNotContains('Mentor', $systemRoles);

        array_pop($selectedRolesMapping);

        $systemRoles = \OmegaUp\DAO\UserRoles::getSystemRoles($user->user_id);
        foreach ($selectedRolesMapping as $role) {
            $this->assertContains($role, $systemRoles);
        }

        $payload = \OmegaUp\Controllers\User::getUserRolesForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['templateProperties']['payload'];

        foreach ($payload['userSystemRoles'] as $userRole) {
            $this->assertSame(
                $userRole['value'],
                in_array(
                    $userRole['name'],
                    $selectedRolesMapping
                )
            );
        }
    }

    public function testPreviouslyAddedRoles() {
        $username = 'testuserrole';
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['username' => $username]
            )
        );

        $login = self::login($identity);
        // Call to API Add Role to add a mentor role only, since the user already has the admin role
        \OmegaUp\Controllers\User::apiAddRole(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $username,
                'role' => 'Mentor'
            ])
        );

        [
            'systemRoles' => $systemRoles,
        ] = \OmegaUp\Controllers\User::getUserDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $username,
            ])
        )['templateProperties']['payload'];

        $this->assertEqualsCanonicalizing(['Admin', 'Mentor'], $systemRoles);
    }

    public function testAddPreviouslyAddedRoles() {
        $username = 'testuserrole';
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['username' => $username]
            )
        );

        $login = self::login($identity);

        try {
            // Trying to add the same role when the user already has it
            \OmegaUp\Controllers\User::apiAddRole(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'username' => $username,
                    'role' => 'Admin'
                ])
            );
            $this->fail('should not have been able to add the same role');
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            // Exception expected
            $this->assertSame($e->getMessage(), 'userAlreadyHasSelectedRole');
        }
    }

    /*
     * Test for the function add/remove groups
     */
    public function testAddRemoveGroups() {
        $username = 'testusergroup';
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['username' => $username]
            )
        );

        $login = self::login($identity);
        // Call to API Add Group
        \OmegaUp\Controllers\User::apiAddGroup(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $username,
            'group' => 'omegaup:quality-reviewer'
        ]));
        \OmegaUp\Controllers\User::apiAddGroup(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $username,
            'group' => 'omegaup:course-curator'
        ]));
        \OmegaUp\Controllers\User::apiAddGroup(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $username,
            'group' => 'omegaup:mentor'
        ]));

        $selectedGroupsMapping = [
            'omegaup:quality-reviewer',
            'omegaup:course-curator',
            'omegaup:mentor',
        ];

        $systemGroups = \OmegaUp\DAO\UserRoles::getSystemGroups(
            $identity->identity_id
        );
        foreach ($selectedGroupsMapping as $group) {
            $this->assertContains($group, $systemGroups);
        }

        $payload = \OmegaUp\Controllers\User::getUserRolesForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['templateProperties']['payload'];

        foreach ($payload['userSystemGroups'] as $userGroup) {
            $this->assertSame(
                $userGroup['value'],
                in_array(
                    $userGroup['name'],
                    $selectedGroupsMapping
                )
            );
        }

        // Call to API Remove Group
        \OmegaUp\Controllers\User::apiRemoveGroup(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $username,
            'group' => 'omegaup:mentor'
        ]));
        $systemGroups = \OmegaUp\DAO\UserRoles::getSystemGroups(
            $identity->user_id
        );
        $this->assertNotContains('omegaup:mentor', $systemGroups);

        array_pop($selectedGroupsMapping);

        $payload = \OmegaUp\Controllers\User::getUserRolesForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['templateProperties']['payload'];

        foreach ($payload['userSystemGroups'] as $userGroup) {
            $this->assertSame(
                $userGroup['value'],
                in_array(
                    $userGroup['name'],
                    $selectedGroupsMapping
                )
            );
        }
    }

    /*
     * Test API addRole for users intended to upload identities
     */
    public function testAddGroupIdentityCreatorRole() {
        // Create an admin identity
        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createAdminUser();

        // Create group identity creator user
        ['identity' => $creator] = \OmegaUp\Test\Factories\User::createUser();

        $adminLogin = self::login($admin);
        // Call to API Add Role
        $response = \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'username' => $creator->username,
            'role' => 'GroupIdentityCreator'
        ]));

        $systemRoles = \OmegaUp\DAO\UserRoles::getSystemRoles(
            $creator->user_id
        );
        $this->assertContains('GroupIdentityCreator', $systemRoles);

        $creatorLogin = self::login($creator);
        ['group' => $group] = \OmegaUp\Test\Factories\Groups::createGroup(
            $creator,
            null,
            null,
            null,
            $creatorLogin
        );

        // Call api using identity creator group member
        \OmegaUp\Controllers\Identity::apiBulkCreate(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'identities' => \OmegaUp\Test\Factories\Identity::getCsvData(
                'identities.csv',
                $group->alias,
            ),
            'group_alias' => $group->alias,
        ]));
        $response = \OmegaUp\Controllers\Group::apiMembers(
            new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'group_alias' => $group->alias,
            ])
        );
        $this->assertCount(5, $response['identities']);

        // Call to API Remove Role
        $adminLogin = self::login($admin);
        $response = \OmegaUp\Controllers\User::apiRemoveRole(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'username' => $creator->username,
                'role' => 'GroupIdentityCreator'
            ])
        );
        $systemRoles = \OmegaUp\DAO\UserRoles::getSystemRoles(
            $creator->user_id
        );
        $this->assertNotContains('GroupIdentityCreator', $systemRoles);

        try {
            $creatorLogin = self::login($creator);
            \OmegaUp\Controllers\Identity::apiBulkCreate(new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'identities' => \OmegaUp\Test\Factories\Identity::getCsvData(
                    'identities.csv',
                    $group->alias,
                ),
                'group_alias' => $group->alias,
            ]));
            $this->fail('should not have been able to create identites');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            // Exception expected
            $this->assertSame($e->getMessage(), 'userNotAllowed');
        }
    }
}
