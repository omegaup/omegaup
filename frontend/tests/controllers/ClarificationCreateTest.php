<?php

/**
 * Description of CreateClarificationTest
 *
 * @author joemmanuel
 */

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

		// Call the API
		$this->detourBroadcasterCalls();
		$clarificationData = ClarificationsFactory::createClarification(
			$problemData, $contestData, $contestant);

		// Assert status of new contest
		$this->assertArrayHasKey("clarification_id", $clarificationData['response']);

		// Verify that clarification was inserted in the database
		$clarification =
			ClarificationsDAO::getByPK($clarificationData['response']['clarification_id']);

		// Verify our retreived clarificatoin
		$this->assertNotNull($clarification);
		$this->assertEquals($clarificationData['request']['message'],
			$clarification->getMessage());

		// We need to verify that the contest and problem IDs where properly saved
		// Extractiing the contest and problem from DB to check IDs
		$problem = ProblemsDAO::getByAlias($problemData["request"]["alias"]);
		$contest = ContestsDAO::getByAlias($contestData["request"]["alias"]);

		$this->assertEquals($contest->getContestId(), $clarification->getContestId());
		$this->assertEquals($problem->getProblemId(), $clarification->getProblemId());
	}
}
