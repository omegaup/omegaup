<?php

/**
 * CreateContestTest
 *
 * @author joemmanuel
 */
require_once 'ContestsFactory.php';

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

		// Validate that data was written to DB by iterating through all contests
		$contest = new Contests();
		$contest->setTitle($r["title"]);
		$contests = ContestsDAO::search($contest);
		$contest = $contests[0];

		// Assert that we found our contest       
		$this->assertNotNull($contest);
		$this->assertNotNull($contest->getContestId());

		// Assert data was correctly saved
		$this->assertEquals($r["description"], $contest->getDescription());
		$this->assertEquals($r["start_time"], Utils::GetPhpUnixTimestamp($contest->getStartTime()));
		$this->assertEquals($r["finish_time"], Utils::GetPhpUnixTimestamp($contest->getFinishTime()));
		$this->assertEquals($r["window_length"], $contest->getWindowLength());
		$this->assertEquals($r["public"], $contest->getPublic());
		$this->assertEquals($r["alias"], $contest->getAlias());
		$this->assertEquals($r["points_decay_factor"], $contest->getPointsDecayFactor());
		$this->assertEquals($r["partial_score"], $contest->getPartialScore());
		$this->assertEquals($r["submissions_gap"], $contest->getSubmissionsGap());
		$this->assertEquals($r["feedback"], $contest->getFeedback());
		$this->assertEquals($r["penalty"], $contest->getPenalty());
		$this->assertEquals($r["scoreboard"], $contest->getScoreboard());
		$this->assertEquals($r["penalty_time_start"], $contest->getPenaltyTimeStart());
		$this->assertEquals($r["penalty_calc_policy"], $contest->getPenaltyCalcPolicy());
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
			"partial_score",
			"submissions_gap",
			"feedback",
			"scoreboard",
			"penalty_time_start",
			"penalty_calc_policy"
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

