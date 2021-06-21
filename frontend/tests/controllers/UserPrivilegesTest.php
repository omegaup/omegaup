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
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['username' => $username]
            )
        );

        $login = self::login($identity);
        // Call to API Add Role
        $response = \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $username,
            'role' => 'Admin'
        ]));
        $response = \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $username,
            'role' => 'Reviewer'
        ]));
        $response = \OmegaUp\Controllers\User::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $username,
            'role' => 'Mentor'
        ]));

        $systemRoles = \OmegaUp\DAO\UserRoles::getSystemRoles($user->user_id);
        $this->assertContains('Admin', $systemRoles);
        $this->assertContains('Reviewer', $systemRoles);
        $this->assertContains('Mentor', $systemRoles);

        // Call to API Remove Role
        $response = \OmegaUp\Controllers\User::apiRemoveRole(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $username,
            'role' => 'Mentor'
        ]));
        $systemRoles = \OmegaUp\DAO\UserRoles::getSystemRoles($user->user_id);
        $this->assertNotContains('Mentor', $systemRoles);
    }

    public function testPreviouslyAddedRoles() {
        $username = 'testuserrole';
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['username' => $username]
            )
        );

        $login = self::login($identity);
        // Call to API Add Role
        \OmegaUp\Controllers\User::apiAddRole(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $username,
                'role' => 'Admin'
            ])
        );
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
        )['smartyProperties']['payload'];

        $this->assertEqualsCanonicalizing(['Admin', 'Mentor'], $systemRoles);
    }

    public function testAddPreviouslyAddedRoles() {
        $username = 'testuserrole';
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['username' => $username]
            )
        );

        $login = self::login($identity);
        // Call to API Add Role
        \OmegaUp\Controllers\User::apiAddRole(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $username,
                'role' => 'Admin'
            ])
        );

        try {
            // Trying to add the same role
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
            $this->assertEquals($e->getMessage(), 'userAlreadyHasSelectedRole');
        }
    }

    /*
     * Test for the function add/remove groups
     */
    public function testAddRemoveGroups() {
        $username = 'testusergroup';
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams(
                ['username' => $username]
            )
        );

        $login = self::login($identity);
        // Call to API Add Group
        $response = \OmegaUp\Controllers\User::apiAddGroup(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $username,
            'group' => 'omegaup:quality-reviewer'
        ]));
        $response = \OmegaUp\Controllers\User::apiAddGroup(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $username,
            'group' => 'omegaup:course-curator'
        ]));
        $response = \OmegaUp\Controllers\User::apiAddGroup(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $username,
            'group' => 'omegaup:mentor'
        ]));

        $systemGroups = \OmegaUp\DAO\UserRoles::getSystemGroups(
            $identity->identity_id
        );
        $this->assertContains('omegaup:quality-reviewer', $systemGroups);
        $this->assertContains('omegaup:course-curator', $systemGroups);
        $this->assertContains('omegaup:mentor', $systemGroups);

        // Call to API Remove Group
        $response = \OmegaUp\Controllers\User::apiRemoveGroup(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $username,
            'group' => 'omegaup:mentor'
        ]));
        $systemGroups = \OmegaUp\DAO\UserRoles::getSystemGroups(
            $identity->user_id
        );
        $this->assertNotContains('omegaup:mentor', $systemGroups);
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
            $this->assertEquals($e->getMessage(), 'userNotAllowed');
        }
    }
}
