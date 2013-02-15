<?php

/**
 * Description of ListClarificationsContest
 *
 * @author joemmanuel
 */
require_once 'ProblemsFactory.php';
require_once 'ContestsFactory.php';
require_once 'ClarificationsFactory.php';

class ListClarificationsContest extends OmegaupTestCase {

	/**
	 * Basic test for getting the list of clarifications of a contest.
	 * Create 3 clarifications in a contest with one user, then another 3 clarifications
	 * with another user. Get the list for the first user, will see only his 3
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

		// Create 3 clarifications with this contestant
		$clarificationData1 = array();
		for ($i = 0; $i < 2; $i++) {
			$clarificationData1[$i] = ClarificationsFactory::createClarification($problemData, $contestData, $contestant1);			
			
			// We need to sleep a little bit to separate the times
			sleep(1);
		}

		// Create another contestant
		$contestant2 = UserFactory::createUser();

		// Create 3 clarifications with this contestant
		$clarificationData2 = array();
		for ($i = 0; $i < 3; $i++) {
			$clarificationData2[$i] = ClarificationsFactory::createClarification($problemData, $contestData, $contestant2);
		}

		// Prepare the request
		$r = new Request();
		$r["contest_alias"] = $contestData["request"]["alias"];

		// Log in with first user
		$r["auth_token"] = $this->login($contestant1);

		// Call API
		$response = ContestController::apiClarifications($r);

		// Check that we got our 3 clarifications
		$this->assertEquals(2, count($response["clarifications"]));
		
		// Check that the clarifications came in reverse order as we inserted. T
		$i = 0;
		foreach($response["clarifications"] as $c) {
			$this->assertEquals($clarificationData1[$i]["request"]["message"], $c["message"]);
			$i++;
		}
	}

}

