<?php

/**
 * Parent class of all Test cases for Omegaup
 * Implements common methods for setUp and asserts
 *
 * @author joemmanuel
 */
class OmegaupTestCase extends PHPUnit_Framework_TestCase {
	
	/**
	 * setUp function gets executed before each test (thanks to phpunit)
	 */
	public function setUp() {		
		
		//Clean $_REQUEST before each test
		unset($_REQUEST);				
	}
		
}

