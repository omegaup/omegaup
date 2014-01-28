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
}

