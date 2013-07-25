<?php

/**
 * Description of ContestCreateUITest
 *
 * @author joemmanuel
 */
class ContestCreateUITest extends OmegaupUITestCase {
	
	public function testCreateContest() {
		
		$contestData = ContestsFactory::getRequest();
		
		// Login
		$director = $this->createUserAndLogin();
		
		// Open contest create
		$this->open('/contestcreate.php');
		
		// Use preioi template
		$this->click('//*[@id="preioi"]');
		
		$this->type('name=title', $contestData["request"]["title"]);
		$this->type('name=alias', $contestData["request"]["alias"]);
		$this->type('name=description', $contestData["request"]["description"]);
				
		// Submit
		$this->click("//input[@value='Agendar concurso']");
		sleep(1);
		$this->assertElementContainsText('//*[@id="content"]/div[2]/div', "Tu concurso ha sido creado!");
		
	}	
}

