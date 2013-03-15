<?php

/**
 * Description of LoginUITest
 *
 * @author joemmanuel
 */

class LoginUITest extends OmegaupUITestCase {

	public function testLogin() {

		// Create a user
		$contestant = UserFactory::createUser();

		// Open index
		$this->open('/');

		// Click in Iniciar Sesion
		$this->clickAndWait('link=Inicia sesion');

		// Type login data
		$this->type('user', $contestant->getUsername());
		$this->type('pass', $contestant->getPassword());

		// Click inicia sesion		
		$this->clickAndWait("//input[@value='Inicia sesion']");

		// Sanity check that we are logged in
		$this->waitForElementPresent('//*[@id="wrapper"]/div[1]/a');
		$this->assertElementContainsText('//*[@id="wrapper"]/div[1]/a', $contestant->getUsername());
	}

}

