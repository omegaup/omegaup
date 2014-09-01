<?php

/**
 * CreateContestTest
 *
 * @author joemmanuel
 */

class GroupsTest extends OmegaupTestCase {
	
	/**
	 * Basic create group test
	 */
	public function testCreateGroup() {
		$owner = UserFactory::createUser();
		$name = Utils::CreateRandomString();
		$description = Utils::CreateRandomString();
		$alias = Utils::CreateRandomString();
		
		$response = GroupController::apiCreate(new Request(array(
			"auth_token" => self::login($owner),
			"name" => $name,
			"alias" => $alias,
			"description" => $description
		)));
		
		$this->assertEquals("ok", $response["status"]);
		
		$groups = GroupsDAO::search(new Groups(array(
			"name" => $name
		)));
		$group = $groups[0];
		$this->assertNotNull($group);
		$this->assertEquals($description, $group->getDescription());
		$this->assertEquals($owner->getUserId(), $group->getOwnerId());
	}
	
	/**
	 * Add user to group
	 */
	public function testAddUserToGroup() {		
		$group = GroupsFactory::createGroup();
		$user = UserFactory::createUser();
						
		$response = GroupController::apiAddUser(new Request(array(
			"auth_token" => self::login($group["owner"]),
			"usernameOrEmail" => $user->username,
			"group_alias" => $group["group"]->alias
		)));
		$this->assertEquals("ok", $response["status"]);
		
		$group_users = GroupsUsersDAO::getByPK($group["group"]->group_id, $user->user_id);
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
						
		$response = GroupController::apiAddUser(new Request(array(
			"auth_token" => self::login($userCalling),
			"usernameOrEmail" => $user->username,
			"group_alias" => $group["group"]->alias
		)));		
	}
	
	/**
	 * Remove user from group test
	 */
	public function testRemoveUserFromGroup() {
		
		$groupData = GroupsFactory::createGroup();
		$user = UserFactory::createUser();
		GroupsFactory::addUserToGroup($groupData, $user);
		
		$response = GroupController::apiRemoveUser(new Request(array(
			"auth_token" => self::login($groupData["owner"]),
			"usernameOrEmail" => $user->username,
			"group_alias" => $groupData["group"]->alias
		)));
		
		$this->assertEquals("ok", $response["status"]);
		
		$group_users = GroupsUsersDAO::getByPK($groupData["group"]->group_id, $user->user_id);
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
		
		GroupController::apiRemoveUser(new Request(array(
			"auth_token" => self::login($groupData["owner"]),
			"usernameOrEmail" => $user->username,
			"group_alias" => $groupData["group"]->alias
		)));			
	}
	
	/**
	 * Remove user from group test
	 * 
	 * @expectedException ForbiddenAccessException
	 */
	public function testRemoveUserFromGroupUserNotOwner() {
		
		$groupData = GroupsFactory::createGroup();
		$user = UserFactory::createUser();		
		
		GroupController::apiRemoveUser(new Request(array(
			"auth_token" => self::login($user),
			"usernameOrEmail" => $user->username,
			"group_alias" => $groupData["group"]->alias
		)));			
	}
	
	/**
	 * List of groups
	 */
	public function testGroupsList() {
		
		// Create 5 groups for the same owner
		$owner = UserFactory::createUser();
		$groups = array();
		$n = 5;
		for ($i = 0; $i < $n; $i++) {
			$groups[] = GroupsFactory::createGroup($owner);
		}
		
		// Create a group for another user
		GroupsFactory::createGroup();
		
		// Call API
		$response = GroupController::apiList(new Request(array(
			"auth_token" => self::login($owner),						
		)));
				
		$this->assertEquals($n, count($response["groups"]));		
	}
	
	/**
	 * Details of a group
	 */
	public function testGroupDetails() {
		// Create a group with 5 users
		$groupData = GroupsFactory::createGroup();
		$users = array();
		$nUsers = 5;
		for ($i = 0; $i < $nUsers; $i++) {
			$users[] = UserFactory::createUser();
			GroupsFactory::addUserToGroup($groupData, $users[$i]);
		}
		
		// Call API
		$response = GroupController::apiDetails(new Request(array(
			"auth_token" => self::login($groupData["owner"]),
			"group_alias" => $groupData["group"]->alias
		)));
				
		$this->assertEquals($nUsers, count($response["users"]));
		$this->assertEquals($groupData["group"]->group_id, $response["group"]["group_id"]);
	}
	
	/**
	 * Test add a scoreboard
	 */	
	public function testCreateScoreboard() {		
		$groupData = GroupsFactory::createGroup();		
		$name = Utils::CreateRandomString();
		$description = Utils::CreateRandomString();
		$alias = Utils::CreateRandomString();
		
		$response = GroupController::apiCreateScoreboard(new Request(array(
			"auth_token" => self::login($groupData["owner"]),
			"group_alias" => $groupData["group"]->alias,
			"name" => $name,
			"alias" => $alias,
			"description" => $description	
		)));
	
		$this->assertEquals("ok", $response["status"]);
		
		$groupScoreboards = GroupsScoreboardsDAO::search(new GroupsScoreboards(array(
			"alias" => $alias
		)));		
		
		$groupScoreboard = $groupScoreboards[0];		
		$this->assertNotNull($groupScoreboard);
		$this->assertEquals($description, $groupScoreboard->getDescription());
		$this->assertEquals($groupData["group"]->group_id, $groupScoreboard->getGroupId());
	}
	
	/**
	 * Adding a contest to a scoreboard
	 */
	public function testAddContestToScoreboard() {
		$groupData = GroupsFactory::createGroup();
		$scoreboardData = GroupsFactory::createGroupScoreboard($groupData);
		$contestData = ContestsFactory::createContest();
		ContestsFactory::addAdminUser($contestData, $groupData["owner"]);
		
		$response = GroupScoreboardController::apiAddContest(new Request(array(
			"auth_token" => self::login($groupData["owner"]),
			"group_alias" => $groupData["request"]["alias"],
			"scoreboard_alias" => $scoreboardData["request"]["alias"],
			"contest_alias" => $contestData["request"]["alias"],
			"weight" => 1,
			"only_ac" => 0
		)));
		
		$this->assertEquals("ok", $response["status"]);
		
		$gscs = GroupsScoreboardsContestsDAO::search(new GroupsScoreboardsContests(array(
			"group_scoreboard_id" => $scoreboardData["scoreboard"]->group_scoreboard_id,
			"contest_id" => $contestData["contest"]->contest_id			
		)));
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
		$contestData = ContestsFactory::createContest(null /*title*/, 0 /*public*/);		
		
		GroupScoreboardController::apiAddContest(new Request(array(
			"auth_token" => self::login($groupData["owner"]),
			"group_alias" => $groupData["request"]["alias"],
			"scoreboard_alias" => $scoreboardData["request"]["alias"],
			"contest_alias" => $contestData["request"]["alias"]
		)));				
	}
	
	/**
	 * Removes contest from scoreboard
	 */
	public function testRemoveContestFromScoreboard() {
		
		$groupData = GroupsFactory::createGroup();
		$scoreboardData = GroupsFactory::createGroupScoreboard($groupData);
		$contestData = ContestsFactory::createContest();
		ContestsFactory::addAdminUser($contestData, $groupData["owner"]);
		
		GroupsFactory::addContestToScoreboard($contestData, $scoreboardData, $groupData);
		
		$response = GroupScoreboardController::apiRemoveContest(new Request(array(
			"auth_token" => self::login($groupData["owner"]),
			"group_alias" => $groupData["request"]["alias"],
			"scoreboard_alias" => $scoreboardData["request"]["alias"],
			"contest_alias" => $contestData["request"]["alias"]
		)));
		
		$this->assertEquals("ok", $response["status"]);
		
		$gscs = GroupsScoreboardsContestsDAO::search(new GroupsScoreboardsContests(array(
			"group_scoreboard_id" => $scoreboardData["scoreboard"]->group_scoreboard_id,
			"contest_id" => $contestData["contest"]->contest_id			
		)));		
		
		$this->assertEquals(0, count($gscs));	
	}
	
	
	/**
	 * apiDetails
	 */
	public function testScoreboardDetails() {
		
		$groupData = GroupsFactory::createGroup();
		$scoreboardData = GroupsFactory::createGroupScoreboard($groupData);
		$contestsData = array();
		
		// Create contestants to submit runs
		$contestantInGroup = UserFactory::createUser();
		GroupsFactory::addUserToGroup($groupData, $contestantInGroup);		
		$contestantNotInGroup = UserFactory::createUser();
						
		$n = 5;
		
		for ($i = 0; $i < $n; $i++) {
			$contestsData[] = ContestsFactory::createContest();
			ContestsFactory::addAdminUser($contestsData[$i], $groupData["owner"]);		
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
		
		$response = GroupScoreboardController::apiDetails(new Request(array(
			"auth_token" => self::login($groupData["owner"]),
			"group_alias" => $groupData["request"]["alias"],
			"scoreboard_alias" => $scoreboardData["request"]["alias"],
		)));
				
		$this->assertEquals($n, count($response["contests"]));
		$this->assertEquals($scoreboardData["request"]["alias"], $response["scoreboard"]["alias"]);		
		
		// Only 1 user in the merged scoreboard is expected		
		$this->assertEquals(1, count($response["ranking"]));
		$this->assertEquals($n, count($response["ranking"][0]["contests"]));
	}
	
	/**
	 * apiList
	 */
	public function testScoreboardsList() {
		
		$groupData = GroupsFactory::createGroup();
		$n = 5;
		$scoreboardsData = array();
		for ($i = 0; $i < $n; $i++) {
			$scoreboardsData[] = GroupsFactory::createGroupScoreboard($groupData);
		}
		
		$response = GroupScoreboardController::apiList(new Request(array(
			"auth_token" => self::login($groupData["owner"]),
			"group_alias" => $groupData["request"]["alias"],			
		)));
				
		$this->assertEquals($n, count($response["scoreboards"]));		
	}
	
	/**
	 * apiDetails with only AC and Weights
	 */
	public function testScoreboardDetailsOnlyAcAndWeight() {
		
		$groupData = GroupsFactory::createGroup();
		$scoreboardData = GroupsFactory::createGroupScoreboard($groupData);
		$contestsData = array();
		
		// Create contestants to submit runs
		$contestantInGroup = UserFactory::createUser();
		GroupsFactory::addUserToGroup($groupData, $contestantInGroup);		
		$contestantInGroupNoAc = UserFactory::createUser();
		GroupsFactory::addUserToGroup($groupData, $contestantInGroupNoAc);		
						
		$n = 5;
		
		for ($i = 0; $i < $n; $i++) {
			$contestsData[] = ContestsFactory::createContest();
			ContestsFactory::addAdminUser($contestsData[$i], $groupData["owner"]);		
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
		
		$response = GroupScoreboardController::apiDetails(new Request(array(
			"auth_token" => self::login($groupData["owner"]),
			"group_alias" => $groupData["request"]["alias"],
			"scoreboard_alias" => $scoreboardData["request"]["alias"],
		)));
				
		$this->assertEquals($n, count($response["contests"]));
		$this->assertEquals($scoreboardData["request"]["alias"], $response["scoreboard"]["alias"]);		
		
		// 2 users in the merged scoreboard is expected		
		$this->assertEquals(2, count($response["ranking"]));
		$this->assertEquals($n, count($response["ranking"][0]["contests"]));
		
		// Only AC is expected		
		$this->assertEquals(100, $response["ranking"][0]["contests"][$contestsData[1]["request"]["alias"]]["points"]);
		$this->assertEquals(0, $response["ranking"][1]["contests"][$contestsData[1]["request"]["alias"]]["points"]);
		
		// Weight x3 in the first contest for 1st user
		$this->assertEquals(300, $response["ranking"][0]["contests"][$contestsData[0]["request"]["alias"]]["points"]);
		$this->assertEquals(700, $response["ranking"][0]["total"]["points"]);
	}
	
}