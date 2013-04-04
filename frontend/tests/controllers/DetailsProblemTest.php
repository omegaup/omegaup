<?php

/**
 * Description of DetailsProblem
 *
 * @author joemmanuel
 */

class DetailsProblem extends OmegaupTestCase {
	
	/**
	 * 
	 */	
	public function testViewProblemInAContestDetailsValid() {
		
		// Get a contest 
		$contestData = ContestsFactory::createContest();
		
		// Get a problem
		$problemData = ProblemsFactory::createProblem();
		
		// Add the problem to the contest
		ContestsFactory::addProblemToContest($problemData, $contestData);
		
		// Get a user for our scenario
		$contestant = UserFactory::createUser();
		
		// Prepare our request
		$r = new Request();
		$r["contest_alias"] = $contestData["request"]["alias"];
		$r["problem_alias"] = $problemData["request"]["alias"];

		// Log in the user
		$r["auth_token"] = $this->login($contestant);
		
		// Call api
		$response = ProblemController::apiDetails($r);
		
		// Get problem and contest from DB to check it
        $problemDAO = ProblemsDAO::getByAlias($problemData["request"]["alias"]);               
		$contestDAO = ContestsDAO::getByAlias($contestData["request"]["alias"]);
		$contestantsDAO = UsersDAO::search(new Users(array("username" => $contestant->getUsername())));
		$contestantDAO = $contestantsDAO[0];						
        
        // Assert data
        $this->assertEquals($response["title"], $problemDAO->getTitle());
        $this->assertEquals($response["alias"], $problemDAO->getAlias());
        $this->assertEquals($response["validator"], $problemDAO->getValidator());
        $this->assertEquals($response["time_limit"], $problemDAO->getTimeLimit());
        $this->assertEquals($response["memory_limit"], $problemDAO->getMemoryLimit());                      
        $this->assertEquals($response["author_id"], $problemDAO->getAuthorId()); 
        $this->assertEquals($response["source"], $problemDAO->getSource()); 
        $this->assertContains("<h1>Entrada</h1>", $response["problem_statement"]);        
        $this->assertEquals($response["order"], $problemDAO->getOrder());
        
        // Default data
        $this->assertEquals(0, $problemDAO->getVisits());
        $this->assertEquals(0, $problemDAO->getSubmissions());
        $this->assertEquals(0, $problemDAO->getAccepted());
        $this->assertEquals(0, $problemDAO->getDifficulty());
        
        // Verify that we have an empty array of runs
        $this->assertEquals(0, count($response["runs"]));
        
        // Verify that problem was marked as Opened
        $problem_opened = ContestProblemOpenedDAO::getByPK($contestDAO->getContestId(), $problemDAO->getProblemId(), $contestantDAO->getUserId());
        $this->assertNotNull($problem_opened);        

        // Verify open time 
        $this->assertEquals(Utils::GetPhpUnixTimestamp(), Utils::GetPhpUnixTimestamp($problem_opened->getOpenTime()));

	}
	
}

