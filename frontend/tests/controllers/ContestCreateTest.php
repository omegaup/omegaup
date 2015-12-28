<?php

/**
 * CreateContestTest
 *
 * @author joemmanuel
 */

class CreateContestTest extends OmegaupTestCase {
	/**
	 * Basic Create Contest scenario
	 *
	 */
	public function testCreateContestPositive() {
		// Create a valid contest Request object
		$contestData = ContestsFactory::getRequest();
		$r = $contestData["request"];
		$contestDirector = $contestData["director"];

		// Log in the user and set the auth token in the new request
		$r["auth_token"] = $this->login($contestDirector);

		// Call the API
		$response = ContestController::apiCreate($r);

		// Assert status of new contest
		$this->assertEquals("ok", $response["status"]);

		// Assert that the contest requested exists in the DB
		$this->assertContest($r);
	}

	/**
	 * Tests that missing params throw exception
	 */
	public function testMissingParameters() {
		// Array of valid keys
		$valid_keys = array(
			"title",
			"description",
			"start_time",
			"finish_time",
			"public",
			"alias",
			"points_decay_factor",
			"submissions_gap",
			"feedback",
			"scoreboard",
			"penalty_type",
		);

		foreach ($valid_keys as $key) {
			// Create a valid contest Request object
			$contestData = ContestsFactory::getRequest();
			$r = $contestData["request"];
			$contestDirector = $contestData["director"];

			$auth_token = $this->login($contestDirector);

			// unset the current key from request
			unset($r[$key]);

			// Set the valid auth token in the new request
			$r["auth_token"] = $auth_token;

			try {
				// Call the API
				$response = ContestController::apiCreate($r);
			} catch (InvalidParameterException $e) {
				// This exception is expected
				unset($_REQUEST);
				continue;
			}

			$this->fail("Exception was expected. Parameter: " . $key);
		}
	}

	/**
	 * Tests that 2 contests with same name cannot be created
	 *
	 * @expectedException DuplicatedEntryInDatabaseException
	 */
	public function testCreate2ContestsWithSameAlias() {
		// Create a valid contest Request object
		$contestData = ContestsFactory::getRequest();
		$r = $contestData["request"];
		$contestDirector = $contestData["director"];

		// Log in the user and set the auth token in the new request
		$r["auth_token"] = $this->login($contestDirector);

		// Call the API
		$response = ContestController::apiCreate($r);
		$this->assertEquals("ok", $response["status"]);

		// Call the API for the 2nd time with same alias
		$response = ContestController::apiCreate($r);
	}
}

