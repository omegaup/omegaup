<?php

/**
 * Description of CreateRun
 *
 * @author joemmanuel
 */
require_once 'ProblemsFactory.php';
require_once 'ContestsFactory.php';

class CreateRun extends OmegaupTestCase {

	private $graderMock;

	/**
	 * Basic new run test
	 */
	public function testNewRunValid() {

		// Get a problem
		$problemData = ProblemsFactory::createProblem();

		// Get a contest 
		$contestData = ContestsFactory::createContest();

		// Add the problem to the contest
		ContestsFactory::addProblemToContest($problemData, $contestData);

		// Create our contestant
		$contestant = UserFactory::createUser();

		// Our contestant has to open the contest before sending a run
		ContestsFactory::openContest($contestData, $contestant);

		// Then we need to open the problem
		ContestsFactory::openProblemInContest($contestData, $problemData, $contestant);

		// Create an empty request
		$r = new Request();

		// Log in as contest director
		$r["auth_token"] = $this->login($contestant);

		// Build request
		$r["contest_alias"] = $contestData["request"]["alias"];
		$r["problem_alias"] = $problemData["request"]["alias"];
		$r["language"] = "c";
		$r["source"] = "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }";

		//PHPUnit does not set IP address, doing it manually
		$_SERVER["REMOTE_ADDR"] = "127.0.0.1";

		// Create a fake Grader object which will always return true (see
		// next line)
		$this->graderMock = $this->getMock('Grader', array('Grade'));

		// Set expectations: 
		$this->graderMock->expects($this->once())
				->method('Grade')
				->will($this->returnValue(true));

		// Detour all Grader::grade() calls to our mock
		RunController::$grader = $this->graderMock;

		// Call API
		$response = RunController::apiCreate($r);

		// Validate
		$this->assertEquals("ok", $response["status"]);
		$this->assertArrayHasKey("guid", $response);

		// Get run from DB
		$run = RunsDAO::getByAlias($response["guid"]);
		$this->assertNotNull($run);
		
		// Get contest from DB to check times with respect to contest start
		$contest = ContestsDAO::getByAlias($contestData["request"]["alias"]);		

		// Validate data        
		$this->assertEquals($r["language"], $run->getLanguage());
		$this->assertNotEmpty($run->getGuid());

		// Validate file created
		$filename = RUNS_PATH . DIRECTORY_SEPARATOR . $run->getGuid();
		$this->assertFileExists($filename);
		$fileContent = file_get_contents($filename);
		$this->assertEquals($r["source"], $fileContent);

		// Validate defaults
		$this->assertEquals("new", $run->getStatus());
		$this->assertEquals(0, $run->getRuntime());
		$this->assertEquals(0, $run->getMemory());
		$this->assertEquals(0, $run->getScore());
		$this->assertEquals(0, $run->getContestScore());
		$this->assertEquals("127.0.0.1", $run->getIp());
		$this->assertEquals((time() - strtotime($contest->getStartTime())) / 60, $run->getSubmitDelay(), '', 0.5);
		$this->assertEquals("JE", $run->getVeredict());
	}

}

