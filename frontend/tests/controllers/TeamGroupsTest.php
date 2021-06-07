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
        $response = \OmegaUp\Controllers\TeamsGroup::getTeamGroupEditDetailsForTypeScript(
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
        \OmegaUp\Controllers\TeamsGroup::getTeamGroupEditDetailsForTypeScript(
            new \OmegaUp\Request([
                'team_group_alias' => $teamGroup->alias,
                'auth_token' => $login->auth_token,
            ])
        );

        // Anyone else should not
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        try {
            \OmegaUp\Controllers\TeamsGroup::getTeamGroupEditDetailsForTypeScript(
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
        \OmegaUp\Controllers\TeamsGroup::apiCreate(
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
        \OmegaUp\Controllers\TeamsGroup::apiCreate(
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
        \OmegaUp\Controllers\TeamsGroup::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => $name,
                'alias' => $alias,
                'description' => $description
            ])
        );

        try {
            \OmegaUp\Controllers\TeamsGroup::apiCreate(
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

    /**
     * Basic test for uploading csv file with identities as teams
     */
    public function testUploadCsvFile() {
        // Identity creator group member will upload csv file
        [
            'identity' => $creatorIdentity,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        [
            'teamGroup' => $teamGroup,
        ] = \OmegaUp\Test\Factories\Groups::createTeamsGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        );

        // Users to associate
        foreach (range(0, 9) as $id) {
            \OmegaUp\Test\Factories\User::createUser(
                new \OmegaUp\Test\Factories\UserParams([
                    'username' => "user{$id}",
                ])
            );
        }

        $teamUsernames = \OmegaUp\Test\Factories\Identity::getUsernamesInCsvFile(
            'team_identities.csv',
            $teamGroup->alias,
        );

        // Call api using identity creator group member
        \OmegaUp\Controllers\Identity::apiBulkCreateForTeams(
            new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'team_identities' => \OmegaUp\Test\Factories\Identity::getCsvData(
                    'team_identities.csv',
                    $teamGroup->alias,
                ),
                'team_group_alias' => $teamGroup->alias,
            ])
        );

        [
            'identities' => $identities,
        ] = \OmegaUp\Controllers\TeamsGroup::getTeamGroupEditDetailsForTypeScript(
            new \OmegaUp\Request([
                'team_group_alias' => $teamGroup->alias,
                'auth_token' => $creatorLogin->auth_token,
            ])
        )['smartyProperties']['payload'];

        $this->assertCount(5, $identities);

        foreach ($identities as $index => $identity) {
            $dbIdentity = \OmegaUp\DAO\Identities::findByUsername(
                $identity['username']
            );
            $team = \OmegaUp\DAO\Teams::getByIdentityId(
                $dbIdentity->identity_id
            );
            $result = \OmegaUp\DAO\TeamUsers::getByTeamId($team->team_id);
            // All the teams have 2 associated users
            $this->assertCount(2, $result);
            $teamUsername = $teamUsernames[$index];
            $this->assertEquals(
                "{$teamGroup->alias}:{$teamUsername}",
                $identity['username']
            );
            $this->assertStringContainsString('Team', $identity['name']);
        }
    }

    public function testUploadCsvFileWithNoPrivilegesUser() {
        // Identity creator group member will upload csv file
        [
            'identity' => $creatorIdentity,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        [
            'teamGroup' => $teamGroup,
        ] = \OmegaUp\Test\Factories\Groups::createTeamsGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        );

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        try {
            \OmegaUp\Controllers\Identity::apiBulkCreateForTeams(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'team_identities' => \OmegaUp\Test\Factories\Identity::getCsvData(
                        'team_identities.csv',
                        $teamGroup->alias,
                    ),
                    'team_group_alias' => $teamGroup->alias,
                ])
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    public function testUploadCsvFileWithWrongAlias() {
        // Identity creator group member will upload csv file
        [
            'identity' => $creatorIdentity,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        [
            'teamGroup' => $teamGroup,
        ] = \OmegaUp\Test\Factories\Groups::createTeamsGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        );

        try {
            \OmegaUp\Controllers\Identity::apiBulkCreateForTeams(
                new \OmegaUp\Request([
                    'auth_token' => $creatorLogin->auth_token,
                    'team_identities' => \OmegaUp\Test\Factories\Identity::getCsvData(
                        'team_identities.csv',
                        $teamGroup->alias,
                    ),
                    'team_group_alias' => 'fake_alias',
                ])
            );
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            $this->assertEquals('groupNotFound', $e->getMessage());
        }
    }

    public function testUploadCsvFileWithWrongFile() {
        // Identity creator group member will upload csv file
        [
            'identity' => $creatorIdentity,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        [
            'teamGroup' => $teamGroup,
        ] = \OmegaUp\Test\Factories\Groups::createTeamsGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        );

        try {
            \OmegaUp\Controllers\Identity::apiBulkCreateForTeams(
                new \OmegaUp\Request([
                    'auth_token' => $creatorLogin->auth_token,
                    'team_identities' => \OmegaUp\Test\Factories\Identity::getCsvData(
                        'malformed_team_identities.csv',
                        $teamGroup->alias,
                    ),
                    'team_group_alias' => $teamGroup->alias,
                ])
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals('parameterInvalid', $e->getMessage());
        }
    }

    public function testUploadCsvFileWithDuplicatedTeamUsername() {
        // Identity creator group member will upload csv file
        [
            'identity' => $creatorIdentity,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        [
            'teamGroup' => $teamGroup,
        ] = \OmegaUp\Test\Factories\Groups::createTeamsGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        );

        try {
            \OmegaUp\Controllers\Identity::apiBulkCreateForTeams(
                new \OmegaUp\Request([
                    'auth_token' => $creatorLogin->auth_token,
                    'team_identities' => \OmegaUp\Test\Factories\Identity::getCsvData(
                        'duplicated_team_identities.csv',
                        $teamGroup->alias,
                    ),
                    'team_group_alias' => $teamGroup->alias,
                ])
            );
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            $this->assertEquals('aliasInUse', $e->getMessage());
        }
    }

    public function testTeamsGroupDetails() {
        [
            'owner' => $owner,
            'teamGroup' => $teamGroup,
        ] = \OmegaUp\Test\Factories\Groups::createTeamsGroup();

        // Call API
        $login = self::login($owner);
        $response = \OmegaUp\Controllers\TeamsGroup::apiDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'team_group_alias' => $teamGroup->alias,
        ]));
        $this->assertEquals(
            $teamGroup->alias,
            $response['team_group']['alias']
        );
    }

    public function testTeamsGroupsList() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $numberOfGroups = 3;

        $login = self::login($identity);
        foreach (range(0, $numberOfGroups - 1) as $i) {
            $name = \OmegaUp\Test\Utils::createRandomString();
            $description = \OmegaUp\Test\Utils::createRandomString();
            $alias = \OmegaUp\Test\Utils::createRandomString();
            \OmegaUp\Controllers\TeamsGroup::apiCreate(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'name' => $name,
                    'alias' => $alias,
                    'description' => $description
                ])
            );
        }
        $response = \OmegaUp\Controllers\TeamsGroup::getGroupListForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        )['smartyProperties']['payload'];

        $this->assertCount($numberOfGroups, $response['teamsGroups']);
    }

    public function testTeamsIntoTeamsGroup() {
        // Identity creator group member will upload csv file
        [
            'identity' => $creatorIdentity,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        [
            'teamGroup' => $teamGroup,
        ] = \OmegaUp\Test\Factories\Groups::createTeamsGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        );
        $numberOfUsers = 10;

        // Users to associate
        foreach (range(0, $numberOfUsers - 1) as $id) {
            \OmegaUp\Test\Factories\User::createUser(
                new \OmegaUp\Test\Factories\UserParams([
                    'username' => "user{$id}",
                ])
            );
        }

        $teamUsernames = \OmegaUp\Test\Factories\Identity::getUsernamesInCsvFile(
            'team_identities.csv',
            $teamGroup->alias,
        );

        // Call api using identity creator group member
        \OmegaUp\Controllers\Identity::apiBulkCreateForTeams(
            new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'team_identities' => \OmegaUp\Test\Factories\Identity::getCsvData(
                    'team_identities.csv',
                    $teamGroup->alias,
                ),
                'team_group_alias' => $teamGroup->alias,
            ])
        );

        [
            'identities' => $teams,
        ] = \OmegaUp\Controllers\TeamsGroup::apiTeams(
            new \OmegaUp\Request([
                'team_group_alias' => $teamGroup->alias,
                'auth_token' => $creatorLogin->auth_token,
            ])
        );

        $this->assertCount(5, $teams);

        foreach ($teams as $index => $identity) {
            $dbIdentity = \OmegaUp\DAO\Identities::findByUsername(
                $identity['username']
            );
            $team = \OmegaUp\DAO\Teams::getByIdentityId(
                $dbIdentity->identity_id
            );
            $result = \OmegaUp\DAO\TeamUsers::getByTeamId($team->team_id);
            // All the teams have 2 associated users
            $this->assertCount(2, $result);
            $teamUsername = $teamUsernames[$index];
            $this->assertEquals(
                "{$teamGroup->alias}:{$teamUsername}",
                $identity['username']
            );
            $this->assertStringContainsString('Team', $identity['name']);
        }
    }

    public function testRemoveTeamsFromTeamsGroup() {
        // Identity creator group member will upload csv file
        [
           'identity' => $creatorIdentity,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $teamGroup = \OmegaUp\Test\Factories\Groups::createTeamsGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        )['teamGroup'];

        // Call api using identity creator group team
        \OmegaUp\Controllers\Identity::apiBulkCreateForTeams(
            new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'team_identities' => \OmegaUp\Test\Factories\Identity::getCsvData(
                    'team_identities.csv',
                    $teamGroup->alias
                ),
                'team_group_alias' => $teamGroup->alias,
            ])
        );

        [
            'identities' => $teamIdentities,
        ] = \OmegaUp\Controllers\TeamsGroup::getTeamGroupEditDetailsForTypeScript(
            new \OmegaUp\Request([
                'team_group_alias' => $teamGroup->alias,
                'auth_token' => $creatorLogin->auth_token,
            ])
        )['smartyProperties']['payload'];

        $this->assertCount(5, $teamIdentities);

        // Now, we are going to remove all teams from the teams group
        foreach ($teamIdentities as $i => $teamIdentity) {
            [
                'identities' => $teamIdentities,
            ] = \OmegaUp\Controllers\TeamsGroup::getTeamGroupEditDetailsForTypeScript(
                new \OmegaUp\Request([
                    'team_group_alias' => $teamGroup->alias,
                    'auth_token' => $creatorLogin->auth_token,
                ])
            )['smartyProperties']['payload'];

            $this->assertCount(5 - $i, $teamIdentities);

            \OmegaUp\Controllers\TeamsGroup::apiRemoveTeam(new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'usernameOrEmail' => $teamIdentity['username'],
                'team_group_alias' => $teamGroup->alias
            ]));
        }

        [
            'identities' => $teamIdentities,
        ] = \OmegaUp\Controllers\TeamsGroup::getTeamGroupEditDetailsForTypeScript(
            new \OmegaUp\Request([
                'team_group_alias' => $teamGroup->alias,
                'auth_token' => $creatorLogin->auth_token,
            ])
        )['smartyProperties']['payload'];

        $this->assertEmpty($teamIdentities);
    }
}
