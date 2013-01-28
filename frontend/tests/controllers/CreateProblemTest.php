<?php

/**
 * Tests for apiCreate in ProblemController
 *
 * @author joemmanuel
 */
require_once 'UserFactory.php';
require_once 'ProblemsFactory.php';
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
		$response = ProblemsController::apiCreate($r);

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
				$response = ProblemsController::apiCreate($r);
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
        $problemData = ProblemsFactory::getRequest("triangulos.zip");
		$r = $problemData["request"];
		$problemAuthor = $problemData["author"];

		// Login user
		$r["auth_token"] = $this->login($problemAuthor);

		// Get File Uploader Mock and tell Omegaup API to use it
		FileHandler::SetFileUploader($this->createFileUploaderMock());

		// Call the API				
		$response = ProblemsController::apiCreate($r);

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
        $problemData = ProblemsFactory::getRequest("nonutf8stmt.zip");
		$r = $problemData["request"];
		$problemAuthor = $problemData["author"];

		// Login user
		$r["auth_token"] = $this->login($problemAuthor);

		// Get File Uploader Mock and tell Omegaup API to use it
		FileHandler::SetFileUploader($this->createFileUploaderMock());

		// Call the API				
		$response = ProblemsController::apiCreate($r);

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

	/**
	 * Problem: PHPUnit does not support is_uploaded_file and move_uploaded_file
	 * native functions of PHP to move files around needed for store zip contents
	 * in the required places.
	 * 
	 * Solution: We abstracted those PHP native functions in an object FileUploader.
	 * We need to create a new FileUploader object that uses our own implementations.
	 * 
	 * Here we create a FileUploader and set our own implementations of is_uploaded_file 
	 * and move_uploaded_file. PHPUnit will intercept those calls and use our owns instead (mock). 
	 * Moreover, it will validate that they were actually called.
	 * 
	 * @return $fileUploaderMock
	 */
	private function createFileUploaderMock() {

		// Create fileUploader mock                        
		$fileUploaderMock = $this->getMock('FileUploader', array('IsUploadedFile', 'MoveUploadedFile'));

		// Detour IsUploadedFile function inside FileUploader to our own IsUploadedFile
		$fileUploaderMock->expects($this->any())
				->method('IsUploadedFile')
				->will($this->returnCallback(array($this, 'IsUploadedFile')));

		// Detour MoveUploadedFile function inside FileUploader to our own MoveUploadedFile
		$fileUploaderMock->expects($this->any())
				->method('MoveUploadedFile')
				->will($this->returnCallback(array($this, 'MoveUploadedFile')));

		return $fileUploaderMock;
	}

	/**
	 * Redefinition of IsUploadedFile
	 * 
	 * @param string $filename
	 * @return type
	 */
	public function IsUploadedFile($filename) {
		return file_exists($filename);
	}

	/**
	 * Redefinition of MoveUploadedFile
	 * 
	 * @return type
	 */
	public function MoveUploadedFile() {
		$filename = func_get_arg(0);
		$targetpath = func_get_arg(1);

		return copy($filename, $targetpath);
	}

}

