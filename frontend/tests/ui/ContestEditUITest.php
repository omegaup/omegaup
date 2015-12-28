<?php

/**
 * Description of ContestEditUITest
 *
 * @author joemmanuel
 */
class ContestEditUITest extends OmegaupUITestCase {
	public function testEditContest() {
		// Login
		$author = $this->createUserAndLogin();

		// Create a problem
		$contestData = ContestsFactory::createContest(null, 1, $author);

		// Open problem create
		$this->open('/contestedit.php');

		sleep(1);

		$this->type('name=contests', $contestData["request"]["alias"]);

		$this->waitForValue('name=title', $contestData["request"]["title"]);

		$contestData["request"]["title"] = "new title";
		$contestData["request"]["description"] = "new description";
		$contestData["request"]["public"] = 0;

		$this->type('name=title', $contestData["request"]["title"]);
		$this->type('name=description', $contestData["request"]["description"]);
		$this->type('name=public', $contestData["request"]["public"]);

		// Click inicia sesion
		$this->click("//input[@value='Actualizar concurso']");

		$this->waitForElementPresent('id=status');
		sleep(1);
		$this->assertElementContainsText('//*[@id="content"]/div[2]/div', "Tu concurso ha sido editado!");

		// Check DB values
		$v = new OmegaupTestCase();
		$v->assertContest($contestData["request"]);
	}
}

