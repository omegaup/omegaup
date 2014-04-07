<?php

/**
 * Description of UpdateProblem
 *
 * @author joemmanuel
 */


class UpdateProblemTest extends OmegaupTestCase {
	
	public function testUpdateProblemTitleAndContents() {
		
		// Get a problem
		$problemData = ProblemsFactory::createProblem();

		// Get a contest 
		$contestData = ContestsFactory::createContest();
		
		// Add the problem to the contest
		ContestsFactory::addProblemToContest($problemData, $contestData);

		// Create our contestant
		$contestant = UserFactory::createUser();
		
		// Create a run
		$runData[0] = RunsFactory::createRun($problemData, $contestData, $contestant);
		$runData[1] = RunsFactory::createRun($problemData, $contestData, $contestant);
		
		// Grade the run
		RunsFactory::gradeRun($runData[0]);
		RunsFactory::gradeRun($runData[1]);
		
		// Get File Uploader Mock and tell Omegaup API to use it
		FileHandler::SetFileUploader($this->createFileUploaderMock());
		
		// Update Problem calls grader to rejudge, we need to detour grader calls
		// We will submit 2 runs to the problem, so we can expect 2 calls to grader
		// to rejudge them
		$this->detourGraderCalls($this->exactly(2));
		
		// Prepare request
		$r = new Request();
		$r["title"] = "new title";
		$r["time_limit"] = 12345;
		$r["problem_alias"] = $problemData["request"]["alias"];
		
		// Set file upload context
        $_FILES['problem_contents']['tmp_name'] = OMEGAUP_RESOURCES_ROOT."triangulos.zip";
		
		// Log in as contest director
		$r["auth_token"] = $this->login($problemData["author"]);
		
		//Call API
		$response = ProblemController::apiUpdate($r);
		
		// Verify data in DB
		$problem_mask = new Problems();
		$problem_mask->setTitle($r["title"]);
		$problems = ProblemsDAO::search($problem_mask);

		// Check that we only retreived 1 element
		$this->assertEquals(1, count($problems));
		
		// Validate rsponse
		$this->assertEquals("ok", $response["status"]);
		$this->assertEquals("cases/1.in", $response["uploaded_files"][0]);
		
		// Verify problem contents.zip were copied
		$targetpath = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $r["problem_alias"] . DIRECTORY_SEPARATOR;

		$this->assertFileExists($targetpath . "contents.zip");
		$this->assertFileExists($targetpath . "cases.zip");
		$this->assertFileExists($targetpath . "cases");
		$this->assertFileExists($targetpath . "inputname");
		$this->assertFileExists($targetpath . "statements" . DIRECTORY_SEPARATOR . "es.html");
		
		// Check update in statements
		$statement = file_get_contents($targetpath . "statements" . DIRECTORY_SEPARATOR . "es.html");
		$this->assertContains("perímetro", $statement);
				
	}
	
	/**
	 * Test apiUpdateStatement
	 */
	public function testProblemStatementUpdate() {
		
		// Get a problem (with 'es' statements)
		$problemData = ProblemsFactory::createProblem(OMEGAUP_RESOURCES_ROOT . "triangulos.zip");
		
		// Update statement
		$statement = "This is the new statement \$x\$";
		$response = ProblemController::apiUpdateStatement(new Request(array(
			"auth_token" => $this->login($problemData["author"]),
			"problem_alias" => $problemData["request"]["alias"],
			"statement" => $statement
		)));
		
		$this->assertEquals($response["status"], "ok");	

		// Check statment contents
		$targetpath = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $problemData["request"]["alias"] . DIRECTORY_SEPARATOR;
		$statementHtmlContents = file_get_contents($targetpath . "statements" . DIRECTORY_SEPARATOR . "es.html");
		$statementMarkdownContents = file_get_contents($targetpath . "statements" . DIRECTORY_SEPARATOR . "es.markdown");
		
		$this->assertContains("<p>This is the new statement \$x\$</p>", $statementHtmlContents);
		$this->assertContains($statement, $statementMarkdownContents);		
	}
	
	
	/**
	 * Test apiUpdateStatement with embedded imgs via data URI 
	 */
	public function testProblemStatementUpdateWithImagesAsDataURI() {
		
		// Get a problem (with 'es' statements)
		$problemData = ProblemsFactory::createProblem(OMEGAUP_RESOURCES_ROOT . "triangulos.zip");
		
		// Update statement
		$imgUri = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==";
		$imgFilename = 'af6a4603e039cca2f6823d287f6c87e561aa6e68.png';
		
		$statement = "This is the new statement with an image omg ![Alt text]($imgUri \"Optional title\")";
		$response = ProblemController::apiUpdateStatement(new Request(array(
			"auth_token" => $this->login($problemData["author"]),
			"problem_alias" => $problemData["request"]["alias"],
			"statement" => $statement
		)));
		
		$this->assertEquals($response["status"], "ok");	

		// Check statment contents
		$targetpath = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $problemData["request"]["alias"] . DIRECTORY_SEPARATOR;
		$statementHtmlContents = file_get_contents($targetpath . "statements" . DIRECTORY_SEPARATOR . "es.html");
		$statementMarkdownContents = file_get_contents($targetpath . "statements" . DIRECTORY_SEPARATOR . "es.markdown");
		
		$this->assertFileExists(IMAGES_PATH . $imgFilename);		
		$this->assertContains("<img src=\"" . IMAGES_URL_PATH . $imgFilename . "\" alt=\"Alt text\" title=\"Optional title\" />", $statementHtmlContents);
		$this->assertContains($statement, $statementMarkdownContents);		
	}
	
	/**
	 * Tests update problem: on error, original contesnts should persist
	 */
	public function testUpdateProblemFailed() {				
		// Get a problem
		$problemData = ProblemsFactory::createProblem();
	
		// Get File Uploader Mock and tell Omegaup API to use it
		FileHandler::SetFileUploader($this->createFileUploaderMock());
		
		// Update Problem calls grader to rejudge, we need to detour grader calls
		// We will submit 2 runs to the problem, so we can expect 2 calls to grader
		// to rejudge them
		$this->detourGraderCalls($this->exactly(0));
		
		// Prepare request
		$r = new Request();
		$r["title"] = "new title";
		$r["time_limit"] = 12345;
		$r["problem_alias"] = $problemData["request"]["alias"];
		
		// Set file upload context. This problem should fail
        $_FILES['problem_contents']['tmp_name'] = OMEGAUP_RESOURCES_ROOT."nostmt.zip";
		
		// Log in as contest director
		$r["auth_token"] = $this->login($problemData["author"]);
		
		// Call API. Should fail
		try {
			ProblemController::apiUpdate($r);
		} catch (ProblemDeploymentFailedException $e) {
			// Expected
		}
				
		// Verify contents were not erased
		$targetpath = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $r["problem_alias"] . DIRECTORY_SEPARATOR;
		$temppath = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $r["problem_alias"] . '_tmp' . DIRECTORY_SEPARATOR;
		
		$this->assertFileNotExists($temppath);
		$this->assertFileExists($targetpath . "contents.zip");
		$this->assertFileExists($targetpath . "cases.zip");
		$this->assertFileExists($targetpath . "cases");
		$this->assertFileExists($targetpath . "inputname");
		$this->assertFileExists($targetpath . "statements" . DIRECTORY_SEPARATOR . "es.html");
		
		// Check statements still is the original one
		$statement = file_get_contents($targetpath . "statements" . DIRECTORY_SEPARATOR . "es.html");
		$this->assertContains("<h1>Entrada</h1>", $statement);					
	}			
}

