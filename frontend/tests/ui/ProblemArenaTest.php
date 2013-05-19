<?php

/**
 * Description of ProblemArenaTest
 *
 * @author joemmanuel
 */
class ProblemArenaTest extends OmegaupUITestCase {

	public function testProblemArenaAndSubmbit() {

		// Login
		$contestant = $this->createUserAndLogin();
		
		// Create a problem
		$problemData = ProblemsFactory::createProblem();
		
		// Click in Problems
		$this->clickAndWait('link=Problemas');
		
		// Click in Problem $problemData
		$this->waitForElementPresent('//*[@id="problems_list"]/table/tbody/tr[2]/td/a');		
		$this->clickAndWait('link='.$problemData["request"]["title"]);
		
		// Check that arena contains the title
		$this->waitForElementPresent('//*[@id="problem"]/h1');
		$this->assertElementContainsText('//*[@id="problem"]/h1', $problemData["request"]["title"]);
		
		// Click in New run
		$this->click('link=Nuevo envío');
		$this->waitForElementPresent('//*[@id="lang-select"]');
		
		// Write some code and submit
		$this->select('name=language', 'label=C++');
		$this->type('code', "Code lol");		
		$this->click('//*[@id="submit"]/input');
				
		// Get run ID
		sleep(1);
		$runs = ProblemController::apiRuns(new Request(array(
			"problem_alias" => $problemData["request"]["alias"],
			"auth_token" => OmegaupTestCase::login($contestant)
		)));	
		
		
		// Wait for submit history to show				
		$this->waitForElementPresent('//*[@id="run_'.$runs["runs"][0]["guid"].'"]/td[2]');
		
	}

}
