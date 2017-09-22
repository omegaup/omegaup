<?php

/**
 * GroupsTest
 *
 * @author joemmanuel
 */

class GroupsTest extends OmegaupTestCase {
    private $contestFactory;

    public function __construct() {
        $this->contestFactory = new ContestsFactory(new ContestsParams([]));
    }
    /**
     * Basic create group test
     */
    public function testCreateGroup() {
        $owner = UserFactory::createUser();
        $name = Utils::CreateRandomString();
        $description = Utils::CreateRandomString();
        $alias = Utils::CreateRandomString();

        $login = self::login($owner);
        $response = GroupController::apiCreate(new Request([
            'auth_token' => $login->auth_token,
            'name' => $name,
            'alias' => $alias,
            'description' => $description
        ]));

        $this->assertEquals('ok', $response['status']);

        $groups = GroupsDAO::search(new Groups([
            'name' => $name
        ]));
        $group = $groups[0];
        $this->assertNotNull($group);
        $this->assertEquals($description, $group->description);
        $this->assertTrue(Authorization::isGroupAdmin($owner->user_id, $group));
    }

    /**
     * Add user to group
     */
    public function testAddUserToGroup() {
        $group = GroupsFactory::createGroup();
        $user = UserFactory::createUser();

        $login = self::login($group['owner']);
        $response = GroupController::apiAddUser(new Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $user->username,
            'group_alias' => $group['group']->alias
        ]));
        $this->assertEquals('ok', $response['status']);

        $group_users = GroupsUsersDAO::getByPK($group['group']->group_id, $user->user_id);
        $this->assertNotNull($group_users);
    }

    /**
     * Add user to group
     *
     * @expectedException ForbiddenAccessException
     */
    public function testAddUserToGroupNotOwned() {
        $group = GroupsFactory::createGroup();
        $user = UserFactory::createUser();
        $userCalling = UserFactory::createUser();

        $login = self::login($userCalling);
        $response = GroupController::apiAddUser(new Request([
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
        $user = UserFactory::createUser();
        GroupsFactory::addUserToGroup($groupData, $user);

        $login = self::login($groupData['owner']);
        $response = GroupController::apiRemoveUser(new Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $user->username,
            'group_alias' => $groupData['group']->alias
        ]));

        $this->assertEquals('ok', $response['status']);

        $group_users = GroupsUsersDAO::getByPK($groupData['group']->group_id, $user->user_id);
        $this->assertNull($group_users);
    }

    /**
     * Remove user from group test
     *
     * @expectedException InvalidParameterException
     */
    public function testRemoveUserFromGroupUserNotInGroup() {
        $groupData = GroupsFactory::createGroup();
        $user = UserFactory::createUser();

        $login = self::login($groupData['owner']);
        GroupController::apiRemoveUser(new Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $user->username,
            'group_alias' => $groupData['group']->alias
        ]));
    }

    /**
     * Remove user from group test
     *
     * @expectedException ForbiddenAccessException
     */
    public function testRemoveUserFromGroupUserNotOwner() {
        $groupData = GroupsFactory::createGroup();
        $user = UserFactory::createUser();

        $login = self::login($user);
        GroupController::apiRemoveUser(new Request([
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
        $owner = UserFactory::createUser();
        $groups = [];
        $n = 5;
        for ($i = 0; $i < $n; $i++) {
            $groups[] = GroupsFactory::createGroup($owner);
        }

        // Create a group for another user
        GroupsFactory::createGroup();

        // Call API
        $login = self::login($owner);
        $response = GroupController::apiMyList(new Request([
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
        $nUsers = 5;
        for ($i = 0; $i < $nUsers; $i++) {
            $users[] = UserFactory::createUser();
            GroupsFactory::addUserToGroup($groupData, $users[$i]);
        }

        // Call API
        $login = self::login($groupData['owner']);
        $response = GroupController::apiDetails(new Request([
            'auth_token' => $login->auth_token,
            'group_alias' => $groupData['group']->alias,
        ]));
        $this->assertEquals($groupData['group']->group_id, $response['group']['group_id']);

        $response = GroupController::apiMembers(new Request([
            'auth_token' => $login->auth_token,
            'group_alias' => $groupData['group']->alias
        ]));
        $this->assertEquals($nUsers, count($response['users']));
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
        $response = GroupController::apiCreateScoreboard(new Request([
            'auth_token' => $login->auth_token,
            'group_alias' => $groupData['group']->alias,
            'name' => $name,
            'alias' => $alias,
            'description' => $description
        ]));

        $this->assertEquals('ok', $response['status']);

        $groupScoreboards = GroupsScoreboardsDAO::search(new GroupsScoreboards([
            'alias' => $alias
        ]));

        $groupScoreboard = $groupScoreboards[0];
        $this->assertNotNull($groupScoreboard);
        $this->assertEquals($description, $groupScoreboard->description);
        $this->assertEquals($groupData['group']->group_id, $groupScoreboard->group_id);
    }

    /**
     * Adding a contest to a scoreboard
     */
    public function testAddContestToScoreboard() {
        $groupData = GroupsFactory::createGroup();
        $scoreboardData = GroupsFactory::createGroupScoreboard($groupData);
        $contestData = $this->contestFactory->createContest();
        ContestsFactory::addAdminUser($contestData, $groupData['owner']);

        $login = self::login($groupData['owner']);
        $response = GroupScoreboardController::apiAddContest(new Request([
            'auth_token' => $login->auth_token,
            'group_alias' => $groupData['request']['alias'],
            'scoreboard_alias' => $scoreboardData['request']['alias'],
            'contest_alias' => $contestData['request']['alias'],
            'weight' => 1,
            'only_ac' => 0
        ]));

        $this->assertEquals('ok', $response['status']);

        $gscs = GroupsScoreboardsContestsDAO::search(new GroupsScoreboardsContests([
            'group_scoreboard_id' => $scoreboardData['scoreboard']->group_scoreboard_id,
            'contest_id' => $contestData['contest']->contest_id
        ]));
        $gsc = $gscs[0];

        $this->assertNotNull($gsc);
    }

    /**
     * Adding a contest to a scoreboard not being contest admin
     *
     * @expectedException ForbiddenAccessException
     */
    public function testAddContestToScoreboardNoContestAdmin() {
        $groupData = GroupsFactory::createGroup();
        $scoreboardData = GroupsFactory::createGroupScoreboard($groupData);
        $contestFactoryPublic = new ContestsFactory(new ContestsParams(['public' => 0]));
        $contestData = $contestFactoryPublic->createContest();

        $login = self::login($groupData['owner']);
        GroupScoreboardController::apiAddContest(new Request([
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
        $contestData = $this->contestFactory->createContest();
        ContestsFactory::addAdminUser($contestData, $groupData['owner']);

        GroupsFactory::addContestToScoreboard($contestData, $scoreboardData, $groupData);

        $login = self::login($groupData['owner']);
        $response = GroupScoreboardController::apiRemoveContest(new Request([
            'auth_token' => $login->auth_token,
            'group_alias' => $groupData['request']['alias'],
            'scoreboard_alias' => $scoreboardData['request']['alias'],
            'contest_alias' => $contestData['request']['alias']
        ]));

        $this->assertEquals('ok', $response['status']);

        $gscs = GroupsScoreboardsContestsDAO::search(new GroupsScoreboardsContests([
            'group_scoreboard_id' => $scoreboardData['scoreboard']->group_scoreboard_id,
            'contest_id' => $contestData['contest']->contest_id
        ]));

        $this->assertEquals(0, count($gscs));
    }

    /**
     * apiDetails
     */
    public function testScoreboardDetails() {
        $groupData = GroupsFactory::createGroup();
        $scoreboardData = GroupsFactory::createGroupScoreboard($groupData);
        $contestsData = [];

        // Create contestants to submit runs
        $contestantInGroup = UserFactory::createUser();
        GroupsFactory::addUserToGroup($groupData, $contestantInGroup);
        $contestantNotInGroup = UserFactory::createUser();

        $n = 5;

        for ($i = 0; $i < $n; $i++) {
            $contestFactory = new ContestsFactory(new ContestsParams([]));
            $contestsData[] = $contestFactory->createContest();
            ContestsFactory::addAdminUser($contestsData[$i], $groupData['owner']);
            GroupsFactory::addContestToScoreboard($contestsData[$i], $scoreboardData, $groupData);

            // Create a problem to solve
            $problemData = ProblemsFactory::createProblem();
            ContestsFactory::addProblemToContest($problemData, $contestsData[$i]);

            // Submit runs
            $run1 = RunsFactory::createRun($problemData, $contestsData[$i], $contestantInGroup);
            $run2 = RunsFactory::createRun($problemData, $contestsData[$i], $contestantNotInGroup);
            RunsFactory::gradeRun($run1);
            RunsFactory::gradeRun($run2);
        }

        $login = self::login($groupData['owner']);
        $response = GroupScoreboardController::apiDetails(new Request([
            'auth_token' => $login->auth_token,
            'group_alias' => $groupData['request']['alias'],
            'scoreboard_alias' => $scoreboardData['request']['alias'],
        ]));

        $this->assertEquals($n, count($response['contests']));
        $this->assertEquals($scoreboardData['request']['alias'], $response['scoreboard']['alias']);

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
            $scoreboardsData[] = GroupsFactory::createGroupScoreboard($groupData);
        }

        $login = self::login($groupData['owner']);
        $response = GroupScoreboardController::apiList(new Request([
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
        $contestantInGroup = UserFactory::createUser();
        GroupsFactory::addUserToGroup($groupData, $contestantInGroup);
        $contestantInGroupNoAc = UserFactory::createUser();
        GroupsFactory::addUserToGroup($groupData, $contestantInGroupNoAc);

        $n = 5;

        for ($i = 0; $i < $n; $i++) {
            $contestFactory = new ContestsFactory(new ContestsParams([]));
            $contestsData[] = $contestFactory->createContest();
            ContestsFactory::addAdminUser($contestsData[$i], $groupData['owner']);
            GroupsFactory::addContestToScoreboard($contestsData[$i], $scoreboardData, $groupData, 1 /*onlyAC*/, ($i === 0 ? 3 : 1));

            // Create a problem to solve
            $problemData = ProblemsFactory::createProblem();
            ContestsFactory::addProblemToContest($problemData, $contestsData[$i]);

            // Submit runs
            $run1 = RunsFactory::createRun($problemData, $contestsData[$i], $contestantInGroup);
            $run2 = RunsFactory::createRun($problemData, $contestsData[$i], $contestantInGroupNoAc);
            RunsFactory::gradeRun($run1);
            RunsFactory::gradeRun($run2, 0.5, 'PA');
        }

        $login = self::login($groupData['owner']);
        $response = GroupScoreboardController::apiDetails(new Request([
            'auth_token' => $login->auth_token,
            'group_alias' => $groupData['request']['alias'],
            'scoreboard_alias' => $scoreboardData['request']['alias'],
        ]));

        $this->assertEquals($n, count($response['contests']));
        $this->assertEquals($scoreboardData['request']['alias'], $response['scoreboard']['alias']);

        // 2 users in the merged scoreboard is expected
        $this->assertEquals(2, count($response['ranking']));
        $this->assertEquals($n, count($response['ranking'][0]['contests']));

        // Only AC is expected
        $this->assertEquals(100, $response['ranking'][0]['contests'][$contestsData[1]['request']['alias']]['points']);
        $this->assertEquals(0, $response['ranking'][1]['contests'][$contestsData[1]['request']['alias']]['points']);

        // Weight x3 in the first contest for 1st user
        $this->assertEquals(300, $response['ranking'][0]['contests'][$contestsData[0]['request']['alias']]['points']);
        $this->assertEquals(700, $response['ranking'][0]['total']['points']);
    }
}
