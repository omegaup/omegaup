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
	public function testCreateContestPositive () {
		
		// Create a contest director
		$contestDirector = UserFactory::createUser();					
		
		// Create a valid contest Request object
		$r = ContestsFactory::getContestContext();
		
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
	
}

