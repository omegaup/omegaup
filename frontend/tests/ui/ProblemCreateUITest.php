<?php

/**
 * Description of ProblemCreateTest
 *
 * @author joemmanuel
 */
class ProblemCreateUITest extends OmegaupUITestCase {
	
	public function testCreateProblem() {
		
		// Create a problem
		$problemData = ProblemsFactory::getRequest();
		
		// Login
		$contestant = $this->createAdminUserAndLogin();
				
		// Open problem create
		$this->open('/problemnew.php');
		
		$this->type('name=title', $problemData["request"]["title"]);
		$this->type('source', $problemData["request"]["source"]);
		$this->type('problem_contents', $problemData["zip_path"]);
		
		// Click inicia sesion		
		$this->clickAndWait("//input[@value='Crear problema']");
		
		$this->assertElementContainsText('//*[@id="content"]/div[2]/div', "New problem created succesfully! Alias: " . $problemData["request"]["alias"]);
	}
	
	public function testCreateProblemMissingParameters() {
		
		// Create a problem
		$problemData = ProblemsFactory::getRequest();
		
		// Login
		$contestant = $this->createAdminUserAndLogin();
				
		// Open problem create
		$this->open('/problemnew.php');
		
		// Click inicia sesion		
		$this->clickAndWait("//input[@value='Crear problema']");
		
		$this->assertElementContainsText('//*[@id="content"]/div[2]/div', "title cannot be empty.");
	}
	
	public function testOpenCreatePageWithoutLogin() {
		
		// Create a problem
		$problemData = ProblemsFactory::getRequest();				
				
		// Open problem create
		$this->open('/problemnew.php');
		
		$this->waitForElementPresent('//*[@id="content"]/div[2]/div[1]/h1');
		$this->assertElementContainsText('//*[@id="content"]/div[2]/div[1]/h1', "¡Inicia sesion en Omegaup!");		
	}
}

