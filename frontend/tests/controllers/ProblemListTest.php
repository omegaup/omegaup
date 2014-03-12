<?php

/**
 * Description of ProblemList
 *
 * @author joemmanuel
 */
class ProblemList extends OmegaupTestCase {

	/**
	 * Gets the list of problems
	 */
	public function testProblemList() {

		// Get 3 problems
		$n = 3;
		for ($i = 0; $i < $n; $i++) {
			$problemData[$i] = ProblemsFactory::createProblem(null, null, 1 /* public */);
		}

		// Get 1 problem private, should not appear
		$privateProblemData = ProblemsFactory::createProblem(null, null, 0 /* public */);

		$r = new Request();
		$r["auth_token"] = $this->login(UserFactory::createUser());

		$response = ProblemController::apiList($r);

		// Check that all public problems are there
		for ($i = 0; $i < $n; $i++) {
			$exists = false;
			foreach ($response["results"] as $problemResponse) {
				if ($problemResponse === "ok") {
					continue;
				}
				
				if ($problemResponse['alias'] === $problemData[$i]["request"]["alias"]) {
					$exists = true;
					break;
				}
			}			
			if (!$exists) {
				$this->fail("Problem" . $problemData[$i]["request"]["alias"] . " is not in the list.");
			}
		}

		// Check private problem is not there
		$exists = false;
		foreach ($response['results'] as $problemResponse) {
			if ($problemResponse["alias"] === $privateProblemData["request"]["alias"]) {
				$exists = true;
				break;
			}
		}

		if ($exists) {
			$this->fail("Private problem" . $privateProblemData["request"]["alias"] . " is in the list.");
		}
	}
	
	/**
	 * Limit the output to one problem we know
	 */
	public function testLimitOffset() {
		
		// Get 3 problems
		$n = 3;
		for ($i = 0; $i < $n; $i++) {
			$problemData[$i] = ProblemsFactory::createProblem(null, null, 1 /* public */);
		}
		

		$r = new Request();
		$r["auth_token"] = $this->login(UserFactory::createUser());
		$r["rowcount"] = 1;
		$r["offset"] = 1;

		$response = ProblemController::apiList($r);

		$this->assertEquals(1, count($response["results"]));
		$this->assertEquals($problemData[1]["request"]["alias"], $response["results"][0]["alias"]);
	}

	/**
	 * The author should see his problems as well
	 *
	 */
	public function testPrivateProblemsShowToAuthor() {
		
		$author = UserFactory::createUser();
		$anotherAuthor = UserFactory::createUser();
		
		$problemDataPublic = ProblemsFactory::createProblem(null, null, 1 /* public */, $author);
		$problemDataPrivate = ProblemsFactory::createProblem(null, null, 0 /* public */, $author);		
		$anotherProblemDataPrivate = ProblemsFactory::createProblem(null, null, 0 /* public */, $anotherAuthor);		
		
		$r = new Request();
		$r["auth_token"] = $this->login($author);
		
		$response = ProblemController::apiList($r);
		
		$this->assertArrayContainsInKey($response["results"], "alias", $problemDataPrivate["request"]["alias"]);
	}
	
	/**
	 * The author should see his problems as well
	 *
	 */
	public function testAllPrivateProblemsShowToAdmin() {
		
		$author = UserFactory::createUser();
		
		$problemDataPublic = ProblemsFactory::createProblem(null, null, 1 /* public */, $author);
		$problemDataPrivate = ProblemsFactory::createProblem(null, null, 0 /* public */, $author);		
		
		$admin = UserFactory::createAdminUser();
		
		$r = new Request();
		$r["auth_token"] = $this->login($admin);
		
		$response = ProblemController::apiList($r);
		
		$this->assertArrayContainsInKey($response["results"], "alias", $problemDataPrivate["request"]["alias"]);
	}
	
	/**
	 * Test myList API
	 */
	public function testMyList() {
		
		// Get 3 problems
		$author = UserFactory::createUser();
		$n = 3;
		for ($i = 0; $i < $n; $i++) {
			$problemData[$i] = ProblemsFactory::createProblem(null, null, 1 /* public */, $author);
		}
		
		$r = new Request();
		$r["auth_token"] = $this->login($author);		

		$response = ProblemController::apiMyList($r);		
		$this->assertEquals(3, count($response["results"]));
		$this->assertEquals($problemData[2]["request"]["alias"], $response["results"][0]["alias"]);
	}
		
	/**
	 * Logged-in users will have their best scores for all problems
	 */
	public function testListContainsScores() {
		
		$contestant = UserFactory::createUser();
		
		$problemData = ProblemsFactory::createProblem();
		$problemDataNoRun = ProblemsFactory::createProblem();
		$problemDataDecimal = ProblemsFactory::createProblem();
		
		// We'll send consecutive runs, changing submission gap to 0 to avoid waiting
		RunController::$defaultSubmissionGap = 0;
		
		$runData = RunsFactory::createRunToProblem($problemData, $contestant);
		RunsFactory::gradeRun($runData);				
		
		$runDataDecimal = RunsFactory::createRunToProblem($problemDataDecimal, $contestant);
		RunsFactory::gradeRun($runDataDecimal, ".123456", "PA");
		
		
		RunController::$defaultSubmissionGap = 100;
		
		$r = new Request(array(
			"auth_token" => $this->login($contestant)
		));
		
		$response = ProblemController::apiList($r);
		
		// Validate results
		foreach ($response['results'] as $responseProblem) {
			if ($responseProblem['alias'] === $problemData['request']['alias']) {
				if ($responseProblem['score'] != 100.00) {
					$this->fail("Expected to see 100 score for this problem");
				}
			} else if ($responseProblem['alias'] === $problemDataDecimal['request']['alias']){
				if ($responseProblem['score'] != 12.35) {
					$this->fail("Expected to see 12.34 score for this problem");
				}
			} else {
				if ($responseProblem['score'] != 0) {
					$this->fail("Expected to see 0 score for this problem");
				}
			}
		}
	}
	
	/**
	 * Test that non-logged in users dont have score set
	 */
	public function testListScoresForNonLoggedIn() {				
		
		$problemData = ProblemsFactory::createProblem();						
		
		$r = new Request();
		
		$response = ProblemController::apiList($r);
		
		// Validate results
		foreach ($response['results'] as $responseProblem) {
			if ($responseProblem['score'] != "0") {
				$this->fail('Expecting score to be not set for non-logged in users');
			}			
		}
	}
	
	/**
	 * Test List API with query param
	 */
	public function testListWithAliasQuery() {				
		
		$problemDataPublic = ProblemsFactory::createProblem(null, null, 1 /* public */);
		$problemDataPrivate = ProblemsFactory::createProblem(null, null, 0 /* public */);		
		
		$user = UserFactory::createUser();		
		$admin = UserFactory::createAdminUser();
		
		// Expect public problem only
		$r = new Request();
		$r["auth_token"] = $this->login($user);
		$r["query"] = substr($problemDataPublic["request"]["title"], 2, 5);				
		$response = ProblemController::apiList($r);		
		$this->assertArrayContainsInKey($response["results"], "alias", $problemDataPublic["request"]["alias"]);
		
		// Expect 0 problems, matches are private for $user
		$r = new Request();
		$r["auth_token"] = $this->login($user);
		$r["query"] = substr($problemDataPrivate["request"]["title"], 2, 5);				
		$response = ProblemController::apiList($r);
		$this->assertEquals(0, count($response["results"]));
		
		// Expect 1 problem, admin can see private problem
		$r = new Request();
		$r["auth_token"] = $this->login($admin);
		$r["query"] = substr($problemDataPrivate["request"]["title"], 2, 5);				
		$response = ProblemController::apiList($r);		
		$this->assertArrayContainsInKey($response["results"], "alias", $problemDataPrivate["request"]["alias"]); 
		
		// Expect public problem only
		$r = new Request();
		$r["auth_token"] = $this->login($user);		
		$response = ProblemController::apiList($r);		
		$this->assertArrayContainsInKey($response["results"], "alias", $problemDataPublic["request"]["alias"]);
	}
}

