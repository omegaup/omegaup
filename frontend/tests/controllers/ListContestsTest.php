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
	
	
	/** 
	 * 
	 */
	public function testPrivateContestForInvitedUser() {
		
		$r = new Request();
		
		// Create new private contest
		$contestData = ContestsFactory::createContest(null, false /*private*/);
		
		// Get a user for our scenario
		$contestant = UserFactory::createUser();

		// Add user to our private contest
		ContestsFactory::addUser($contestData, $contestant);
		
		$r["auth_token"] = $this->login($contestant);
		
		$response = ContestController::apiList($r);
		
		// Assert our contest is there
        $this->assertArrayHasKey("0", $response['results']);        
        $this->assertEquals($contestData["request"]["title"], $response['results'][0]["title"]);
	}
	
	/** 
	 * 
	 */
	public function testPrivateContestForNonInvitedUser() {
		
		$r = new Request();
		
		// Create new private contest
		$contestData = ContestsFactory::createContest(null, false /*private*/);
		
		// Get a user for our scenario
		$contestant = UserFactory::createUser();

		// Add user to our private contest
		ContestsFactory::addUser($contestData, $contestant);
		
		$r["auth_token"] = $this->login(UserFactory::createUser());
		
		$response = ContestController::apiList($r);
		
		// Assert our contest is not there
        $this->assertArrayHasKey("0", $response['results']);        
        $this->assertNotEquals($contestData["request"]["title"], $response['results'][0]["title"]);
	}
	
	
	/** 
	 * 
	 */
	public function testPrivateContestForSystemAdmin() {
		
		$r = new Request();
		
		// Create new private contest
		$contestData = ContestsFactory::createContest(null, false /*private*/);
				
		$r["auth_token"] = $this->login(UserFactory::createAdminUser());
		
		$response = ContestController::apiList($r);
		
		// Assert our contest is there
        $this->assertArrayHasKey("0", $response['results']);        
        $this->assertEquals($contestData["request"]["title"], $response['results'][0]["title"]);
	}
	
	/** 
	 * 
	 */
	public function testPrivateContestForContestAdmin() {
		
		$r = new Request();
		
		// Create new private contest
		$contestData = ContestsFactory::createContest(null, false /*private*/);
		
		// Get a user for our scenario
		$contestant = UserFactory::createUser();

		
		// Add user to our private contest
		ContestsFactory::addAdminUser($contestData, $contestant);
						
		$r["auth_token"] = $this->login($contestant);
		
		$response = ContestController::apiList($r);
		
		// Assert our contest is there
        $this->assertArrayHasKey("0", $response['results']);        
        $this->assertEquals($contestData["request"]["title"], $response['results'][0]["title"]);
	}
}

