<?php

/**
 * Description of ArenaTest
 *
 * @author joemmanuel
 */
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class ArenaTest extends PHPUnit_Extensions_SeleniumTestCase {

	protected function setUp() {
		$this->setBrowser('*firefox');
		$this->setBrowserUrl('http://localhost/');
	}

	public function testArenaRoot() {
		$this->open('http://localhost/arena');
		$this->assertElementContainsText('//*[@id="root"]/h1', 'Arena');
	}

}
