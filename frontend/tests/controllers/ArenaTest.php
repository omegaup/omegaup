<?php

/**
 * Description of ArenaTest
 *
 * @author joemmanuel
 */
require_once 'ContestsFactory.php';

class ArenaTest extends OmegaupUITestCase {
	
	public function testArenaRoot() {
		
		// Create a contest
		$contestData = ContestsFactory::createContest();
		
		// Open URL
		$this->open('/arena');
		
		// Sanity check: Arena at leasts says 'Arena', it is not badly broken
		$this->assertElementContainsText('//*[@id="root"]/h1', 'Arena');
		
		// Check that our latest contest is there
		$this->waitForElementPresent('//*[@id="past-contests"]/tr[1]/td[1]/a');
		$this->assertElementContainsText('//*[@id="past-contests"]/tr[1]/td[1]/a', $contestData["request"]["title"]);		
	}

}
