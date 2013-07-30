<?php

/**
 * Description of ContestStatsTest
 *
 * @author joemmanuel
 */
class ContestStatsTest extends OmegaupTestCase {

	/**
	 * Check stats are ok for WA, AC, PA and total counts
	 * Also validates the max wait time guid
	 */
	public function testGetStats() {

		// Get a problem
		$problemData = ProblemsFactory::createProblem();

		// Get a contest 
		$contestData = ContestsFactory::createContest();

		// Add the problem to the contest
		ContestsFactory::addProblemToContest($problemData, $contestData);

		// Create our contestant
		$contestant = UserFactory::createUser();

		// Create a run that we will wait to grade it
		$maxWaitRunData = RunsFactory::createRun($problemData, $contestData, $contestant);

		// Wait 1 sec before pushiing more runs
		sleep(1);

		// Create some runs to be pending
		$pendingRunsCount = 10;
		$pendingRunsData = array();
		for ($i = 0; $i < $pendingRunsCount; $i++) {
			$pendingRunsData[$i] = RunsFactory::createRun($problemData, $contestData, $contestant);
		}

		$ACRunsCount = 7;
		$ACRunsData = array();
		for ($i = 0; $i < $ACRunsCount; $i++) {
			$ACRunsData[$i] = RunsFactory::createRun($problemData, $contestData, $contestant);

			// Grade the run
			RunsFactory::gradeRun($ACRunsData[$i]);
		}

		$WARunsCount = 5;
		$WARunsData = array();
		for ($i = 0; $i < $WARunsCount; $i++) {
			$WARunsData[$i] = RunsFactory::createRun($problemData, $contestData, $contestant);

			// Grade the run with WA
			RunsFactory::gradeRun($WARunsData[$i], 0, "WA");
		}

		// Create request
		$r = new Request();
		$r["contest_alias"] = $contestData["request"]["alias"];
		$r["auth_token"] = $this->login($contestData["director"]);

		// Call API
		$response = ContestController::apiStats($r);

		// Check number of pending runs
		$this->assertEquals(count($pendingRunsData) + 1 /* max wait run */, count($response["pending_runs"]));
		$this->assertEquals(count($ACRunsData), ($response["veredict_counts"]["AC"]));
		$this->assertEquals(count($WARunsData), ($response["veredict_counts"]["WA"]));

		$this->assertEquals($maxWaitRunData["response"]["guid"], $response["max_wait_time_guid"]);

		$this->assertEquals($pendingRunsCount + $ACRunsCount + $WARunsCount + 1, $response["total_runs"]);
		$this->assertEquals(1, $response["distribution"][100]);
	}

	/**
	 * Checks that, if there's no wait time, 0 is posted in max_wait_time
	 */
	public function testGetStatsNoWaitTime() {
		
		// Get a problem
		$problemData = ProblemsFactory::createProblem();

		// Get a contest 
		$contestData = ContestsFactory::createContest();

		// Add the problem to the contest
		ContestsFactory::addProblemToContest($problemData, $contestData);

		// Create our contestant
		$contestant = UserFactory::createUser();
		
		$ACRunsCount = 2;
		$ACRunsData = array();
		for ($i = 0; $i < $ACRunsCount; $i++) {
			$ACRunsData[$i] = RunsFactory::createRun($problemData, $contestData, $contestant);

			// Grade the run
			RunsFactory::gradeRun($ACRunsData[$i]);
		}
		
		// Create request
		$r = new Request();
		$r["contest_alias"] = $contestData["request"]["alias"];
		$r["auth_token"] = $this->login($contestData["director"]);

		// Call API
		$response = ContestController::apiStats($r);
		
		// Check number of pending runs
		$this->assertEquals($ACRunsCount, $response["total_runs"]);
		$this->assertEquals(0, $response["max_wait_time"]);
		$this->assertEquals(0, $response["max_wait_time_guid"]);						
	}

}

