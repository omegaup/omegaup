<?php

/**
 * @author joemmanuel
 */

class ProblemBestScoreTest extends OmegaupTestCase {

	/**
	 * Test apiBestScore for submits in a problem
	 */
	public function testBestScoreInProblem() {
		
		// Create problem
		$problemData = ProblemsFactory::createProblem();
		
		// Create contestant
		$contestant = UserFactory::createUser();
		
		// Create 2 runs, 100 and 50.
		RunController::$defaultSubmissionGap = 0;
		$runData = RunsFactory::createRunToProblem($problemData, $contestant);
		$runDataPA = RunsFactory::createRunToProblem($problemData, $contestant);
		RunsFactory::gradeRun($runData);
		RunsFactory::gradeRun($runDataPA, 0.5, "PA");
		RunController::$defaultSubmissionGap = 60;
		
		// Call API
		$response = ProblemController::apiBestScore(new Request(array(
			"auth_token" => $this->login($contestant),
			"problem_alias" => $problemData["request"]["alias"]
		)));
		
		$this->assertEquals(100.00, $response["score"]);
	}
	
	/**
	 * Test apiBestScore for submits inside a contest
	 */
	public function testBestScoreInsideContest() {
		
		// Create problem and contest
		$problemData = ProblemsFactory::createProblem();
		$contestData = ContestsFactory::createContest();
		ContestsFactory::addProblemToContest($problemData, $contestData);
		
		// Create contestant
		$contestant = UserFactory::createUser();
		
		// Create 2 runs, 100 and 50.
		RunController::$defaultSubmissionGap = 0;
		$runDataOutsideContest = RunsFactory::createRunToProblem($problemData, $contestant);
		$runDataInsideContest = RunsFactory::createRun($problemData, $contestData, $contestant);
		RunsFactory::gradeRun($runDataOutsideContest);
		RunsFactory::gradeRun($runDataInsideContest, 0.5, "PA");
		RunController::$defaultSubmissionGap = 60;
		
		// Call API
		$response = ProblemController::apiBestScore(new Request(array(
			"auth_token" => $this->login($contestant),
			"problem_alias" => $problemData["request"]["alias"],
			"contest_alias" => $contestData["request"]["alias"]
		))); 
		
		$this->assertEquals(50.00, $response["score"]);
	}
}
	