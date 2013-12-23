<?php

/**
 * Description of RunsFactory
 *
 * @author joemmanuel
 */

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
class GraderMock extends Grader {

	public function Grade($runId) {
		return;
	}

}

class RunsFactory {

	/**
	 * Builds and returns a request object to be used for RunController::apiCreate
	 * 
	 * @param type $problemData
	 * @param type $contestData
	 * @param type $contestant
	 * @return Request
	 */
	private static function createRequestCommon($problemData, $contestData, $contestant) {
		
		// Create an empty request
		$r = new Request();

		// Log in as contestant
		$r["auth_token"] = OmegaupTestCase::login($contestant);

		// Build request
		if (!is_null($contestData)) {
			$r["contest_alias"] = $contestData["request"]["alias"];
		}
		
		$r["problem_alias"] = $problemData["request"]["alias"];
		$r["language"] = "c";
		$r["source"] = "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }";

		//PHPUnit does not set IP address, doing it manually
		$_SERVER["REMOTE_ADDR"] = "127.0.0.1";
		
		return $r;
	}
	
	/**
	 * Creates a run
	 * 
	 * @param type $problemData
	 * @param type $contestData
	 * @param Users $contestant
	 * @return array
	 */
	public static function createRun($problemData, $contestData, $contestant) {

		// Our contestant has to open the contest before sending a run
		ContestsFactory::openContest($contestData, $contestant);

		// Then we need to open the problem
		ContestsFactory::openProblemInContest($contestData, $problemData, $contestant);

		$r = self::createRequestCommon($problemData, $contestData, $contestant);

		// Call API
		RunController::$grader = new GraderMock();
		$response = RunController::apiCreate($r);

		// Clean up
		unset($_REQUEST);
		
		return array(
			"request" => $r,
			"contestant" => $contestant,
			"response" => $response
		);
	}
	
	/**
	 * Creates a run to the given problem
	 * 
	 * @param type $problemData
	 * @param type $contestant
	 */
	public static function createRunToProblem($problemData, $contestant) {
		
		$r = self::createRequestCommon($problemData, null, $contestant);
		
		// Call API
		RunController::$grader = new GraderMock();
		$response = RunController::apiCreate($r);

		// Clean up
		unset($_REQUEST);
		
		return array(
			"request" => $r,
			"contestant" => $contestant,
			"response" => $response
		);		
	}
	
	/**
	 * Given a run id, set a score to a given run
	 * 
	 * @param type $runData
	 * @param int $points
	 * @param string $veredict
	 */
	public static function gradeRun($runData, $points = 1, $veredict = "AC") {
		
		$run = RunsDAO::getByAlias($runData["response"]["guid"]);
		
		$run->setVeredict($veredict);
		$run->setScore($points);
		$run->setContestScore($points * 100);
		$run->setStatus("ready");
		
		RunsDAO::save($run);				
	}

}

