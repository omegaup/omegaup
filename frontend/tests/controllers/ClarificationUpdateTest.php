<?php

/**
 * Description of UpdateClarificationTest
 *
 * @author joemmanuel
 */

class UpdateClarificationTest extends OmegaupTestCase {

	/**
	 * Basic test for answer	 
	 * 
	 */
	public function testUpdateAnswer() {

		// Get a problem
		$problemData = ProblemsFactory::createProblem();

		// Get a contest 
		$contestData = ContestsFactory::createContest();

		// Add the problem to the contest
		ContestsFactory::addProblemToContest($problemData, $contestData);

		// Create our contestant who will submit the clarification
		$contestant = UserFactory::createUser();

		// Create clarification
		$clarificationData = ClarificationsFactory::createClarification($problemData, $contestData, $contestant);
		
		// Prepare request
		$r = new Request();
		$r["clarification_id"] = $clarificationData["response"]["clarification_id"];
		
		// Log in the user
		$r["auth_token"] = $this->login($contestData["director"]);
		
		// Update answer
		$r["answer"] = "new answer";
		
		// Call api
		$response = ClarificationController::apiUpdate($r);
		
		// Get clarification from DB
		$clarification = ClarificationsDAO::getByPK($r["clarification_id"]);
		
		// Validate that clarification stays the same
		$this->assertEquals($clarificationData["request"]["message"], $clarification->getMessage());
		$this->assertEquals($clarificationData["request"]["public"], $clarification->getPublic());
				
		// Validate our update
		$this->assertEquals($r["answer"], $clarification->getAnswer());
	}

}

