<?php

/**
 * Description of DetailsContest
 *
 * @author joemmanuel
 */

class DetailsContest extends OmegaupTestCase {

	/**
	 * Insert problems in a contest
	 * 
	 * @param type $contestData
	 * @param type $numOfProblems
	 * @return array array of problemData
	 */
	private function insertProblemsInContest($contestData, $numOfProblems = 3) {

		// Create problems
		$problems = array();
		for ($i = 0; $i < $numOfProblems; $i++) {
			$problems[$i] = ProblemsFactory::createProblem();
			ContestsFactory::addProblemToContest($problems[$i], $contestData);
		}

		return $problems;
	}

	/**
	 * Checks the contest details response
	 * 
	 * @param type $contestData
	 * @param type $problems
	 * @param type $response
	 */
	private function assertContestDetails($contestData, $problems, $response) {

		// To validate, grab the contest object directly from the DB
		$contest = ContestsDAO::getByAlias($contestData["request"]["alias"]);

		// Assert we are getting correct data
		$this->assertEquals($contest->getDescription(), $response["description"]);
		$this->assertEquals(Utils::GetPhpUnixTimestamp($contest->getStartTime()), $response["start_time"]);
		$this->assertEquals(Utils::GetPhpUnixTimestamp($contest->getFinishTime()), $response["finish_time"]);
		$this->assertEquals($contest->getWindowLength(), $response["window_length"]);
		$this->assertEquals($contest->getAlias(), $response["alias"]);
		$this->assertEquals($contest->getPointsDecayFactor(), $response["points_decay_factor"]);
		$this->assertEquals($contest->getPartialScore(), $response["partial_score"]);
		$this->assertEquals($contest->getSubmissionsGap(), $response["submissions_gap"]);
		$this->assertEquals($contest->getFeedback(), $response["feedback"]);
		$this->assertEquals($contest->getPenalty(), $response["penalty"]);
		$this->assertEquals($contest->getScoreboard(), $response["scoreboard"]);
		$this->assertEquals($contest->getPenaltyTimeStart(), $response["penalty_time_start"]);
		$this->assertEquals($contest->getPenaltyCalcPolicy(), $response["penalty_calc_policy"]);

		// Assert we have our problems
		$numOfProblems = count($problems);
		$this->assertEquals($numOfProblems, count($response["problems"]));

		// Assert problem data
		$i = 0;
		foreach ($response["problems"] as $problem_array) {
			// Get problem from DB            
			$problem = ProblemsDAO::getByAlias($problems[$i]["request"]["alias"]);

			// Assert data in DB
			$this->assertEquals($problem->getTitle(), $problem_array["title"]);
			$this->assertEquals($problem->getAlias(), $problem_array["alias"]);
			$this->assertEquals($problem->getValidator(), $problem_array["validator"]);
			$this->assertEquals($problem->getTimeLimit(), $problem_array["time_limit"]);
			$this->assertEquals($problem->getMemoryLimit(), $problem_array["memory_limit"]);
			$this->assertEquals($problem->getVisits(), $problem_array["visits"]);
			$this->assertEquals($problem->getSubmissions(), $problem_array["submissions"]);
			$this->assertEquals($problem->getAccepted(), $problem_array["accepted"]);
			$this->assertEquals($problem->getOrder(), $problem_array["order"]);

			// Get points of problem from Contest-Problem relationship
			$problemInContest = ContestProblemsDAO::getByPK($contest->getContestId(), $problem->getProblemId());
			$this->assertEquals($problemInContest->getPoints(), $problem_array["points"]);

			$i++;
		}
	}

	/**
	 * Get contest details for a public contest
	 */
	public function testGetContestDetailsValid() {

		// Get a contest 
		$contestData = ContestsFactory::createContest();

		// Get some problems into the contest
		$numOfProblems = 3;
		$problems = $this->insertProblemsInContest($contestData, $numOfProblems);

		// Get a user for our scenario
		$contestant = UserFactory::createUser();

		// Prepare our request
		$r = new Request();
		$r["contest_alias"] = $contestData["request"]["alias"];

		// Log in the user
		$r["auth_token"] = $this->login($contestant);

		// Call api
		$response = ContestController::apiDetails($r);

		$this->assertContestDetails($contestData, $problems, $response);
	}

	/**
	 * Check that user in private list can view private contest
	 */
	public function testShowValidPrivateContest() {

		// Get a contest 
		$contestData = ContestsFactory::createContest(null, 0 /* private */);

		// Get some problems into the contest
		$numOfProblems = 3;
		$problems = $this->insertProblemsInContest($contestData, $numOfProblems);

		// Get a user for our scenario
		$contestant = UserFactory::createUser();

		// Add user to our private contest
		ContestsFactory::addUser($contestData, $contestant);

		// Prepare our request
		$r = new Request();
		$r["contest_alias"] = $contestData["request"]["alias"];

		// Log in the user
		$r["auth_token"] = $this->login($contestant);

		// Call api
		$response = ContestController::apiDetails($r);

		$this->assertContestDetails($contestData, $problems, $response);
	}

	/**
	 * Dont show private contests for users that are not in the private list
	 * 
	 * @expectedException ForbiddenAccessException
	 */
	public function testDontShowPrivateContestForAnyUser() {

		// Get a contest 
		$contestData = ContestsFactory::createContest(null, 0 /* private */);

		// Get some problems into the contest
		$numOfProblems = 3;
		$problems = $this->insertProblemsInContest($contestData, $numOfProblems);

		// Get a user for our scenario
		$contestant = UserFactory::createUser();

		// Prepare our request
		$r = new Request();
		$r["contest_alias"] = $contestData["request"]["alias"];

		// Log in the user
		$r["auth_token"] = $this->login($contestant);

		// Call api
		$response = ContestController::apiDetails($r);
	}

	/**
	 * First access time should not change for Window Length contests
	 */
	public function testAccessTimeIsAlwaysFirstAccessForWindowLength() {

		// This API requires DAO cache be turned off 
		ContestsDAO::$useDAOCache = false;

		// Get a contest 
		$contestData = ContestsFactory::createContest();

		// Convert contest into WindowLength one
		ContestsFactory::makeContestWindowLength($contestData, 20);

		// Get a user for our scenario
		$contestant = UserFactory::createUser();

		// Prepare our request
		$r = new Request();
		$r["contest_alias"] = $contestData["request"]["alias"];

		// Log in the user
		$r["auth_token"] = $this->login($contestant);

		// Call api
		$response = ContestController::apiDetails($r);

		// We need to grab the access time from the ContestUsers table
		$contest = ContestsDAO::getByAlias($contestData["request"]["alias"]);
		$contest_user = ContestsUsersDAO::getByPK($contestant->getUserId(), $contest->getContestId());
		$firstAccessTime = $contest_user->getAccessTime();

		// Call API again after 1 second, access time should not change		
		sleep(1);
		$response = ContestController::apiDetails($r);

		$contest_user = ContestsUsersDAO::getByPK($contestant->getUserId(), $contest->getContestId());
		$this->assertEquals($firstAccessTime, $contest_user->getAccessTime());
	}

	/**
	 * First access time should not change
	 */
	public function testAccessTimeIsAlwaysFirstAccess() {

		// This API requires DAO cache be turned off 
		ContestsDAO::$useDAOCache = false;

		// Get a contest 
		$contestData = ContestsFactory::createContest();

		// Get a user for our scenario
		$contestant = UserFactory::createUser();

		// Prepare our request
		$r = new Request();
		$r["contest_alias"] = $contestData["request"]["alias"];

		// Log in the user
		$r["auth_token"] = $this->login($contestant);

		// Call api
		$response = ContestController::apiDetails($r);

		// We need to grab the access time from the ContestUsers table
		$contest = ContestsDAO::getByAlias($contestData["request"]["alias"]);
		$contest_user = ContestsUsersDAO::getByPK($contestant->getUserId(), $contest->getContestId());
		$firstAccessTime = $contest_user->getAccessTime();

		// Call API again after 1 second, access time should not change		
		sleep(1);
		$response = ContestController::apiDetails($r);

		$contest_user = ContestsUsersDAO::getByPK($contestant->getUserId(), $contest->getContestId());
		$this->assertEquals($firstAccessTime, $contest_user->getAccessTime());
	}

	/**
	 * First access time should not change
	 */
	public function testAccessTimeIsAlwaysFirstAccessForPrivate() {

		// This API requires DAO cache be turned off 
		ContestsDAO::$useDAOCache = false;

		// Get a contest 
		$contestData = ContestsFactory::createContest(null, 0 /* private */);

		// Get a user for our scenario
		$contestant = UserFactory::createUser();

		// Add user to our private contest
		ContestsFactory::addUser($contestData, $contestant);

		// Prepare our request
		$r = new Request();
		$r["contest_alias"] = $contestData["request"]["alias"];

		// Log in the user
		$r["auth_token"] = $this->login($contestant);

		// Call api
		$response = ContestController::apiDetails($r);

		// We need to grab the access time from the ContestUsers table
		$contest = ContestsDAO::getByAlias($contestData["request"]["alias"]);
		$contest_user = ContestsUsersDAO::getByPK($contestant->getUserId(), $contest->getContestId());
		$firstAccessTime = $contest_user->getAccessTime();

		// Call API again after 1 second, access time should not change		
		sleep(1);
		$response = ContestController::apiDetails($r);

		$contest_user = ContestsUsersDAO::getByPK($contestant->getUserId(), $contest->getContestId());
		$this->assertEquals($firstAccessTime, $contest_user->getAccessTime());
	}

	/**
	 * Try to view a contest before it has started
	 * 
	 * @expectedException PreconditionFailedException
	 */
	public function testContestNotStartedYet() {
		
		// This API requires DAO cache be turned off 
		ContestsDAO::$useDAOCache = false;

		// Get a contest 
		$contestData = ContestsFactory::createContest();

		// Get a user for our scenario
		$contestant = UserFactory::createUser();

		// Set contest to not started yet
		$contest = ContestsDAO::getByAlias($contestData["request"]["alias"]);
		$contest->setStartTime(Utils::GetTimeFromUnixTimestam(Utils::GetPhpUnixTimestamp() + 30));		
		ContestsDAO::save($contest);

		// Prepare our request
		$r = new Request();
		$r["contest_alias"] = $contestData["request"]["alias"];

		// Log in the user
		$r["auth_token"] = $this->login($contestant);

		// Call api
		$response = ContestController::apiDetails($r);
	}

}

