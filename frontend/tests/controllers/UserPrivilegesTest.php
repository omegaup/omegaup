<?php

/**
 * Test autogenerating privileges user
 *
 * @author juan.pablo
 */
class UserPrivilegesTest extends OmegaupTestCase {
    /*
     * Test for the functions add/remove roles
     */
    public function testAddRemoveRoles() {
        $username = 'testuserrole';
        $user = UserFactory::createUser(new UserParams(['username' => $username]));

        $login = self::login($user);
        // Call to API Add Role
        $response = UserController::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $username,
            'role' => 'Admin'
        ]));
        $response = UserController::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $username,
            'role' => 'Reviewer'
        ]));
        $response = UserController::apiAddRole(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $username,
            'role' => 'Mentor'
        ]));

        $systemRoles = \OmegaUp\DAO\UserRoles::getSystemRoles($user->user_id);
        $this->assertContains('Admin', $systemRoles);
        $this->assertContains('Reviewer', $systemRoles);
        $this->assertContains('Mentor', $systemRoles);

        // Call to API Remove Role
        $response = UserController::apiRemoveRole(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $username,
            'role' => 'Mentor'
        ]));
        $systemRoles = \OmegaUp\DAO\UserRoles::getSystemRoles($user->user_id);
        $this->assertNotContains('Mentor', $systemRoles);
    }

    /*
     * Test for the function add/remove groups
     */
    public function testAddRemoveGroups() {
        $username = 'testusergroup';
        $user = UserFactory::createUser(new UserParams(['username' => $username]));
        $identity = \OmegaUp\DAO\Identities::getByPK($user->main_identity_id);

        $login = self::login($user);
        // Call to API Add Group
        $response = UserController::apiAddGroup(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $username,
            'group' => 'omegaup:quality-reviewer'
        ]));
        $response = UserController::apiAddGroup(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $username,
            'group' => 'omegaup:course-curator'
        ]));
        $response = UserController::apiAddGroup(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $username,
            'group' => 'omegaup:mentor'
        ]));

        $systemGroups = \OmegaUp\DAO\UserRoles::getSystemGroups($identity->identity_id);
        $this->assertContains('omegaup:quality-reviewer', $systemGroups);
        $this->assertContains('omegaup:course-curator', $systemGroups);
        $this->assertContains('omegaup:mentor', $systemGroups);

        // Call to API Remove Group
        $response = UserController::apiRemoveGroup(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'username' => $username,
            'group' => 'omegaup:mentor'
        ]));
        $systemGroups = \OmegaUp\DAO\UserRoles::getSystemGroups($user->user_id);
        $this->assertNotContains('omegaup:mentor', $systemGroups);
    }
}
