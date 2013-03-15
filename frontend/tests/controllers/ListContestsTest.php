<?php

/**
 * Description of ListContests
 *
 * @author joemmanuel
 */

class ListContests extends OmegaupTestCase {
	
	/** 
	 * Basic test. Check that most recent contest is at the top of the list
	 */
	public function testLatestPublicContest() {
		
		$r = new Request();
		
		// Create new PUBLIC contest
		$contestData = ContestsFactory::createContest();
		
		// Log as a random contestant
		$contestant = UserFactory::createUser();
		$r["auth_token"] = $this->login($contestant);
		
		$response = ContestController::apiList($r);
		
		// Assert our contest is there
        $this->assertArrayHasKey("0", $response['results']);        
        $this->assertEquals($contestData["request"]["title"], $response['results'][0]["title"]);
	}		
	
	
	/** 
	 * Basic test. Check that most recent contest is at the top of the list
	 */
	public function testLatestPublicContestNotLoggedIn() {
		
		$r = new Request();
		
		// Create new PUBLIC contest
		$contestData = ContestsFactory::createContest();						
		
		$response = ContestController::apiList($r);
		
		// Assert our contest is there
        $this->assertArrayHasKey("0", $response['results']);        
        $this->assertEquals($contestData["request"]["title"], $response['results'][0]["title"]);
	}	
}

