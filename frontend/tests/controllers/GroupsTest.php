<?php

/**
 * GroupsTest
 *
 * @author joemmanuel
 */

class GroupsTest extends OmegaupTestCase {
    /**
     * Basic create group test
     */
    public function testCreateGroup() {
        ['user' => $owner, 'identity' => $identity] = UserFactory::createUser();
        $name = Utils::CreateRandomString();
        $description = Utils::CreateRandomString();
        $alias = Utils::CreateRandomString();

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
     * Attempts to create groups with a restricted alias should fail.
     */
    public function testCreateGroupRestrictedAlias() {
        ['user' => $owner, 'identity' => $identity] = UserFactory::createUser();

        try {
            $login = self::login($identity);
            \OmegaUp\Controllers\Group::apiCreate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => Utils::CreateRandomString(),
                'alias' => 'omegaup',
                'description' => Utils::CreateRandomString(),
            ]));
            $this->fail('Group creation should have failed');
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            $this->assertEquals($e->getMessage(), 'aliasInUse');
        }
    }

    /**
     * Add user to group
     */
    public function testAddUserToGroup() {
        $group = GroupsFactory::createGroup();
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        $login = self::login($group['owner']);
        $response = \OmegaUp\Controllers\Group::apiAddUser(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $user->username,
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
     *
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testAddUserToGroupNotOwned() {
        $group = GroupsFactory::createGroup();
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        ['user' => $userCalling, 'identity' => $identityCalling] = UserFactory::createUser();

        $login = self::login($identityCalling);
        $response = \OmegaUp\Controllers\Group::apiAddUser(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $user->username,
            'group_alias' => $group['group']->alias
        ]));
    }

    /**
     * Remove user from group test
     */
    public function testRemoveUserFromGroup() {
        $groupData = GroupsFactory::createGroup();
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        GroupsFactory::addUserToGroup($groupData, $identity);

        $login = self::login($groupData['owner']);
        $response = \OmegaUp\Controllers\Group::apiRemoveUser(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $user->username,
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
     *
     * @expectedException \OmegaUp\Exceptions\InvalidParameterException
     */
    public function testRemoveUserFromGroupUserNotInGroup() {
        $groupData = GroupsFactory::createGroup();
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        $login = self::login($groupData['owner']);
        \OmegaUp\Controllers\Group::apiRemoveUser(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $user->username,
            'group_alias' => $groupData['group']->alias
        ]));
    }

    /**
     * Remove user from group test
     *
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testRemoveUserFromGroupUserNotOwner() {
        $groupData = GroupsFactory::createGroup();
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        $login = self::login($identity);
        \OmegaUp\Controllers\Group::apiRemoveUser(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $user->username,
            'group_alias' => $groupData['group']->alias
        ]));
    }

    /**
     * List of groups
     */
    public function testGroupsMyList() {
        // Create 5 groups for the same owner
        ['user' => $owner, 'identity' => $identity] = UserFactory::createUser();
        $groups = [];
        $n = 5;
        for ($i = 0; $i < $n; $i++) {
            $groups[] = GroupsFactory::createGroup($identity);
        }

        // Create a group for another user
        GroupsFactory::createGroup();

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
        $groupData = GroupsFactory::createGroup();
        $users = [];
        $identities = [];
        $nUsers = 5;
        for ($i = 0; $i < $nUsers; $i++) {
            ['user' => $users[], 'identity' => $identities[]] = UserFactory::createUser();
            GroupsFactory::addUserToGroup($groupData, $identities[$i]);
        }

        // Call API
        $login = self::login($groupData['owner']);
        $response = \OmegaUp\Controllers\Group::apiDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'group_alias' => $groupData['group']->alias,
        ]));
        $this->assertEquals(
            $groupData['group']->group_id,
            $response['group']['group_id']
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
        $groupData = GroupsFactory::createGroup();
        $name = Utils::CreateRandomString();
        $description = Utils::CreateRandomString();
        $alias = Utils::CreateRandomString();

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

    /**
     * Adding a contest to a scoreboard
     */
    public function testAddContestToScoreboard() {
        $groupData = GroupsFactory::createGroup();
        $scoreboardData = GroupsFactory::createGroupScoreboard($groupData);
        $contestData = ContestsFactory::createContest();
        ContestsFactory::addAdminUser($contestData, $groupData['owner']);

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
     *
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testAddContestToScoreboardNoContestAdmin() {
        $groupData = GroupsFactory::createGroup();
        $scoreboardData = GroupsFactory::createGroupScoreboard($groupData);
        $contestData = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
            )
        );

        $login = self::login($groupData['owner']);
        \OmegaUp\Controllers\GroupScoreboard::apiAddContest(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'group_alias' => $groupData['request']['alias'],
            'scoreboard_alias' => $scoreboardData['request']['alias'],
            'contest_alias' => $contestData['request']['alias']
        ]));
    }

    /**
     * Removes contest from scoreboard
     */
    public function testRemoveContestFromScoreboard() {
        $groupData = GroupsFactory::createGroup();
        $scoreboardData = GroupsFactory::createGroupScoreboard($groupData);
        $contestData = ContestsFactory::createContest();
        ContestsFactory::addAdminUser($contestData, $groupData['owner']);

        GroupsFactory::addContestToScoreboard(
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
        $groupData = GroupsFactory::createGroup();
        $scoreboardData = GroupsFactory::createGroupScoreboard($groupData);
        $contestsData = [];

        // Create contestants to submit runs
        ['user' => $contestantInGroup, 'identity' => $identityInGroup] = UserFactory::createUser();
        GroupsFactory::addUserToGroup($groupData, $identityInGroup);
        ['user' => $contestantNotInGroup, 'identity' => $identityNotInGroup] = UserFactory::createUser();

        $n = 5;

        for ($i = 0; $i < $n; $i++) {
            $contestsData[] = ContestsFactory::createContest();
            ContestsFactory::addAdminUser(
                $contestsData[$i],
                $groupData['owner']
            );
            GroupsFactory::addContestToScoreboard(
                $contestsData[$i],
                $scoreboardData,
                $groupData
            );

            // Create a problem to solve
            $problemData = ProblemsFactory::createProblem();
            ContestsFactory::addProblemToContest(
                $problemData,
                $contestsData[$i]
            );

            // Submit runs
            $run1 = RunsFactory::createRun(
                $problemData,
                $contestsData[$i],
                $identityInGroup
            );
            $run2 = RunsFactory::createRun(
                $problemData,
                $contestsData[$i],
                $identityNotInGroup
            );
            RunsFactory::gradeRun($run1);
            RunsFactory::gradeRun($run2);
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
        $groupData = GroupsFactory::createGroup();
        $n = 5;
        $scoreboardsData = [];
        for ($i = 0; $i < $n; $i++) {
            $scoreboardsData[] = GroupsFactory::createGroupScoreboard(
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
        $groupData = GroupsFactory::createGroup();
        $scoreboardData = GroupsFactory::createGroupScoreboard($groupData);
        $contestsData = [];

        // Create contestants to submit runs
        ['user' => $contestantInGroup, 'identity' => $identityInGroup] = UserFactory::createUser();
        GroupsFactory::addUserToGroup($groupData, $identityInGroup);
        ['user' => $contestantInGroupNoAc, 'identity' => $identityInGroupNoAc] = UserFactory::createUser();
        GroupsFactory::addUserToGroup($groupData, $identityInGroupNoAc);

        $n = 5;

        for ($i = 0; $i < $n; $i++) {
            $contestsData[] = ContestsFactory::createContest();
            ContestsFactory::addAdminUser(
                $contestsData[$i],
                $groupData['owner']
            );
            GroupsFactory::addContestToScoreboard(
                $contestsData[$i],
                $scoreboardData,
                $groupData,
                1 /*onlyAC*/,
                ($i === 0 ? 3 : 1)
            );

            // Create a problem to solve
            $problemData = ProblemsFactory::createProblem();
            ContestsFactory::addProblemToContest(
                $problemData,
                $contestsData[$i]
            );

            // Submit runs
            $run1 = RunsFactory::createRun(
                $problemData,
                $contestsData[$i],
                $identityInGroup
            );
            $run2 = RunsFactory::createRun(
                $problemData,
                $contestsData[$i],
                $identityInGroupNoAc
            );
            RunsFactory::gradeRun($run1);
            RunsFactory::gradeRun($run2, 0.5, 'PA');
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
