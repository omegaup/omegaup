<?php

/**
 * Description of ContestScoreboardTest
 *
 * @author joemmanuel
 */


class ContestScoreboardTest extends OmegaupTestCase {
	
	/**
	 * Basic test of scoreboard, shows at least the run 
	 * just submitted
	 */
	public function testBasicScoreboard() {
		
		// Get a problem
		$problemData = ProblemsFactory::createProblem();

		// Get a contest 
		$contestData = ContestsFactory::createContest();

		// Add the problem to the contest
		ContestsFactory::addProblemToContest($problemData, $contestData);

		// Create our contestant
		$contestant = UserFactory::createUser();
		
		// Create a run
		$runData = RunsFactory::createRun($problemData, $contestData, $contestant);
		
		// Grade the run
		RunsFactory::gradeRun($runData);
		
		// Create request
		$r = new Request();
		$r["contest_alias"] = $contestData["request"]["alias"];
		$r["auth_token"] = $this->login($contestant);
		
		// Create API
		$response = ContestController::apiScoreboard($r);								
		
		// Validate that we have ranking
		$this->assertEquals(1, count($response["ranking"]));
		
		$this->assertEquals($contestant->getUsername(), $response["ranking"][0]["username"]);
		
		//Check totals
		$this->assertEquals(100, $response["ranking"][0]["total"]["points"]);
		$this->assertEquals(60, $response["ranking"][0]["total"]["penalty"]); /* 60 because contest started 60 mins ago in the default factory */
		
		// Check data per problem
		$this->assertEquals(100, $response["ranking"][0]["problems"][$problemData["request"]["alias"]]["points"]);
		$this->assertEquals(60, $response["ranking"][0]["problems"][$problemData["request"]["alias"]]["penalty"]);
		$this->assertEquals(0, $response["ranking"][0]["problems"][$problemData["request"]["alias"]]["wrong_runs_count"]);
	}
	
	/**
	 * Set 0% of scoreboard for contestants, should show all 0s
	 */
	public function testScoreboardPercentajeForContestant() {
		
		DAO::$useDAOCache = false;
		// Get a problem
		$problemData = ProblemsFactory::createProblem();

		// Get a contest 
		$contestData = ContestsFactory::createContest();
		
		// Set 0% of scoreboard show
		$contest = ContestsDAO::getByAlias($contestData["request"]["alias"]);
		$contest->setScoreboard(0);
		ContestsDAO::save($contest);		

		// Add the problem to the contest
		ContestsFactory::addProblemToContest($problemData, $contestData);

		// Create our contestant
		$contestant = UserFactory::createUser();
		
		// Create a run
		$runData = RunsFactory::createRun($problemData, $contestData, $contestant);
		
		// Grade the run
		RunsFactory::gradeRun($runData);
		
		// Create request
		$r = new Request();
		$r["contest_alias"] = $contestData["request"]["alias"];
		$r["auth_token"] = $this->login($contestant);
		
		// Create API
		$response = ContestController::apiScoreboard($r);								
		
		// Validate that we have ranking
		$this->assertEquals(1, count($response["ranking"]));
		
		$this->assertEquals($contestant->getUsername(), $response["ranking"][0]["username"]);
		
		//Check totals
		$this->assertEquals(0, $response["ranking"][0]["total"]["points"]);
		$this->assertEquals(0, $response["ranking"][0]["total"]["penalty"]); /* 60 because contest started 60 mins ago in the default factory */
		
		// Check data per problem
		$this->assertEquals(0, $response["ranking"][0]["problems"][$problemData["request"]["alias"]]["points"]);
		$this->assertEquals(0, $response["ranking"][0]["problems"][$problemData["request"]["alias"]]["penalty"]);
		$this->assertEquals(0, $response["ranking"][0]["problems"][$problemData["request"]["alias"]]["wrong_runs_count"]);
	}
	
	/**
	 * Set 0% of scoreboard for admins
	 */
	public function testScoreboardPercentajeForContestAdmin() {
		
		DAO::$useDAOCache = false;
		// Get a problem
		$problemData = ProblemsFactory::createProblem();

		// Get a contest 
		$contestData = ContestsFactory::createContest();
		
		// Set 0% of scoreboard show
		$contest = ContestsDAO::getByAlias($contestData["request"]["alias"]);
		$contest->setScoreboard(0);
		ContestsDAO::save($contest);		

		// Add the problem to the contest
		ContestsFactory::addProblemToContest($problemData, $contestData);

		// Create our contestant
		$contestant = UserFactory::createUser();
		
		// Create a run
		$runData = RunsFactory::createRun($problemData, $contestData, $contestant);
		
		// Grade the run
		RunsFactory::gradeRun($runData);
		
		// Create request
		$r = new Request();
		$r["contest_alias"] = $contestData["request"]["alias"];
		$r["auth_token"] = $this->login($contestData["director"]);
		
		// Create API
		$response = ContestController::apiScoreboard($r);								
		
		// Validate that we have ranking
		$this->assertEquals(1, count($response["ranking"]));
		
		$this->assertEquals($contestant->getUsername(), $response["ranking"][0]["username"]);
		
		//Check totals
		$this->assertEquals(100, $response["ranking"][0]["total"]["points"]);
		$this->assertEquals(60, $response["ranking"][0]["total"]["penalty"]); /* 60 because contest started 60 mins ago in the default factory */
		
		// Check data per problem
		$this->assertEquals(100, $response["ranking"][0]["problems"][$problemData["request"]["alias"]]["points"]);
		$this->assertEquals(60, $response["ranking"][0]["problems"][$problemData["request"]["alias"]]["penalty"]);
		$this->assertEquals(0, $response["ranking"][0]["problems"][$problemData["request"]["alias"]]["wrong_runs_count"]);
	}		
}

