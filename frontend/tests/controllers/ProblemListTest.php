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
}

