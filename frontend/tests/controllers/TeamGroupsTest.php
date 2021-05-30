<?php

/**
 * TeamGroupsTest
 *
 * @author juan.pablo
 */

class TeamGroupsTest extends \OmegaUp\Test\ControllerTestCase {
    public function testTeamGroupEditDetailsPayload() {
        [
            'owner' => $owner,
            'teamGroup' => $teamGroup,
        ] = \OmegaUp\Test\Factories\Groups::createTeamsGroup();

        $login = self::login($owner);
        $response = \OmegaUp\Controllers\Group::getTeamGroupEditDetailsForTypeScript(
            new \OmegaUp\Request([
                'team_group_alias' => $teamGroup->alias,
                'auth_token' => $login->auth_token,
            ])
        )['smartyProperties']['payload'];

        $this->assertEquals(
            $response['teamGroup'],
            [
                'alias' => $teamGroup->alias,
                'name' => $teamGroup->name,
                'description' => $teamGroup->description,
            ]
        );
    }

    public function testTeamGroupEditDetailsPrivileges() {
        [
            'owner' => $owner,
            'teamGroup' => $teamGroup,
        ] = \OmegaUp\Test\Factories\Groups::createTeamsGroup();

        $login = self::login($owner);

        // Owners can get all team group details
        \OmegaUp\Controllers\Group::getTeamGroupEditDetailsForTypeScript(
            new \OmegaUp\Request([
                'team_group_alias' => $teamGroup->alias,
                'auth_token' => $login->auth_token,
            ])
        );

        // Anyone else should not
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        try {
            \OmegaUp\Controllers\Group::getTeamGroupEditDetailsForTypeScript(
                new \OmegaUp\Request([
                    'team_group_alias' => $teamGroup->alias,
                    'auth_token' => $login->auth_token,
                ])
            );
            $this->fail('It should fail because of the permissions');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Basic create team group test
     */
    public function testCreateTeamGroup() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $name = \OmegaUp\Test\Utils::createRandomString();
        $description = \OmegaUp\Test\Utils::createRandomString();
        $alias = \OmegaUp\Test\Utils::createRandomString();

        $login = self::login($identity);
        \OmegaUp\Controllers\Group::apiCreateTeamGroup(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => $name,
                'alias' => $alias,
                'description' => $description
            ])
        );

        $teamGroup = \OmegaUp\DAO\TeamGroups::getByName($name);
        $this->assertNotNull($teamGroup);
        $this->assertEquals($description, $teamGroup->description);
        $this->assertTrue(
            \OmegaUp\Authorization::isTeamGroupAdmin($identity, $teamGroup)
        );

        $teamGroup = \OmegaUp\DAO\TeamGroups::getByAlias($alias);
        $this->assertNotNull($teamGroup);
        $this->assertEquals($description, $teamGroup->description);
    }

    public function testCreateTeamGroupWithCorrectOwnership() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        [
            'identity' => $nonAuthorizedIdentity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        $name = \OmegaUp\Test\Utils::createRandomString();
        $description = \OmegaUp\Test\Utils::createRandomString();
        $alias = \OmegaUp\Test\Utils::createRandomString();

        $login = self::login($identity);
        \OmegaUp\Controllers\Group::apiCreateTeamGroup(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => $name,
                'alias' => $alias,
                'description' => $description
            ])
        );

        $teamGroup = \OmegaUp\DAO\TeamGroups::getByAlias($alias);
        $this->assertFalse(
            \OmegaUp\Authorization::isTeamGroupAdmin(
                $nonAuthorizedIdentity,
                $teamGroup
            )
        );
    }

    public function testCreateTeamGroupWithDuplicatedAlias() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $name = \OmegaUp\Test\Utils::createRandomString();
        $description = \OmegaUp\Test\Utils::createRandomString();
        $alias = \OmegaUp\Test\Utils::createRandomString();

        $login = self::login($identity);
        \OmegaUp\Controllers\Group::apiCreateTeamGroup(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => $name,
                'alias' => $alias,
                'description' => $description
            ])
        );

        try {
            \OmegaUp\Controllers\Group::apiCreateTeamGroup(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'name' => $name,
                    'alias' => $alias,
                    'description' => $description
                ])
            );
            $this->fail(
                'Teams group creation should have failed because alias is already in use'
            );
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            $this->assertEquals('aliasInUse', $e->getMessage());
        }
    }
}
