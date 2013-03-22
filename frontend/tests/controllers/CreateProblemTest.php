<?php

/**
 * Tests for apiCreate in ProblemController
 *
 * @author joemmanuel
 */

require_once 'libs/FileHandler.php';

class CreateProblemTest extends OmegaupTestCase {

	/**
	 * Basic test for creating a problem
	 */
	public function testCreateValidContest() {

		// Get the problem data
        $problemData = ProblemsFactory::getRequest();
		$r = $problemData["request"];
		$problemAuthor = $problemData["author"];

		// Login user
		$r["auth_token"] = $this->login($problemAuthor);

		// Get File Uploader Mock and tell Omegaup API to use it
		FileHandler::SetFileUploader($this->createFileUploaderMock());

		// Call the API				
		$response = ProblemController::apiCreate($r);

		// Validate
		// Verify response
		$this->assertEquals("ok", $response["status"]);
		$this->assertEquals("testplan", $response["uploaded_files"][10]);


		// Verify data in DB
		$problem_mask = new Problems();
		$problem_mask->setTitle($r["title"]);
		$problems = ProblemsDAO::search($problem_mask);

		// Check that we only retreived 1 element
		$this->assertEquals(1, count($problems));
		$problem = $problems[0];

		// Verify contest was found
		$this->assertNotNull($problem);
		$this->assertNotNull($problem->getProblemId());

		// Verify DB data
		$this->assertEquals($r["title"], $problem->getTitle());
		$this->assertEquals($r["alias"], $problem->getAlias());
		$this->assertEquals($r["validator"], $problem->getValidator());
		$this->assertEquals($r["time_limit"], $problem->getTimeLimit());
		$this->assertEquals($r["memory_limit"], $problem->getMemoryLimit());
		$this->assertEquals($r["order"], $problem->getOrder());
		$this->assertEquals($r["source"], $problem->getSource());

		// Verify author username -> author id conversion
		$user = UsersDAO::getByPK($problem->getAuthorId());
		$this->assertEquals($user->getUsername(), $r["author_username"]);

		// Verify problem contents.zip were copied
		$targetpath = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $problem->getAlias() . DIRECTORY_SEPARATOR;

		$this->assertFileExists($targetpath . "contents.zip");
		$this->assertFileExists($targetpath . "testplan");
		$this->assertFileExists($targetpath . "cases");
		$this->assertFileExists($targetpath . "statements" . DIRECTORY_SEPARATOR . "en.html");

		// Default data
		$this->assertEquals(0, $problem->getVisits());
		$this->assertEquals(0, $problem->getSubmissions());
		$this->assertEquals(0, $problem->getAccepted());
		$this->assertEquals(0, $problem->getDifficulty());
	}

	/**
	 * Test that sends incomplete requests
	 */
	public function testRequiredParameters() {
		
		// Get File Uploader Mock and tell Omegaup API to use it
		FileHandler::SetFileUploader($this->createFileUploaderMock());


		// Array of valid keys
		$valid_keys = array(
			"title",
			"validator",
			"time_limit",
			"memory_limit",
			"source",
			"author_username",
			"alias"
		);

		foreach ($valid_keys as $key) {

			// Get the problem data
			$problemData = ProblemsFactory::getRequest();
			$r = $problemData["request"];
			$problemAuthor = $problemData["author"];
						
			// Login user
			$r["auth_token"] = $this->login($problemAuthor);

			// Unset key
			unset($r[$key]);

			try {

				// Call the API				
				$response = ProblemController::apiCreate($r);
			} catch (InvalidParameterException $e) {
				// We're OK, clean up our mess and continue
				unset($_REQUEST);
				continue;
			}

			$this->fail("Exception was expected. Parameter: " . $key);
		}
	}

	/**
	 * Test that we are able to submit a problem without testplan
	 */
	public function testValidProblemNoTestplan() {

		// Get the problem data
        $problemData = ProblemsFactory::getRequest(OMEGAUP_RESOURCES_ROOT."triangulos.zip");
		$r = $problemData["request"];
		$problemAuthor = $problemData["author"];

		// Login user
		$r["auth_token"] = $this->login($problemAuthor);

		// Get File Uploader Mock and tell Omegaup API to use it
		FileHandler::SetFileUploader($this->createFileUploaderMock());

		// Call the API				
		$response = ProblemController::apiCreate($r);

		// Validate
		// Verify response		
		$this->assertEquals("ok", $response["status"]);
		$this->assertEquals("cases/1.in", $response["uploaded_files"][0]);

		// Verify data in DB
		$problem_mask = new Problems();
		$problem_mask->setTitle($r["title"]);
		$problems = ProblemsDAO::search($problem_mask);

		// Check that we only retreived 1 element
		$this->assertEquals(1, count($problems));
		$problem = $problems[0];

		// Verify contest was found
		$this->assertNotNull($problem);
		$this->assertNotNull($problem->getProblemId());

		// Verify DB data
		$this->assertEquals($r["title"], $problem->getTitle());
		$this->assertEquals($r["alias"], $problem->getAlias());
		$this->assertEquals($r["validator"], $problem->getValidator());
		$this->assertEquals($r["time_limit"], $problem->getTimeLimit());
		$this->assertEquals($r["memory_limit"], $problem->getMemoryLimit());
		$this->assertEquals($r["order"], $problem->getOrder());
		$this->assertEquals($r["source"], $problem->getSource());

		// Verify author username -> author id conversion
		$user = UsersDAO::getByPK($problem->getAuthorId());
		$this->assertEquals($user->getUsername(), $r["author_username"]);

		// Verify problem contents.zip were copied
		$targetpath = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $problem->getAlias() . DIRECTORY_SEPARATOR;

		$this->assertFileExists($targetpath . "contents.zip");
		$this->assertFileExists($targetpath . "cases.zip");
		$this->assertFileExists($targetpath . "cases");
		$this->assertFileExists($targetpath . "inputname");
		$this->assertFileExists($targetpath . "statements" . DIRECTORY_SEPARATOR . "es.html");

		// Default data
		$this->assertEquals(0, $problem->getVisits());
		$this->assertEquals(0, $problem->getSubmissions());
		$this->assertEquals(0, $problem->getAccepted());
		$this->assertEquals(0, $problem->getDifficulty());
	}

	/**
	 * Test that we are able to submit a problem without testplan
	 */
	public function testValidProblemWithNonUTF8CharsInStmt() {

		// Get the problem data
        $problemData = ProblemsFactory::getRequest(OMEGAUP_RESOURCES_ROOT."nonutf8stmt.zip");
		$r = $problemData["request"];
		$problemAuthor = $problemData["author"];

		// Login user
		$r["auth_token"] = $this->login($problemAuthor);

		// Get File Uploader Mock and tell Omegaup API to use it
		FileHandler::SetFileUploader($this->createFileUploaderMock());

		// Call the API				
		$response = ProblemController::apiCreate($r);

		// Verify response
		$this->assertEquals("ok", $response["status"]);
		
		// Get problem info from DB
        $problem_mask = new Problems();
        $problem_mask->setTitle($r["title"]);
        $problems = ProblemsDAO::search($problem_mask);                
        $this->assertEquals(1, count($problems));        
        $problem = $problems[0];
        
        // Verify problem contents.zip were copied
        $targetpath = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $problem->getAlias() . DIRECTORY_SEPARATOR;                
        $this->assertFileExists($targetpath . "contents.zip");                        
        $this->assertFileExists($targetpath . "cases.zip");
        $this->assertFileExists($targetpath . "cases");
        $this->assertFileExists($targetpath . "inputname");
        $this->assertFileExists($targetpath . "statements". DIRECTORY_SEPARATOR . "es.html");
        $this->assertFileExists($targetpath . "statements". DIRECTORY_SEPARATOR . "es.markdown");
        
        // Verify we have the accents, lol
        $markdown_contents = file_get_contents($targetpath . "statements". DIRECTORY_SEPARATOR . "es.markdown");
        if (strpos($markdown_contents, "ó") === false)
        {
            $this->fail("ó not found when expected.");
        }          
        
        $html_contents = file_get_contents($targetpath . "statements". DIRECTORY_SEPARATOR . "es.html");
        if (strpos($html_contents, "ó") === false)
        {
            $this->fail("ó not found when expected.");
        }
	}	
}

