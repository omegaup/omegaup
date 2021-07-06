<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

/**
 * TeamGroupsTest
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

    /**
     * Basic update teams group test
     */
    public function testUpdateTeamsGroup() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $originalName = \OmegaUp\Test\Utils::createRandomString();
        $originalDescription = \OmegaUp\Test\Utils::createRandomString();
        $alias = \OmegaUp\Test\Utils::createRandomString();

        $login = self::login($identity);
        \OmegaUp\Controllers\TeamsGroup::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => $originalName,
                'alias' => $alias,
                'description' => $originalDescription,
            ])
        );

        $teamsGroup = \OmegaUp\DAO\TeamGroups::getByName($originalName);
        $this->assertNotNull($teamsGroup);
        $this->assertEquals($originalDescription, $teamsGroup->description);

        $updatedName = \OmegaUp\Test\Utils::createRandomString();
        $updatedDescription = \OmegaUp\Test\Utils::createRandomString();

        \OmegaUp\Controllers\TeamsGroup::apiUpdate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => $updatedName,
                'alias' => $alias,
                'description' => $updatedDescription,
            ])
        );

        $teamsGroup = \OmegaUp\DAO\TeamGroups::getByAlias($alias);
        $this->assertNotNull($teamsGroup);
        $this->assertEquals($updatedName, $teamsGroup->name);
        $this->assertEquals($updatedDescription, $teamsGroup->description);
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
                    /*$password=*/ '',
                    /*$forTeams=*/ true
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
                "teams:{$teamGroup->alias}:{$teamUsername}",
                $identity['username']
            );
            $this->assertStringContainsString('Team', $identity['name']);
        }

        // All created users are shown in apiList
        $identities = \OmegaUp\Controllers\User::apiList(
            new \OmegaUp\Request([
                'query' => 'user',
                'auth_token' => $creatorLogin->auth_token,
            ])
        )['results'];
        $this->assertCount(10, $identities);

        // And all the teams are hidden in apiList
        $identities = \OmegaUp\Controllers\User::apiList(
            new \OmegaUp\Request([
                'query' => 'teams',
                'auth_token' => $creatorLogin->auth_token,
            ])
        )['results'];
        $this->assertEmpty($identities);
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
                        /*$password=*/ '',
                        /*$forTeams=*/ true
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
                        /*$password=*/ '',
                        /*$forTeams=*/ true
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
                        /*$password=*/ '',
                        /*$forTeams=*/ true
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
                        /*$password=*/ '',
                        /*$forTeams=*/ true
                    ),
                    'team_group_alias' => $teamGroup->alias,
                ])
            );
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            $this->assertEquals('teamAliasInUse', $e->getMessage());
        }
    }

    public function testUploadCsvFileWithDuplicatedMemberUsername() {
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
                        'duplicated_member_identities.csv',
                        $teamGroup->alias,
                        /*$password=*/ '',
                        /*$forTeams=*/ true
                    ),
                    'team_group_alias' => $teamGroup->alias,
                ])
            );
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            $this->assertEquals('teamMemberUsernameInUse', $e->getMessage());
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

    /**
     * A PHPUnit data provider for tests to search teams group.
     *
     * @return list<list<array{0: string, 1: bool, 2: bbol, 3: bool}>>
     */
    public function teamsGroupQueryProvider(): array {
        return [
            // query to search, number of matches
            ['teamsGroup', 2],
            ['s_Group', 3],
            ['Group', 10],
            ['teams', 5],
            ['teams-Group-', 1],
            ['unmatched_string', 0],
            ['_', 10],
        ];
    }

    /**
     * @dataProvider teamsGroupQueryProvider
     */
    public function testTeamsGroupsListForTypeahead(
        string $query,
        int $numberOfMatches
    ) {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $string = substr(\OmegaUp\Test\Utils::createRandomString(), 0, 6);
        $teamsGroupsMapping = [
            "teamsGroup_{$string}",
            "teamGroup_{$string}",
            "teamsGroup{$string}",
            "teamGroup{$string}",
            "teams_Group_{$string}",
            "team_Group_{$string}",
            "teams-Group_{$string}",
            "team-Group_{$string}",
            "teams-Group-{$string}",
            "team-Group-{$string}",
        ];

        $login = self::login($identity);
        foreach ($teamsGroupsMapping as $teamsGroup) {
            \OmegaUp\Controllers\TeamsGroup::apiCreate(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'name' => \OmegaUp\Test\Utils::createRandomString(),
                    'alias' => $teamsGroup,
                    'description' => \OmegaUp\Test\Utils::createRandomString()
                ])
            );
        }
        $response = \OmegaUp\Controllers\TeamsGroup::apiList(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'query' => $query,
            ])
        );

        $this->assertCount($numberOfMatches, $response);
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
                    /*$password=*/ '',
                    /*$forTeams=*/ true
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
                "teams:{$teamGroup->alias}:{$teamUsername}",
                $identity['username']
            );
            $this->assertStringContainsString('Team', $identity['name']);
        }
    }

    public function testUpdateTeamsFromTeamsGroup() {
        // Identity creator group member will upload csv file
        [
           'identity' => $creatorIdentity,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        $teamGroup = \OmegaUp\Test\Factories\Groups::createTeamsGroup(
            $creatorIdentity,
            /*$name*/ null,
            /*$description*/ null,
            /*$alias*/ null,
            $creatorLogin
        )['teamGroup'];

        // Call api using identity creator group team
        \OmegaUp\Controllers\Identity::apiBulkCreateForTeams(
            new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'team_identities' => \OmegaUp\Test\Factories\Identity::getCsvData(
                    'team_identities.csv',
                    $teamGroup->alias,
                    /*$password=*/ '',
                    /*$forTeams=*/ true
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

        // Now, we are going to update all teams from the teams group
        foreach ($teamIdentities as $i => $teamIdentity) {
            [
                'identities' => $teamIdentities,
            ] = \OmegaUp\Controllers\TeamsGroup::getTeamGroupEditDetailsForTypeScript(
                new \OmegaUp\Request([
                    'team_group_alias' => $teamGroup->alias,
                    'auth_token' => $creatorLogin->auth_token,
                ])
            )['smartyProperties']['payload'];

            $identity = \OmegaUp\Controllers\Identity::resolveIdentity(
                $teamIdentity['username']
            );
            $newIdentityName = substr(
                \OmegaUp\Test\Utils::createRandomString(),
                - 10
            );
            \OmegaUp\Controllers\Identity::apiUpdateIdentityTeam(
                new \OmegaUp\Request([
                    'auth_token' => $creatorLogin->auth_token,
                    'username' => $teamIdentity['username'],
                    'name' => $newIdentityName,
                    'country_id' => 'US',
                    'state_id' => 'CA',
                    'gender' => 'other',
                    'school_name' => \OmegaUp\Test\Utils::createRandomString(),
                    'group_alias' => $teamGroup->alias,
                    'original_username' => $identity->username,
                ])
            );

            $updatedIdentity = \OmegaUp\Controllers\Identity::resolveIdentity(
                $teamIdentity['username']
            );
            $this->assertEquals($updatedIdentity->name, $newIdentityName);
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
                    $teamGroup->alias,
                    /*$password=*/ '',
                    /*$forTeams=*/ true
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

    public function testSwitchBetweenAssociatedIdentities() {
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
        $numberOfUsers = 2;

        $usernameAndPasswordIdentities = [];
        foreach (range(0, $numberOfUsers - 1) as $id) {
            $usernameAndPasswordIdentities[] = [
                'username' => "user{$id}",
                'password' => "user_password_{$id}",
            ];
        }

        // Users to associate
        foreach ($usernameAndPasswordIdentities as $id => $usernameIdentity) {
            [
                'identity' => $identities[$id],
            ] = \OmegaUp\Test\Factories\User::createUser(
                new \OmegaUp\Test\Factories\UserParams($usernameIdentity)
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
                    /*$password=*/ '',
                    /*$forTeams=*/ true
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

        foreach ($identities as $index => $identity) {
            $identity->password = $usernameAndPasswordIdentities[$index]['password'];
            $login = self::login($identity);

            [
                'identities' => $associatedIdentities,
            ] = \OmegaUp\Controllers\User::apiListAssociatedIdentities(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                ])
            );

            // All users have two associated identities
            $this->assertCount(2, $associatedIdentities);
            $this->assertStringContainsString(
                "{$teamGroup->alias}:",
                $associatedIdentities[1]['username']
            );

            // User switch the account
            $response = \OmegaUp\Controllers\Identity::apiSelectIdentity(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'usernameOrEmail' => $associatedIdentities[1]['username'],
                ])
            );

            // Identity can not access to apiListAssociatedIdentities
            try {
                \OmegaUp\Controllers\User::apiListAssociatedIdentities(
                    new \OmegaUp\Request([
                        'auth_token' => $login->auth_token,
                    ])
                );
            } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
                $this->assertEquals('userNotAllowed', $e->getMessage());
            }
        }
    }

    public function testListTeamMembers() {
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
                    /*$password=*/ '',
                    /*$forTeams=*/ true
                ),
                'team_group_alias' => $teamGroup->alias,
            ])
        );

        $teamsUsers = \OmegaUp\Controllers\TeamsGroup::apiTeamsMembers(
            new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'team_group_alias' => $teamGroup->alias,
            ])
        )['teamsUsers'];

        foreach ($teamUsernames as $teamUsername) {
            $membersByTeam = array_filter(
                $teamsUsers,
                fn ($teamMember) => $teamMember['team_alias'] === "teams:{$teamGroup->alias}:{$teamUsername}"
            );
            $this->assertCount(2, $membersByTeam);
        }

        $teamsUsersChunk = \OmegaUp\Controllers\TeamsGroup::apiTeamsMembers(
            new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'team_group_alias' => $teamGroup->alias,
                'page' => 1,
                'page_size' => 4,
            ])
        );

        $this->assertCount(4, $teamsUsersChunk['teamsUsers']);
        $this->assertEquals(10, $teamsUsersChunk['totalRows']);

        $usernames = array_map(
            fn ($user) => $user['username'],
            $teamsUsersChunk['teamsUsers']
        );
        $this->assertEquals($usernames, ['user0', 'user1', 'user2', 'user3']);

        $teamsUsersChunk = \OmegaUp\Controllers\TeamsGroup::apiTeamsMembers(
            new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'team_group_alias' => $teamGroup->alias,
                'page' => 2,
                'page_size' => 4,
            ])
        )['teamsUsers'];

        $usernames = array_map(
            fn ($user) => $user['username'],
            $teamsUsersChunk
        );
        $this->assertEquals($usernames, ['user4', 'user5', 'user6', 'user7']);
    }

    public function testRemoveMembersToTeam() {
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
                    /*$password=*/ '',
                    /*$forTeams=*/ true
                ),
                'team_group_alias' => $teamGroup->alias,
            ])
        );

        $teamsUsers = \OmegaUp\Controllers\TeamsGroup::apiTeamsMembers(
            new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'team_group_alias' => $teamGroup->alias,
            ])
        )['teamsUsers'];
        $teamAlias = "teams:{$teamGroup->alias}:{$teamUsernames[0]}";

        foreach ($teamUsernames as $teamUsername) {
            $membersByTeam = array_filter(
                $teamsUsers,
                fn ($teamMember) => $teamMember['team_alias'] === $teamAlias
            );
            $this->assertCount(2, $membersByTeam);
        }

        $membersByTeam1 = array_filter(
            $teamsUsers,
            fn ($teamMember) => $teamMember['team_alias'] === $teamAlias
        );
        $this->assertCount(2, $membersByTeam1);

        // Remove all team members for an specific team
        foreach ($membersByTeam1 as $member) {
            \OmegaUp\Controllers\TeamsGroup::apiRemoveMember(
                new \OmegaUp\Request([
                    'auth_token' => $creatorLogin->auth_token,
                    'team_group_alias' => $teamAlias,
                    'username' => $member['username'],
                ])
            );
        }

        $teamsUsers = \OmegaUp\Controllers\TeamsGroup::apiTeamsMembers(
            new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'team_group_alias' => $teamGroup->alias,
            ])
        )['teamsUsers'];
        $membersByTeam1 = array_filter(
            $teamsUsers,
            fn ($teamMember) => $teamMember['team_alias'] === $teamAlias
        );
        $this->assertEmpty($membersByTeam1);
    }

    public function testAddMembersToTeam() {
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
        foreach (range(0, 11) as $id) {
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
                    /*$password=*/ '',
                    /*$forTeams=*/ true
                ),
                'team_group_alias' => $teamGroup->alias,
            ])
        );

        $teamsUsers = \OmegaUp\Controllers\TeamsGroup::apiTeamsMembers(
            new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'team_group_alias' => $teamGroup->alias,
            ])
        )['teamsUsers'];

        foreach ($teamUsernames as $teamUsername) {
            $membersByTeam = array_filter(
                $teamsUsers,
                fn ($teamMember) => $teamMember['team_alias'] === "teams:{$teamGroup->alias}:{$teamUsername}"
            );
            $this->assertCount(2, $membersByTeam);
        }

        // Add member to the first team
        $teamAlias = "teams:{$teamGroup->alias}:{$teamUsernames[0]}";
        \OmegaUp\Controllers\TeamsGroup::apiAddMembers(
            new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'team_group_alias' => $teamAlias,
                'usernames' => 'user10,user11',
            ])
        );

        $teamsUsers = \OmegaUp\Controllers\TeamsGroup::apiTeamsMembers(
            new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'team_group_alias' => $teamGroup->alias,
            ])
        )['teamsUsers'];
        $membersByTeam1 = array_filter(
            $teamsUsers,
            fn ($teamMember) => $teamMember['team_alias'] === $teamAlias
        );
        $this->assertCount(4, $membersByTeam1);
    }
}
