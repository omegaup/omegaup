<?php

/**
 * Description of ArenaTest
 *
 * @author joemmanuel
 */
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';
require_once 'ContestsFactory.php';

class ArenaTest extends PHPUnit_Extensions_SeleniumTestCase {

	protected function setUp() {
		$this->setBrowser('*firefox');
		$this->setBrowserUrl(OMEGAUP_BASE_URL);
	}

	public function testArenaRoot() {
		
		// Create a contest
		$contestData = ContestsFactory::createContest();
		
		// Open URL
		$this->open(OMEGAUP_BASE_URL . '/arena');
		
		// Sanity check: Arena at leasts says 'Arena', it is not badly broken
		$this->assertElementContainsText('//*[@id="root"]/h1', 'Arena');
		
		// Check that our latest contest is there
		$this->waitForElementPresent('//*[@id="past-contests"]/tr[1]/td[1]/a');
		$this->assertElementContainsText('//*[@id="past-contests"]/tr[1]/td[1]/a', $contestData["request"]["title"]);		
	}

}
