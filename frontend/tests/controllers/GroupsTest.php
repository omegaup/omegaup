<?php
/**
 * GroupsTest
 */

class GroupsTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Basic create group test
     */
    public function testCreateGroup() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
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

        $this->assertSame('ok', $response['status']);

        $group = \OmegaUp\DAO\Groups::getByName($name);
        $this->assertNotNull($group);
        $this->assertSame($description, $group->description);
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

        $this->assertSame('ok', $response['status']);

        $group = \OmegaUp\DAO\Groups::findByAlias($alias);

        $this->assertNotNull($group);
        $this->assertSame($updatedDescription, $group->description);
        $this->assertSame($updatedName, $group->name);
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
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Attempts to create groups with a restricted alias should fail.
     */
    public function testCreateGroupRestrictedAlias() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

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
            $this->assertSame('parameterInvalid', $e->getMessage());
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
            $this->assertSame('parameterInvalid', $e->getMessage());
        }
    }

    /**
     * Add identity to group
     */
    public function testAddUserToGroup() {
        $group = \OmegaUp\Test\Factories\Groups::createGroup();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($group['owner']);
        $response = \OmegaUp\Controllers\Group::apiAddUser(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $identity->username,
            'group_alias' => $group['group']->alias
        ]));
        $this->assertSame('ok', $response['status']);

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
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $identityCalling] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identityCalling);
        try {
            \OmegaUp\Controllers\Group::apiAddUser(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'usernameOrEmail' => $identity->username,
                'group_alias' => $group['group']->alias
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
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

        $this->assertSame('ok', $response['status']);

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
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($groupData['owner']);
        try {
            \OmegaUp\Controllers\Group::apiRemoveUser(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'usernameOrEmail' => $identity->username,
                'group_alias' => $groupData['group']->alias
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterNotFound', $e->getMessage());
            $this->assertSame('User', $e->parameter);
        }
    }

    /**
     * Remove user from group test
     */
    public function testRemoveUserFromGroupUserNotOwner() {
        $groupData = \OmegaUp\Test\Factories\Groups::createGroup();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        try {
            \OmegaUp\Controllers\Group::apiRemoveUser(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'usernameOrEmail' => $identity->username,
                'group_alias' => $groupData['group']->alias
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * List of groups
     */
    public function testGroupsMyList() {
        // Create 5 groups for the same owner
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
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

        $this->assertSame($n, count($response['groups']));
    }

    /**
     * Details of a group
     */
    public function testGroupDetails() {
        // Create a group with 5 users
        $groupData = \OmegaUp\Test\Factories\Groups::createGroup();
        $identities = [];
        $nUsers = 5;
        for ($i = 0; $i < $nUsers; $i++) {
            ['identity' => $identities[]] = \OmegaUp\Test\Factories\User::createUser();
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
        $this->assertSame(
            $groupData['group']->alias,
            $response['group']['alias']
        );

        $response = \OmegaUp\Controllers\Group::apiMembers(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'group_alias' => $groupData['group']->alias
        ]));
        $this->assertSame($nUsers, count($response['identities']));
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

        $this->assertSame('ok', $response['status']);

        $groupScoreboard = \OmegaUp\DAO\GroupsScoreboards::getByAlias($alias);

        $this->assertNotNull($groupScoreboard);
        $this->assertSame($description, $groupScoreboard->description);
        $this->assertSame(
            $groupData['group']->group_id,
            $groupScoreboard->group_id
        );
    }

    public function testCreateScoreboardWithInvalidAlias() {
        $groupData = \OmegaUp\Test\Factories\Groups::createGroup();

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
            $this->assertSame('parameterInvalid', $e->getMessage());
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

        $this->assertSame('ok', $response['status']);

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
            $this->assertSame('userNotAllowed', $e->getMessage());
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

        $this->assertSame('ok', $response['status']);

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
        ['identity' => $identityInGroup] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Groups::addUserToGroup(
            $groupData,
            $identityInGroup
        );
        ['identity' => $identityNotInGroup] = \OmegaUp\Test\Factories\User::createUser();

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

        $this->assertSame($n, count($response['contests']));
        $this->assertSame(
            $scoreboardData['request']['alias'],
            $response['scoreboard']['alias']
        );

        // Only 1 user in the merged scoreboard is expected
        $this->assertSame(1, count($response['ranking']));
        $this->assertSame($n, count($response['ranking'][0]['contests']));
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

        $this->assertSame($n, count($response['scoreboards']));
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
        ['identity' => $identityInGroup] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Groups::addUserToGroup(
            $groupData,
            $identityInGroup
        );
        ['identity' => $identityInGroupNoAc] = \OmegaUp\Test\Factories\User::createUser();
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

        $this->assertSame($n, count($response['contests']));
        $this->assertSame(
            $scoreboardData['request']['alias'],
            $response['scoreboard']['alias']
        );

        // 2 users in the merged scoreboard is expected
        $this->assertSame(2, count($response['ranking']));
        $this->assertSame($n, count($response['ranking'][0]['contests']));

        // Only AC is expected
        $this->assertSame(
            100.0,
            $response['ranking'][0]['contests'][$contestsData[1]['request']['alias']]['points']
        );
        $this->assertSame(
            0.0,
            $response['ranking'][1]['contests'][$contestsData[1]['request']['alias']]['points']
        );

        // Weight x3 in the first contest for 1st user
        $this->assertSame(
            300.0,
            $response['ranking'][0]['contests'][$contestsData[0]['request']['alias']]['points']
        );
        $this->assertSame(700.0, $response['ranking'][0]['total']['points']);
    }

    /**
     * apiDetails with only AC and Weights
     */
    public function testScoreboardDetailsForTypeScript() {
        $groupData = \OmegaUp\Test\Factories\Groups::createGroup();
        $scoreboardData = \OmegaUp\Test\Factories\Groups::createGroupScoreboard(
            $groupData
        );
        $contestsData = [];

        // Create contestants to submit runs
        ['identity' => $identityInGroup] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Groups::addUserToGroup(
            $groupData,
            $identityInGroup
        );
        ['identity' => $identityInGroupNoAc] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Groups::addUserToGroup(
            $groupData,
            $identityInGroupNoAc
        );

        $numberOfContests = 5;
        // Create five contests
        foreach (range(0, $numberOfContests - 1) as $id) {
            $contestsData[] = \OmegaUp\Test\Factories\Contest::createContest();
            \OmegaUp\Test\Factories\Contest::addAdminUser(
                $contestsData[$id],
                $groupData['owner']
            );
            \OmegaUp\Test\Factories\Groups::addContestToScoreboard(
                $contestsData[$id],
                $scoreboardData,
                $groupData,
                1 /*onlyAC*/,
                ($id === 0 ? 3 : 1)
            );

            // Create a problem to solve
            $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
            \OmegaUp\Test\Factories\Contest::addProblemToContest(
                $problemData,
                $contestsData[$id]
            );

            // Submit runs
            $run1 = \OmegaUp\Test\Factories\Run::createRun(
                $problemData,
                $contestsData[$id],
                $identityInGroup
            );
            $run2 = \OmegaUp\Test\Factories\Run::createRun(
                $problemData,
                $contestsData[$id],
                $identityInGroupNoAc
            );
            \OmegaUp\Test\Factories\Run::gradeRun($run1);
            \OmegaUp\Test\Factories\Run::gradeRun($run2, 0.5, 'PA');
        }

        $login = self::login($groupData['owner']);
        $response = \OmegaUp\Controllers\GroupScoreboard::getGroupScoreboardDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'group' => $groupData['request']['alias'],
                'scoreboard' => $scoreboardData['request']['alias'],
            ])
        )['templateProperties']['payload']['details'];

        $this->assertCount($numberOfContests, $response['contests']);
        $this->assertSame(
            $scoreboardData['request']['alias'],
            $response['scoreboard']['alias']
        );

        // 2 users in the merged scoreboard is expected
        $this->assertCount(2, $response['ranking']);
        $this->assertCount(
            $numberOfContests,
            $response['ranking'][0]['contests']
        );

        // Only AC is expected
        $this->assertSame(
            100.0,
            $response['ranking'][0]['contests'][$contestsData[1]['request']['alias']]['points']
        );
        $this->assertSame(
            0.0,
            $response['ranking'][1]['contests'][$contestsData[1]['request']['alias']]['points']
        );

        // Weight x3 in the first contest for 1st user
        $this->assertSame(
            300.0,
            $response['ranking'][0]['contests'][$contestsData[0]['request']['alias']]['points']
        );
        $this->assertSame(700.0, $response['ranking'][0]['total']['points']);
    }

    public function testGroupApiList() {
        ['identity' => $owner] = \OmegaUp\Test\Factories\User::createUser();
        $name = '3a ofmi';
        $description = 'Contestants for 3a ofmi';
        $alias = '3a-ofmi';
        \OmegaUp\Test\Factories\Groups::createGroup(
            $owner,
            $name,
            $description,
            $alias
        );
        $name = '3a OFMI 2023';
        $description = 'Contestants for 3a OFMI 2023';
        $alias = '3a-OFMI-2023';
        \OmegaUp\Test\Factories\Groups::createGroup(
            $owner,
            $name,
            $description,
            $alias
        );

        $login = self::login($owner);

        $response = \OmegaUp\Controllers\Group::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'query' => 'ofmi',
        ]));

        // Both groups should be listed
        $this->assertEqualsCanonicalizing(
            [
                [
                    'label' => '3a ofmi',
                    'value' => '3a-ofmi',
                ],
                [
                    'label' => '3a OFMI 2023',
                    'value' => '3a-OFMI-2023',
                ],
            ],
            $response
        );

        $response = \OmegaUp\Controllers\Group::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'query' => '3a-',
        ]));

        // Both groups should be listed
        $this->assertEqualsCanonicalizing(
            [
                [
                    'label' => '3a ofmi',
                    'value' => '3a-ofmi',
                ],
                [
                    'label' => '3a OFMI 2023',
                    'value' => '3a-OFMI-2023',
                ],
            ],
            $response
        );
        $response = \OmegaUp\Controllers\Group::apiList(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'query' => '2023',
        ]));

        // In this case, only one group matches with the query
        $this->assertEqualsCanonicalizing(
            [
                [
                    'label' => '3a OFMI 2023',
                    'value' => '3a-OFMI-2023',
                ],
            ],
            $response
        );
    }
}
