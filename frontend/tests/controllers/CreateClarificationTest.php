<?php

/**
 * Description of CreateClarificationTest
 *
 * @author joemmanuel
 */

require_once 'ProblemsFactory.php';
require_once 'ContestsFactory.php';

class CreateClarificationTest extends OmegaupTestCase {
	
	/**
	 * Creates a valid clarification
	 */
	public function testCreateValidClarification() {
		
		// Get a problem
		$problemData = ProblemsFactory::createProblem();

		// Get a contest 
		$contestData = ContestsFactory::createContest();

		// Add the problem to the contest
		ContestsFactory::addProblemToContest($problemData, $contestData);

		// Create our contestant who will submit the clarification
		$contestant = UserFactory::createUser();
		
		// Our contestant has to open the contest before sending a clarification
		ContestsFactory::openContest($contestData, $contestant);

		// Then we need to open the problem
		ContestsFactory::openProblemInContest($contestData, $problemData, $contestant);
		
		// Create the request for our api
		$r = new Request();
		$r["message"] = Utils::CreateRandomString();
		$r["contest_alias"] = $contestData["request"]["alias"];
		$r["problem_alias"] = $problemData["request"]["alias"];
		
		// Log in our user and set the auth_token properly
		$r["auth_token"] = $this->login($contestant);
		
		// Call the API
		$response = ClarificationController::apiCreate($r);
		
		// Assert status of new contest
        $this->assertArrayHasKey("clarification_id", $response);
        
        // Verify that clarification was inserted in the database
        $clarification = ClarificationsDAO::getByPK($response["clarification_id"]);
        
        // Verify our retreived clarificatoin
        $this->assertNotNull($clarification);
        $this->assertEquals($r["message"], $clarification->getMessage());
		
		// We need to verify that the contest and problem IDs where properly saved
		// Extractiing the contest and problem from DB to check IDs
		$problem = ProblemsDAO::getByAlias($problemData["request"]["alias"]);
		$contest = ContestsDAO::getByAlias($contestData["request"]["alias"]);
		
        $this->assertEquals($contest->getContestId(), $clarification->getContestId());
        $this->assertEquals($problem->getProblemId(), $clarification->getProblemId());                                        		
	}

}

