<?php

class UserRankTest extends OmegaupTestCase {
	
	/**
	 * Tests apiRankByProblemsSolved
	 */
	public function testFullRankByProblemSolved() {
		
		// Create a user and sumbit a run with him
		$contestant = UserFactory::createUser();
		$problemData = ProblemsFactory::createProblem();		
		$runData = RunsFactory::createRunToProblem($problemData, $contestant);
		RunsFactory::gradeRun($runData);
		
		// Call API
		$response = UserController::apiRankByProblemsSolved(new Request());
		
		$found = false;
		foreach($response["rank"] as $entry) {
			if ($entry["username"] == $contestant->getUsername()) {
				$found = true;
				$this->assertEquals($entry["name"], $contestant->getName());
				$this->assertEquals($entry["problems_solved"], 1);
			}
		}
		$this->assertTrue($found);		
	}
	
	/**
	 * Tests apiRankByProblemsSolved for a specific user
	 */
	public function testUserRankByProblemsSolved() {
		
		// Create a user and sumbit a run with him
		$contestant = UserFactory::createUser();
		$problemData = ProblemsFactory::createProblem();		
		$runData = RunsFactory::createRunToProblem($problemData, $contestant);
		RunsFactory::gradeRun($runData);
		
		// Call API
		$response = UserController::apiRankByProblemsSolved(new Request(array(
			"username" => $contestant->getUsername()
		)));
		
		$this->assertEquals($response["name"], $contestant->getName());
		$this->assertEquals($response["problems_solved"], 1);		
	}
	
	/**
	 * Tests apiRankByProblemsSolved for a specific user with no runs
	 */
	public function testUserRankByProblemsSolvedWith0Runs() {
		
		// Create a user and sumbit a run with him
		$contestant = UserFactory::createUser();		
		
		// Call API
		$response = UserController::apiRankByProblemsSolved(new Request(array(
			"username" => $contestant->getUsername()
		)));
		
		$this->assertEquals($response["name"], $contestant->getName());
		$this->assertEquals($response["problems_solved"], 0);		
		$this->assertEquals($response["rank"], 0);		
	}
}
