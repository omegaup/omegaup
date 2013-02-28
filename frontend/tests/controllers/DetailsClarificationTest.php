<?php

/**
 * Description of DetailsClarificationTest
 *
 * @author joemmanuel
 */
require_once 'ProblemsFactory.php';
require_once 'ContestsFactory.php';
require_once 'ClarificationsFactory.php';

class DetailsClarificationTest extends OmegaupTestCase {

	/**
	 * Validates a clarification given the clarification ID
	 * 
	 * @param int $clarification_id
	 * @param array $response
	 */
	private function assertClarification($clarification_id, $response) {

		// Get the actual clarification from DB to compare it with what we got
		$clarification = ClarificationsDAO::getByPK($clarification_id);

		// Assert status of clarification
		$this->assertEquals($clarification->getMessage(), $response["message"]);
		$this->assertEquals($clarification->getAnswer(), $response["answer"]);
		$this->assertEquals($clarification->getTime(), $response["time"]);
		$this->assertEquals($clarification->getProblemId(), $response["problem_id"]);
		$this->assertEquals($clarification->getContestId(), $response["contest_id"]);
	}

	/**
	 * Basic test that contest director can view private
	 * clarifications
	 */
	public function testShowClarificationAsContestDirector() {

		// Get a problem
		$problemData = ProblemsFactory::createProblem();

		// Get a contest 
		$contestData = ContestsFactory::createContest();

		// Add the problem to the contest
		ContestsFactory::addProblemToContest($problemData, $contestData);

		// Create our contestant who will submit the clarification
		$contestant = UserFactory::createUser();

		// Create the clarification, note that contestant will create it
		$clarificationData = ClarificationsFactory::createClarification($problemData, $contestData, $contestant);

		// Prepare the request object
		$r = new Request();
		$r["clarification_id"] = $clarificationData["response"]["clarification_id"];

		// Log in with the contest director
		$r["auth_token"] = $this->login($contestData["director"]);

		// Call API
		$response = ClarificationController::apiDetails($r);

		// Check the data we got
		$this->assertClarification($r["clarification_id"], $response);
	}

	/**
	 * Checks that the original creator of the clarification can actually
	 * see it, even though it is private by default for everybody else
	 */
	public function testShowClarificationAsOriginalContestant() {

		// Get a problem
		$problemData = ProblemsFactory::createProblem();

		// Get a contest 
		$contestData = ContestsFactory::createContest();

		// Add the problem to the contest
		ContestsFactory::addProblemToContest($problemData, $contestData);

		// Create our contestant who will submit the clarification
		$contestant = UserFactory::createUser();

		// Create the clarification, note that contestant will create it
		$clarificationData = ClarificationsFactory::createClarification($problemData, $contestData, $contestant);

		// Prepare the request object
		$r = new Request();
		$r["clarification_id"] = $clarificationData["response"]["clarification_id"];

		// Log in with the author of the clarification
		$r["auth_token"] = $this->login($contestant);

		// Call API
		$response = ClarificationController::apiDetails($r);

		// Check the data we got
		$this->assertClarification($r["clarification_id"], $response);
	}

	/**
	 * Checks that private clarifications cant be viewed by someone else
	 * 
	 * @expectedException ForbiddenAccessException
	 */
	public function testClarificationsCreatedPrivateAsDefault() {

		// Get a problem
		$problemData = ProblemsFactory::createProblem();

		// Get a contest 
		$contestData = ContestsFactory::createContest();

		// Add the problem to the contest
		ContestsFactory::addProblemToContest($problemData, $contestData);

		// Create our contestant who will submit the clarification
		$contestant = UserFactory::createUser();

		// Create our contestant who will try to view the clarification
		$contestant2 = UserFactory::createUser();

		// Create the clarification, note that contestant will create it
		$clarificationData = ClarificationsFactory::createClarification($problemData, $contestData, $contestant);

		// Prepare the request object
		$r = new Request();
		$r["clarification_id"] = $clarificationData["response"]["clarification_id"];

		// Log in with the author of the clarification
		$r["auth_token"] = $this->login($contestant2);

		// Call API, will fail
		ClarificationController::apiDetails($r);
	}

	public function testPublicClarificationsCanBeViewed() {

		// Get a problem
		$problemData = ProblemsFactory::createProblem();

		// Get a contest 
		$contestData = ContestsFactory::createContest();

		// Add the problem to the contest
		ContestsFactory::addProblemToContest($problemData, $contestData);

		// Create our contestant who will submit the clarification
		$contestant = UserFactory::createUser();

		// Create our contestant who will try to view the clarification
		$contestant2 = UserFactory::createUser();

		// Create the clarification, note that contestant will create it
		$clarificationData = ClarificationsFactory::createClarification($problemData, $contestData, $contestant);

		// Manually set the just created clarification to PUBLIC
		$clarification = ClarificationsDAO::getByPK($clarificationData["response"]["clarification_id"]);
		$clarification->setPublic('1');
		ClarificationsDAO::save($clarification);

		// Prepare the request object
		$r = new Request();
		$r["clarification_id"] = $clarificationData["response"]["clarification_id"];

		// Log in with the author of the clarification
		$r["auth_token"] = $this->login($contestant2);

		// Call API
		$response = ClarificationController::apiDetails($r);

		// Check the data we got
		$this->assertClarification($r["clarification_id"], $response);
	}

}

