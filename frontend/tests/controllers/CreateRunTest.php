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
	private $contestData;

	/**
	 * Prepares the context to submit a run to a problem. Creates the contest,
	 * problem and opens them. 
	 * 
	 * @return Request
	 */
	private function setValidRequest($contest_public = 1) {
		
		// Get a problem
		$problemData = ProblemsFactory::createProblem();

		// Get a contest 
		$this->contestData = ContestsFactory::createContest(null, $contest_public);

		// Add the problem to the contest
		ContestsFactory::addProblemToContest($problemData, $this->contestData);

		// Create our contestant
		$contestant = UserFactory::createUser();
		
		// If the contest is private, add the user
		if ($contest_public === 0) {
			ContestsFactory::addUser($this->contestData, $contestant);
		}

		// Our contestant has to open the contest before sending a run
		ContestsFactory::openContest($this->contestData, $contestant);

		// Then we need to open the problem
		ContestsFactory::openProblemInContest($this->contestData, $problemData, $contestant);

		// Create an empty request
		$r = new Request();

		// Log in as contest director
		$r["auth_token"] = $this->login($contestant);

		// Build request
		$r["contest_alias"] = $this->contestData["request"]["alias"];
		$r["problem_alias"] = $problemData["request"]["alias"];
		$r["language"] = "c";
		$r["source"] = "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }";

		//PHPUnit does not set IP address, doing it manually
		$_SERVER["REMOTE_ADDR"] = "127.0.0.1";						
		
		return $r;
	}
	
	/**
	 * Detours the Grader calls.
	 * Problem: Submiting a new run invokes the Grader::grade() function which makes 
	 * a HTTP call to official grader using CURL. This call will fail if grader is
	 * not turned on. We are not testing the Grader functionallity itself, we are
	 * only validating that we populate the DB correctly and that we make a call
	 * to the function Grader::grade(), without executing the contents.
	 * 
	 * Solution: We create a phpunit mock of the Grader class. We create a fake 
	 * object Grader with the function grade() which will always return true
	 * and expects to be excecuted once.	 
	 *
	 */
	private function detourGraderCalls($times = null) {
		
		if (is_null($times)) {
			$times = $this->once();
		}
		
		// Create a fake Grader object which will always return true (see
		// next line)
		$this->graderMock = $this->getMock('Grader', array('Grade'));

		// Set expectations: 
		$this->graderMock->expects($times)
				->method('Grade')
				->will($this->returnValue(true));

		// Detour all Grader::grade() calls to our mock
		RunController::$grader = $this->graderMock;
	}
	
	/**
	 * Validate a run
	 * 
	 * @param type $r
	 * @param type $response
	 */
	private function assertRun($r, $response) {
		
		// Validate
		$this->assertEquals("ok", $response["status"]);
		$this->assertArrayHasKey("guid", $response);

		// Get run from DB
		$run = RunsDAO::getByAlias($response["guid"]);
		$this->assertNotNull($run);
		
		// Get contest from DB to check times with respect to contest start
		$contest = ContestsDAO::getByAlias($r["contest_alias"]);		

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
	
	/**
	 * Basic new run test
	 */
	public function testNewRunValid() {

		$r = $this->setValidRequest();
		$this->detourGraderCalls();

		// Call API
		$response = RunController::apiCreate($r);

		$this->assertRun($r, $response);
	}
	
	/**
	 * Cannot submit run when contest ended
	 * 
	 * @expectedException ForbiddenAccessException
	 */
	public function testRunWhenContestExpired() {
		
		$r = $this->setValidRequest();
		
		// Manually expire the contest		
        $contest = ContestsDAO::getByAlias($r["contest_alias"]);
        $contest->setFinishTime(Utils::GetTimeFromUnixTimestam(Utils::GetPhpUnixTimestamp() - 1));                        
        ContestsDAO::save($contest);
		
		// Call API
		RunController::apiCreate($r);
	}
	
	/**
	 * Test a valid submission to a private contest
	 */
	public function testRunToValidPrivateContest() {
		
		$r = $this->setValidRequest(0 /*private contest*/);
		$this->detourGraderCalls();

		// Call API
		$response = RunController::apiCreate($r);

		// Validate
		$this->assertEquals("ok", $response["status"]);
		$this->assertArrayHasKey("guid", $response);
		
	}
	
	/**
	 * Test a invalid submission to a private contest
	 * 
	 * @expectedException ForbiddenAccessException
	 */
	public function testRunPrivateContestWithUserNotRegistred() {
		
		$r = $this->setValidRequest(0 /*private contest*/);		
		
		// Create a second user not regitered to private contest
		$contestant2 = UserFactory::createUser();
		
		// Log in this second user
		$r["auth_token"] = self::login($contestant2);

		// Call API
		RunController::apiCreate($r);				
	}
	
	/**
	 * Cannot submit run when contest not started yet
	 * 
	 * @expectedException ForbiddenAccessException
	 */
	public function testRunWhenContestNotStarted() {
		
		$r = $this->setValidRequest();
		
		// Manually expire contest
        $contest = ContestsDAO::getByAlias($r["contest_alias"]);                
        $contest->setStartTime(Utils::GetTimeFromUnixTimestam(Utils::GetPhpUnixTimestamp() + 10));                        
        ContestsDAO::save($contest);
		
		// Call API
		RunController::apiCreate($r);
	}
	
	/**
	 * Test that a user cannot submit once he has already submitted something
	 * and the submissions gap time has not expired
	 * 
	 * @expectedException NotAllowedToSubmitException 
	 */
	public function testInvalidRunInsideSubmissionsGap() {
		
		// This API requires DAO cache be turned off 
		ContestsDAO::$useDAOCache = false;
		
		// Set the context
		$r = $this->setValidRequest();
		$this->detourGraderCalls();		
		
		// Set submissions gap of 20 seconds
        $contest = ContestsDAO::getByAlias($r["contest_alias"]);		
        $contest->setSubmissionsGap(20);
        ContestsDAO::save($contest);
		
		// Call API
		$response = RunController::apiCreate($r);

		// Validate the run
		$this->assertRun($r, $response);
		
		// Send a second run. This one should fail
		$response = RunController::apiCreate($r);		
	}
	
	/**
	 * Submission gap is per problem, not per contest
	 */
	public function testSubmissionGapIsPerProblem() {
		
		// Set the context
		$r = $this->setValidRequest();
		
		// Prepare the Grader mock, validate that grade is called 2 times
		// (we will use 2 problems for this test)
		$this->detourGraderCalls($this->exactly(2));		
		
		// Add a second problem to the contest
		$problemData2 = ProblemsFactory::createProblem();
		ContestsFactory::addProblemToContest($problemData2, $this->contestData);
				
		// Set submissions gap of 20 seconds
        $contest = ContestsDAO::getByAlias($r["contest_alias"]);		
        $contest->setSubmissionsGap(20);
        ContestsDAO::save($contest);
		
		// Call API, send a run for the first problem
		$response = RunController::apiCreate($r);
		$this->assertRun($r, $response);
		
		// Set the second problem as the target
		$r["problem_alias"] = $problemData2["request"]["alias"];
		$response = RunController::apiCreate($r);
		$this->assertRun($r, $response);		
	}

}

