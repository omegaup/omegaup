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
        $response = \OmegaUp\Controllers\User::apiAddRole(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'username' => $username,
                'role' => 'Admin'
            ])
        );

        try {
            // Trying to add the same role
            $response = \OmegaUp\Controllers\User::apiAddRole(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'username' => $username,
                    'role' => 'Admin'
                ])
            );
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
}
