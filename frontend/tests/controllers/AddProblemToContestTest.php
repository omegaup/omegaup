<?php

/**
 * Description of AddProblemToContestTest
 *
 * @author joemmanuel
 */
require_once 'ProblemsFactory.php';
require_once 'ContestsFactory.php';

class AddProblemToContestTest extends OmegaupTestCase {
	
	/**
	 * Add a problem to contest with valid params
	 */
	public function testAddProblemToContestPositive() {				
		
		// Get a problem
		$problemData = ProblemsFactory::createProblem();
		
		// Get a contest 
		$contestData = ContestsFactory::createContest();
		
		// Create an empty request
		$r = new Request();
		
		// Log in as contest director
		$r["auth_token"] = $this->login($contestData["director"]);
		
		// Build request
		$r["contest_alias"] = $contestData["request"]["alias"];
		$r["problem_alias"] = $problemData["request"]["alias"];
		$r["points"] = 100;
		$r["order_in_contest"] = 1;				
		
		// Call API
		$response = ContestController::apiAddProblem($r);
		
		// Validate
		$this->assertEquals("ok", $response["status"]);
		
		// Get problem and contest from DB
		$problem = ProblemsDAO::getByAlias($problemData["request"]["alias"]);
		$contest = ContestsDAO::getByAlias($contestData["request"]["alias"]);
		
		// Get problem-contest and verify it
        $contest_problems = ContestProblemsDAO::getByPK($contest->getContestId(), $problem->getProblemId());
        $this->assertNotNull($contest_problems);        
        $this->assertEquals($r["points"], $contest_problems->getPoints());    		
		$this->assertEquals($r["order_in_contest"], $contest_problems->getOrder());
		
	}
	
	/**
	 * Add a problem to contest with invalid params
	 * 
	 * @expectedException InvalidParameterException
	 */
	public function testAddProblemToContestInvalidProblem() {				
		
		// Get a problem
		$problemData = ProblemsFactory::createProblem();
		
		// Get a contest 
		$contestData = ContestsFactory::createContest();
		
		// Create an empty request
		$r = new Request();
		
		// Log in as contest director
		$r["auth_token"] = $this->login($contestData["director"]);
		
		// Build request
		$r["contest_alias"] = $contestData["request"]["alias"];
		$r["problem_alias"] = "this problem doesnt exists";
		$r["points"] = 100;
		$r["order_in_contest"] = 1;				
		
		// Call API
		$response = ContestController::apiAddProblem($r);
	}	
	
	
	/**
	 * Add a problem to contest with invalid params
	 * 
	 * @expectedException InvalidParameterException
	 */
	public function testAddProblemToContestInvalidContest() {				
		
		// Get a problem
		$problemData = ProblemsFactory::createProblem();
		
		// Get a contest 
		$contestData = ContestsFactory::createContest();
		
		// Create an empty request
		$r = new Request();
		
		// Log in as contest director
		$r["auth_token"] = $this->login($contestData["director"]);
		
		// Build request
		$r["contest_alias"] = "invalid problem";
		$r["problem_alias"] = $problemData["request"]["alias"];
		$r["points"] = 100;
		$r["order_in_contest"] = 1;				
		
		// Call API
		$response = ContestController::apiAddProblem($r);
	}
	
	/**
	 * Add a problem to contest with unauthorized user
	 * 
	 * @expectedException ForbiddenAccessException
	 */
	public function testAddProblemToContestWithUnauthorizedUser() {				
		
		// Get a problem
		$problemData = ProblemsFactory::createProblem();
		
		// Get a contest 
		$contestData = ContestsFactory::createContest();
		
		// Create an empty request
		$r = new Request();
		
		// Log in as another random user
		$user = UserFactory::createUser();
		$r["auth_token"] = $this->login($user);
		
		// Build request
		$r["contest_alias"] = $contestData["request"]["alias"];
		$r["problem_alias"] = $problemData["request"]["alias"];
		$r["points"] = 100;
		$r["order_in_contest"] = 1;				
		
		// Call API
		$response = ContestController::apiAddProblem($r);
	}
}

