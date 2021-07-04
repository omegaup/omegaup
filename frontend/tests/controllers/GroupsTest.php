<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

/**
 * GroupsTest
 */

class GroupsTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Basic create group test
     */
    public function testCreateGroup() {
        ['user' => $owner, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $name = \OmegaUp\Test\Utils::createRandomString();
        $description = \OmegaUp\Test\Utils::createRandomString();
        $alias = \OmegaUp\Test\Utils::createRandomString();

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Group::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => $name,
            'alias' => $alias,
            'description' => $description
        ]));

        $this->assertEquals('ok', $response['status']);

        $group = \OmegaUp\DAO\Groups::getByName($name);
        $this->assertNotNull($group);
        $this->assertEquals($description, $group->description);
        $this->assertTrue(
            \OmegaUp\Authorization::isGroupAdmin(
                $identity,
                $group
            )
        );
    }

    /**
     * Basic update group test
     */
    public function testUpdateGroup() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $originalName = \OmegaUp\Test\Utils::createRandomString();
        $originalDescription = \OmegaUp\Test\Utils::createRandomString();
        $alias = \OmegaUp\Test\Utils::createRandomString();

        $login = self::login($identity);
        \OmegaUp\Controllers\Group::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => $originalName,
            'alias' => $alias,
            'description' => $originalDescription,
        ]));

        $updatedName = \OmegaUp\Test\Utils::createRandomString();
        $updatedDescription = \OmegaUp\Test\Utils::createRandomString();

        $response = \OmegaUp\Controllers\Group::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => $updatedName,
            'alias' => $alias,
            'description' => $updatedDescription,
        ]));

        $this->assertEquals('ok', $response['status']);

        $group = \OmegaUp\DAO\Groups::findByAlias($alias);

        $this->assertNotNull($group);
        $this->assertEquals($updatedDescription, $group->description);
        $this->assertEquals($updatedName, $group->name);
    }

    /**
     * Basic update group test
     */
    public function testUpdateGroupWithWrongIdentity() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $owner] = \OmegaUp\Test\Factories\User::createUser();
        $name = \OmegaUp\Test\Utils::createRandomString();
        $description = \OmegaUp\Test\Utils::createRandomString();
        $alias = \OmegaUp\Test\Utils::createRandomString();

        $login = self::login($owner);
        \OmegaUp\Controllers\Group::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => $name,
            'alias' => $alias,
            'description' => $description,
        ]));

        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\Group::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => \OmegaUp\Test\Utils::createRandomString(),
                'alias' => $alias,
                'description' => \OmegaUp\Test\Utils::createRandomString(),
            ]));
            $this->fail(
                'Group update should have failed because user is not the owner'
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Attempts to create groups with a restricted alias should fail.
     */
    public function testCreateGroupRestrictedAlias() {
        ['user' => $owner, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        try {
            $login = self::login($identity);
            \OmegaUp\Controllers\Group::apiCreate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => \OmegaUp\Test\Utils::createRandomString(),
                'alias' => 'omegaup',
                'description' => \OmegaUp\Test\Utils::createRandomString(),
            ]));
            $this->fail('Group creation should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals('parameterInvalid', $e->getMessage());
        }
    }

    public function testCreateGroupWithInvalidAlias() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        try {
            $login = self::login($identity);
            \OmegaUp\Controllers\Group::apiCreate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => \OmegaUp\Test\Utils::createRandomString(),
                'alias' => 'invalid alias',
                'description' => \OmegaUp\Test\Utils::createRandomString(),
            ]));
            $this->fail('Group creation should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals('parameterInvalid', $e->getMessage());
        }
    }

    /**
     * Add identity to group
     */
    public function testAddUserToGroup() {
        $group = \OmegaUp\Test\Factories\Groups::createGroup();
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($group['owner']);
        $response = \OmegaUp\Controllers\Group::apiAddUser(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $identity->username,
            'group_alias' => $group['group']->alias
        ]));
        $this->assertEquals('ok', $response['status']);

        $group_users = \OmegaUp\DAO\GroupsIdentities::getByPK(
            $group['group']->group_id,
            $identity->identity_id
        );
        $this->assertNotNull($group_users);
    }

    /**
     * Add user to group
     */
    public function testAddUserToGroupNotOwned() {
        $group = \OmegaUp\Test\Factories\Groups::createGroup();
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        ['user' => $userCalling, 'identity' => $identityCalling] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identityCalling);
        try {
            \OmegaUp\Controllers\Group::apiAddUser(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'usernameOrEmail' => $identity->username,
                'group_alias' => $group['group']->alias
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Remove user from group test
     */
    public function testRemoveUserFromGroup() {
        $groupData = \OmegaUp\Test\Factories\Groups::createGroup();
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Groups::addUserToGroup($groupData, $identity);

        $login = self::login($groupData['owner']);
        $response = \OmegaUp\Controllers\Group::apiRemoveUser(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $identity->username,
            'group_alias' => $groupData['group']->alias
        ]));

        $this->assertEquals('ok', $response['status']);

        $group_users = \OmegaUp\DAO\GroupsIdentities::getByPK(
            $groupData['group']->group_id,
            $user->user_id
        );
        $this->assertNull($group_users);
    }

    /**
     * Remove user from group test
     */
    public function testRemoveUserFromGroupUserNotInGroup() {
        $groupData = \OmegaUp\Test\Factories\Groups::createGroup();
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($groupData['owner']);
        try {
            \OmegaUp\Controllers\Group::apiRemoveUser(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'usernameOrEmail' => $identity->username,
                'group_alias' => $groupData['group']->alias
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals('parameterNotFound', $e->getMessage());
            $this->assertEquals('User', $e->parameter);
        }
    }

    /**
     * Remove user from group test
     */
    public function testRemoveUserFromGroupUserNotOwner() {
        $groupData = \OmegaUp\Test\Factories\Groups::createGroup();
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        try {
            \OmegaUp\Controllers\Group::apiRemoveUser(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'usernameOrEmail' => $identity->username,
                'group_alias' => $groupData['group']->alias
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * List of groups
     */
    public function testGroupsMyList() {
        // Create 5 groups for the same owner
        ['user' => $owner, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $groups = [];
        $n = 5;
        for ($i = 0; $i < $n; $i++) {
            $groups[] = \OmegaUp\Test\Factories\Groups::createGroup($identity);
        }

        // Create a group for another user
        \OmegaUp\Test\Factories\Groups::createGroup();

        // Call API
        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Group::apiMyList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]));

        $this->assertEquals($n, count($response['groups']));
    }

    /**
     * Details of a group
     */
    public function testGroupDetails() {
        // Create a group with 5 users
        $groupData = \OmegaUp\Test\Factories\Groups::createGroup();
        $users = [];
        $identities = [];
        $nUsers = 5;
        for ($i = 0; $i < $nUsers; $i++) {
            ['user' => $users[], 'identity' => $identities[]] = \OmegaUp\Test\Factories\User::createUser();
            \OmegaUp\Test\Factories\Groups::addUserToGroup(
                $groupData,
                $identities[$i]
            );
        }

        // Call API
        $login = self::login($groupData['owner']);
        $response = \OmegaUp\Controllers\Group::apiDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'group_alias' => $groupData['group']->alias,
        ]));
        $this->assertEquals(
            $groupData['group']->alias,
            $response['group']['alias']
        );

        $response = \OmegaUp\Controllers\Group::apiMembers(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'group_alias' => $groupData['group']->alias
        ]));
        $this->assertEquals($nUsers, count($response['identities']));
    }

    /**
     * Test add a scoreboard
     */
    public function testCreateScoreboard() {
        $groupData = \OmegaUp\Test\Factories\Groups::createGroup();
        $name = \OmegaUp\Test\Utils::createRandomString();
        $description = \OmegaUp\Test\Utils::createRandomString();
        $alias = \OmegaUp\Test\Utils::createRandomString();

        $login = self::login($groupData['owner']);
        $response = \OmegaUp\Controllers\Group::apiCreateScoreboard(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'group_alias' => $groupData['group']->alias,
            'name' => $name,
            'alias' => $alias,
            'description' => $description
        ]));

        $this->assertEquals('ok', $response['status']);

        $groupScoreboard = \OmegaUp\DAO\GroupsScoreboards::getByAlias($alias);

        $this->assertNotNull($groupScoreboard);
        $this->assertEquals($description, $groupScoreboard->description);
        $this->assertEquals(
            $groupData['group']->group_id,
            $groupScoreboard->group_id
        );
    }

    public function testCreateScoreboardWithInvalidAlias() {
        $groupData = \OmegaUp\Test\Factories\Groups::createGroup();
        $name = \OmegaUp\Test\Utils::createRandomString();
        $description = \OmegaUp\Test\Utils::createRandomString();
        $alias = \OmegaUp\Test\Utils::createRandomString();

        $login = self::login($groupData['owner']);
        try {
            \OmegaUp\Controllers\Group::apiCreateScoreboard(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'group_alias' => $groupData['group']->alias,
                    'name' => \OmegaUp\Test\Utils::createRandomString(),
                    'alias' => 'invalid alias',
                    'description' => \OmegaUp\Test\Utils::createRandomString()
                ])
            );
            $this->fail('Group creation should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals('parameterInvalid', $e->getMessage());
        }
    }

    /**
     * Adding a contest to a scoreboard
     */
    public function testAddContestToScoreboard() {
        $groupData = \OmegaUp\Test\Factories\Groups::createGroup();
        $scoreboardData = \OmegaUp\Test\Factories\Groups::createGroupScoreboard(
            $groupData
        );
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        \OmegaUp\Test\Factories\Contest::addAdminUser(
            $contestData,
            $groupData['owner']
        );

        $login = self::login($groupData['owner']);
        $response = \OmegaUp\Controllers\GroupScoreboard::apiAddContest(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'group_alias' => $groupData['request']['alias'],
            'scoreboard_alias' => $scoreboardData['request']['alias'],
            'contest_alias' => $contestData['request']['alias'],
            'weight' => 1,
            'only_ac' => 0
        ]));

        $this->assertEquals('ok', $response['status']);

        $gsc = \OmegaUp\DAO\GroupsScoreboardsProblemsets::getByPK(
            $scoreboardData['scoreboard']->group_scoreboard_id,
            $contestData['contest']->problemset_id
        );

        $this->assertNotNull($gsc);
    }

    /**
     * Adding a contest to a scoreboard not being contest admin
     */
    public function testAddContestToScoreboardNoContestAdmin() {
        $groupData = \OmegaUp\Test\Factories\Groups::createGroup();
        $scoreboardData = \OmegaUp\Test\Factories\Groups::createGroupScoreboard(
            $groupData
        );
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );

        $login = self::login($groupData['owner']);
        try {
            \OmegaUp\Controllers\GroupScoreboard::apiAddContest(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'group_alias' => $groupData['request']['alias'],
                'scoreboard_alias' => $scoreboardData['request']['alias'],
                'contest_alias' => $contestData['request']['alias']
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Removes contest from scoreboard
     */
    public function testRemoveContestFromScoreboard() {
        $groupData = \OmegaUp\Test\Factories\Groups::createGroup();
        $scoreboardData = \OmegaUp\Test\Factories\Groups::createGroupScoreboard(
            $groupData
        );
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        \OmegaUp\Test\Factories\Contest::addAdminUser(
            $contestData,
            $groupData['owner']
        );

        \OmegaUp\Test\Factories\Groups::addContestToScoreboard(
            $contestData,
            $scoreboardData,
            $groupData
        );

        $login = self::login($groupData['owner']);
        $response = \OmegaUp\Controllers\GroupScoreboard::apiRemoveContest(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'group_alias' => $groupData['request']['alias'],
            'scoreboard_alias' => $scoreboardData['request']['alias'],
            'contest_alias' => $contestData['request']['alias']
        ]));

        $this->assertEquals('ok', $response['status']);

        $gscs = \OmegaUp\DAO\GroupsScoreboardsProblemsets::getByPK(
            $scoreboardData['scoreboard']->group_scoreboard_id,
            $contestData['contest']->problemset_id
        );

        $this->assertNull($gscs);
    }

    /**
     * apiDetails
     */
    public function testScoreboardDetails() {
        $groupData = \OmegaUp\Test\Factories\Groups::createGroup();
        $scoreboardData = \OmegaUp\Test\Factories\Groups::createGroupScoreboard(
            $groupData
        );
        $contestsData = [];

        // Create contestants to submit runs
        ['user' => $contestantInGroup, 'identity' => $identityInGroup] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Groups::addUserToGroup(
            $groupData,
            $identityInGroup
        );
        ['user' => $contestantNotInGroup, 'identity' => $identityNotInGroup] = \OmegaUp\Test\Factories\User::createUser();

        $n = 5;

        for ($i = 0; $i < $n; $i++) {
            $contestsData[] = \OmegaUp\Test\Factories\Contest::createContest();
            \OmegaUp\Test\Factories\Contest::addAdminUser(
                $contestsData[$i],
                $groupData['owner']
            );
            \OmegaUp\Test\Factories\Groups::addContestToScoreboard(
                $contestsData[$i],
                $scoreboardData,
                $groupData
            );

            // Create a problem to solve
            $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
            \OmegaUp\Test\Factories\Contest::addProblemToContest(
                $problemData,
                $contestsData[$i]
            );

            // Submit runs
            $run1 = \OmegaUp\Test\Factories\Run::createRun(
                $problemData,
                $contestsData[$i],
                $identityInGroup
            );
            $run2 = \OmegaUp\Test\Factories\Run::createRun(
                $problemData,
                $contestsData[$i],
                $identityNotInGroup
            );
            \OmegaUp\Test\Factories\Run::gradeRun($run1);
            \OmegaUp\Test\Factories\Run::gradeRun($run2);
        }

        $login = self::login($groupData['owner']);
        $response = \OmegaUp\Controllers\GroupScoreboard::apiDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'group_alias' => $groupData['request']['alias'],
            'scoreboard_alias' => $scoreboardData['request']['alias'],
        ]));

        $this->assertEquals($n, count($response['contests']));
        $this->assertEquals(
            $scoreboardData['request']['alias'],
            $response['scoreboard']['alias']
        );

        // Only 1 user in the merged scoreboard is expected
        $this->assertEquals(1, count($response['ranking']));
        $this->assertEquals($n, count($response['ranking'][0]['contests']));
    }

    /**
     * apiList
     */
    public function testScoreboardsList() {
        $groupData = \OmegaUp\Test\Factories\Groups::createGroup();
        $n = 5;
        $scoreboardsData = [];
        for ($i = 0; $i < $n; $i++) {
            $scoreboardsData[] = \OmegaUp\Test\Factories\Groups::createGroupScoreboard(
                $groupData
            );
        }

        $login = self::login($groupData['owner']);
        $response = \OmegaUp\Controllers\GroupScoreboard::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'group_alias' => $groupData['request']['alias'],
        ]));

        $this->assertEquals($n, count($response['scoreboards']));
    }

    /**
     * apiDetails with only AC and Weights
     */
    public function testScoreboardDetailsOnlyAcAndWeight() {
        $groupData = \OmegaUp\Test\Factories\Groups::createGroup();
        $scoreboardData = \OmegaUp\Test\Factories\Groups::createGroupScoreboard(
            $groupData
        );
        $contestsData = [];

        // Create contestants to submit runs
        ['user' => $contestantInGroup, 'identity' => $identityInGroup] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Groups::addUserToGroup(
            $groupData,
            $identityInGroup
        );
        ['user' => $contestantInGroupNoAc, 'identity' => $identityInGroupNoAc] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Groups::addUserToGroup(
            $groupData,
            $identityInGroupNoAc
        );

        $n = 5;

        for ($i = 0; $i < $n; $i++) {
            $contestsData[] = \OmegaUp\Test\Factories\Contest::createContest();
            \OmegaUp\Test\Factories\Contest::addAdminUser(
                $contestsData[$i],
                $groupData['owner']
            );
            \OmegaUp\Test\Factories\Groups::addContestToScoreboard(
                $contestsData[$i],
                $scoreboardData,
                $groupData,
                1 /*onlyAC*/,
                ($i === 0 ? 3 : 1)
            );

            // Create a problem to solve
            $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
            \OmegaUp\Test\Factories\Contest::addProblemToContest(
                $problemData,
                $contestsData[$i]
            );

            // Submit runs
            $run1 = \OmegaUp\Test\Factories\Run::createRun(
                $problemData,
                $contestsData[$i],
                $identityInGroup
            );
            $run2 = \OmegaUp\Test\Factories\Run::createRun(
                $problemData,
                $contestsData[$i],
                $identityInGroupNoAc
            );
            \OmegaUp\Test\Factories\Run::gradeRun($run1);
            \OmegaUp\Test\Factories\Run::gradeRun($run2, 0.5, 'PA');
        }

        $login = self::login($groupData['owner']);
        $response = \OmegaUp\Controllers\GroupScoreboard::apiDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'group_alias' => $groupData['request']['alias'],
            'scoreboard_alias' => $scoreboardData['request']['alias'],
        ]));

        $this->assertEquals($n, count($response['contests']));
        $this->assertEquals(
            $scoreboardData['request']['alias'],
            $response['scoreboard']['alias']
        );

        // 2 users in the merged scoreboard is expected
        $this->assertEquals(2, count($response['ranking']));
        $this->assertEquals($n, count($response['ranking'][0]['contests']));

        // Only AC is expected
        $this->assertEquals(
            100,
            $response['ranking'][0]['contests'][$contestsData[1]['request']['alias']]['points']
        );
        $this->assertEquals(
            0,
            $response['ranking'][1]['contests'][$contestsData[1]['request']['alias']]['points']
        );

        // Weight x3 in the first contest for 1st user
        $this->assertEquals(
            300,
            $response['ranking'][0]['contests'][$contestsData[0]['request']['alias']]['points']
        );
        $this->assertEquals(700, $response['ranking'][0]['total']['points']);
    }
}
