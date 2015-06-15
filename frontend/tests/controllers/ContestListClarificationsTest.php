<?php

/**
 * Description of ListClarificationsContest
 *
 * @author joemmanuel
 */

class ListClarificationsContest extends OmegaupTestCase {

	/**
	 * Basic test for getting the list of clarifications of a contest.
	 * Create 4 clarifications in a contest with one user, then another 3 clarifications
	 * with another user. 
	 * Get the list for the first user, will see only his 4
	 */
	public function testListPublicClarificationsForContestant() {

		// Get a problem
		$problemData = ProblemsFactory::createProblem();

		// Get a contest 
		$contestData = ContestsFactory::createContest();

		// Add the problem to the contest
		ContestsFactory::addProblemToContest($problemData, $contestData);

		// Create our contestant who will submit the clarification
		$contestant1 = UserFactory::createUser();

		// Create 4 clarifications with this contestant
		$clarificationData1 = array();
		$this->initMockClarificationController(9);
		for ($i = 0; $i < 4; $i++) {
			$clarificationData1[$i] = 
				ClarificationsFactory::createClarification($this, $problemData, 
				$contestData, $contestant1);
			// We need to sleep a little bit to separate the times
			sleep(1);
		}
		
		// Answer clarification 0 and 2
		ClarificationsFactory::answer($this, $clarificationData1[0], $contestData);
		sleep(1);
		ClarificationsFactory::answer($this, $clarificationData1[2], $contestData);

		// Create another contestant
		$contestant2 = UserFactory::createUser();

		// Create 3 clarifications with this contestant
		$clarificationData2 = array();
		for ($i = 0; $i < 3; $i++) {
			$clarificationData2[$i] = 
				ClarificationsFactory::createClarification($this, $problemData, 
				$contestData, $contestant2);
		}

		// Prepare the request
		$r = new Request();
		$r["contest_alias"] = $contestData["request"]["alias"];

		// Log in with first user
		$r["auth_token"] = $this->login($contestant1);

		// Call API
		$response = ContestController::apiClarifications($r);

		// Check that we got all clarifications
		$this->assertEquals(count($clarificationData1), count($response["clarifications"]));

		// Check that the clarifications came in the order we expect
		// First we expect clarifications not answered
		$this->assertEquals($clarificationData1[3]["request"]["message"], $response["clarifications"][0]["message"]);
		$this->assertEquals($clarificationData1[1]["request"]["message"], $response["clarifications"][1]["message"]);
		
		// Then clarifications answered, newer first
		$this->assertEquals($clarificationData1[2]["request"]["message"], $response["clarifications"][2]["message"]);
		$this->assertEquals($clarificationData1[0]["request"]["message"], $response["clarifications"][3]["message"]);
	}
}
