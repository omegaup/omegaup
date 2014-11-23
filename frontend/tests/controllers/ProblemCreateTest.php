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
	public function testCreateValidProblem() {

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
		$this->assertEquals(substr($r["title"], 0, 32), $problem->getAlias());
		$this->assertEquals($r["validator"], $problem->getValidator());
		$this->assertEquals($r["time_limit"], $problem->getTimeLimit());
		$this->assertEquals($r["memory_limit"], $problem->getMemoryLimit());
		$this->assertEquals($r["order"], $problem->getOrder());
		$this->assertEquals($r["source"], $problem->getSource());
		$this->assertEqualSets($r["languages"], $problem->getLanguages());
		$this->assertEquals(0, $problem->slow);
		$this->assertEquals(10000, $problem->stack_limit);

		// Verify author username -> author id conversion
		$user = UsersDAO::getByPK($problem->getAuthorId());
		$this->assertEquals($user->getUsername(), $r["author_username"]);

		// Verify problem contents were copied
		$targetpath = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $problem->getAlias() . DIRECTORY_SEPARATOR;

		$this->assertFileExists($targetpath . "testplan");
		$this->assertFileExists($targetpath . "cases");
		$this->assertFileExists($targetpath . "statements" . DIRECTORY_SEPARATOR . "en.html");
		$this->assertFileExists($targetpath . "statements" . DIRECTORY_SEPARATOR . "en.markdown");

		// Default data
		$this->assertEquals(0, $problem->getVisits());
		$this->assertEquals(0, $problem->getSubmissions());
		$this->assertEquals(0, $problem->getAccepted());
		$this->assertEquals(0, $problem->getDifficulty());
	}

	/**
	 * Basic test for slow problems
	 */
	public function testSlowQueue() {
		// Get the problem data
		$problemData = ProblemsFactory::getRequest();
		$r = $problemData["request"];
		$r['time_limit'] = 8000;
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

		// Verify DB data
		$this->assertEquals(1, $problem->slow);
	}

	/**
	 * Basic test for slow problems
	 */
	public function testSlowQueueWithWallLimit() {
		// Get the problem data
		$problemData = ProblemsFactory::getRequest();
		$r = $problemData["request"];
		$r['time_limit'] = 8000;
		$r['overall_wall_time_limit'] = 20000;
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

		// Verify DB data
		$this->assertEquals(0, $problem->slow);
	}

	/**
	 * Basic test for creating a problem
	 */
	public function testCreateValidProblemWithINCases() {

		// Get the problem data
		$problemData = ProblemsFactory::getRequest(OMEGAUP_RESOURCES_ROOT . 'mrkareltastic.zip');
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
		$this->assertEquals("cases/g1.train0.in", $response["uploaded_files"][0]);
		$this->assertEquals("cases/g1.train0.out", $response["uploaded_files"][1]);

		// Verify problem contents were copied
		$targetpath = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $r["alias"] . DIRECTORY_SEPARATOR;

		$this->assertFileExists($targetpath . "testplan");
		$this->assertFileExists($targetpath . "cases/in/g1.train0.in");
		$this->assertFileExists($targetpath . "cases/out/g1.train0.out");
		$this->assertFileExists($targetpath . "cases");
		$this->assertFileExists($targetpath . "statements" . DIRECTORY_SEPARATOR . "es.html");
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
			"languages",
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
	 * Test that sends invalid languages.
	 */
	public function testInvalidLanguage() {

		// Get File Uploader Mock and tell Omegaup API to use it
		FileHandler::SetFileUploader($this->createFileUploaderMock());

		foreach (array("abc", "c,cpp,cows", "java,coffee,espresso") as $languages) {
			// Get the problem data
			$problemData = ProblemsFactory::getRequest();
			$r = $problemData["request"];
			$problemAuthor = $problemData["author"];

			// Login user
			$r["auth_token"] = $this->login($problemAuthor);
			$r["languages"] = $languages;
			try {
				// Call the API
				$response = ProblemController::apiCreate($r);
			} catch (InvalidParameterException $e) {
				// We're OK, clean up our mess and continue
				unset($_REQUEST);
				continue;
			}
			$this->fail("Exception was expected. Language set: $languages");
		}
	}

	/**
	 * Test that we are able to submit a problem without testplan
	 */
	public function testValidProblemNoTestplan() {

		// Get the problem data
		$problemData = ProblemsFactory::getRequest(OMEGAUP_RESOURCES_ROOT . "triangulos.zip");
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
		$this->assertEquals(substr($r["title"], 0, 32), $problem->getAlias());
		$this->assertEquals($r["validator"], $problem->getValidator());
		$this->assertEquals($r["time_limit"], $problem->getTimeLimit());
		$this->assertEquals($r["memory_limit"], $problem->getMemoryLimit());
		$this->assertEquals($r["order"], $problem->getOrder());
		$this->assertEquals($r["source"], $problem->getSource());

		// Verify author username -> author id conversion
		$user = UsersDAO::getByPK($problem->getAuthorId());
		$this->assertEquals($user->getUsername(), $r["author_username"]);

		// Verify problem contents were copied
		$targetpath = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $problem->getAlias() . DIRECTORY_SEPARATOR;

		$this->assertFileExists($targetpath . "cases");
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
		$problemData = ProblemsFactory::getRequest(OMEGAUP_RESOURCES_ROOT . "nonutf8stmt.zip");
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

		// Verify problem contents were copied
		$targetpath = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $problem->getAlias() . DIRECTORY_SEPARATOR;
		$this->assertFileExists($targetpath . "cases");
		$this->assertFileExists($targetpath . "statements" . DIRECTORY_SEPARATOR . "es.html");
		$this->assertFileExists($targetpath . "statements" . DIRECTORY_SEPARATOR . "es.markdown");

		// Verify we have the accents, lol
		$markdown_contents = file_get_contents($targetpath . "statements" . DIRECTORY_SEPARATOR . "es.markdown");
		if (strpos($markdown_contents, "ó") === false) {
			$this->fail("ó not found when expected.");
		}

		$html_contents = file_get_contents($targetpath . "statements" . DIRECTORY_SEPARATOR . "es.html");
		if (strpos($html_contents, "ó") === false) {
			$this->fail("ó not found when expected.");
		}
	}

	/**
	 * Test that image upload works.
	 */
	public function testImageUpload() {
		$imageSha1 = '27938919b32434b39486d04db57d5b8dccbe881b';
		$imageExtension = 'jpg';
		$imageAbsoluteUrl = 'http://i.imgur.com/fUkvDkw.png';

		// Get the problem data
		$problemData = ProblemsFactory::getRequest(OMEGAUP_RESOURCES_ROOT . "imagetest.zip");
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

		// Verify problem contents were copied
		$targetpath = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $r["alias"] . DIRECTORY_SEPARATOR;
		$this->assertFileExists($targetpath . "cases");
		$this->assertFileExists($targetpath . "statements" . DIRECTORY_SEPARATOR . "es.html");
		$this->assertFileExists($targetpath . "statements" . DIRECTORY_SEPARATOR . "es.markdown");
		$this->assertFileExists($targetpath . "statements" . DIRECTORY_SEPARATOR . "bunny.jpg");
		$this->assertFileExists(IMAGES_PATH . $imageSha1 . "." . $imageExtension);

		// Verify that all the images are there.
		$html_contents = file_get_contents($targetpath . "statements" . DIRECTORY_SEPARATOR . "es.html");
		$this->assertContains("<img src=\"". IMAGES_URL_PATH ."$imageSha1.$imageExtension\"", $html_contents);
		// And the direct URL.
		$this->assertContains("<img src=\"$imageAbsoluteUrl\"", $html_contents);
		// And the unmodified, not found image.
		$this->assertContains("<img src=\"notfound.jpg\"", $html_contents);
		
		// Do image paht replacement checks in the markdown file
		$markdown_contents = file_get_contents($targetpath . "statements" . DIRECTORY_SEPARATOR . "es.markdown");
		$this->assertContains("![Saluda](" . IMAGES_URL_PATH . "$imageSha1.$imageExtension)", $markdown_contents);
		// And the direct URL.
		$this->assertContains("![Saluda]($imageAbsoluteUrl)", $markdown_contents);
		// And the unmodified, not found image.
		$this->assertContains("![404](notfound.jpg)", $markdown_contents);
	}

		
	/**
	 * Test that we can produce a valid alias from the title
	 */
	public function testConstructAliasFromTitle() {

		// Get the problem data
		$problemData = ProblemsFactory::getRequest();
		$r = $problemData["request"];
		$problemAuthor = $problemData["author"];
		
		// Set a valid "complex" title
		$r["title"] = "Lá Venganza Del Malvado Dr. Liraaa";

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
		
		// Verify problem contents were copied
		$targetpath = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $problem->getAlias() . DIRECTORY_SEPARATOR;

		$this->assertFileExists($targetpath . "testplan");
		$this->assertFileExists($targetpath . "cases");
		$this->assertFileExists($targetpath . "statements" . DIRECTORY_SEPARATOR . "en.html");						
	}
	
	/**
	 * Basic test for uploadin problem without statement
	 * 
	 * @expectedException InvalidParameterException
	 */
	public function testCreateProblemWithoutStatement() {

		// Get the problem data
		$problemData = ProblemsFactory::getRequest(OMEGAUP_RESOURCES_ROOT . "nostmt.zip");
		$r = $problemData["request"];
		$problemAuthor = $problemData["author"];

		// Login user
		$r["auth_token"] = $this->login($problemAuthor);

		// Get File Uploader Mock and tell Omegaup API to use it
		FileHandler::SetFileUploader($this->createFileUploaderMock());

		// Call the API				
		$response = ProblemController::apiCreate($r);			
	}
	
	/**
	 * Basic test for uploadin problem missing outputs
	 * 
	 * @expectedException InvalidParameterException
	 */
	public function testCreateProblemMissingOutput() {

		// Get the problem data
		$problemData = ProblemsFactory::getRequest(OMEGAUP_RESOURCES_ROOT . "missingout.zip");
		$r = $problemData["request"];
		$problemAuthor = $problemData["author"];

		// Login user
		$r["auth_token"] = $this->login($problemAuthor);

		// Get File Uploader Mock and tell Omegaup API to use it
		FileHandler::SetFileUploader($this->createFileUploaderMock());

		// Call the API				
		$response = ProblemController::apiCreate($r);	
	}
}

