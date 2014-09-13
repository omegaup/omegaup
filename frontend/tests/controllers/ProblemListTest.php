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

	/**
	 * Test 'page', 'order_by' and 'mode' parametes of the apiList() method, and search by title.
	 */
	public function testProblemListPager() {
		// Create a user and some problems with submissions for the tests.
		$contestant = UserFactory::createUser();
		RunController::$defaultSubmissionGap = 0;
		for ($i = 0; $i < 6; $i++) {
			$problemData[$i] = ProblemsFactory::createProblem(null, null, 1);
			$runs = $i / 2;
			for ($r = 0; $r < $runs; $r++) {
				$runData = RunsFactory::createRunToProblem($problemData[$i], $contestant);
				$points = rand(0, 100);
				$veredict = 'WA';
				if ($points > 0) {
					$veredict = ($points == 100) ? 'AC' : 'PA';
				}

				RunsFactory::gradeRun($runData, $points / 100, $veredict);
			}
		}
		RunController::$defaultSubmissionGap = 100;


		$request = new Request();
		$request['auth_token'] = $this->login($contestant);
		$response = ProblemController::apiList($request);

		// Test search by title
		$titles = array();
		foreach ($response['results'] as $problem) {
			array_push($titles, $problem['title']);
		}
		foreach ($titles as $title) {
			$request['query'] = $title;
			$response = ProblemController::apiList($request);
			$this->assertTrue(count($response['results']) == 1);
			$this->assertTrue($title === $response['results'][0]['title']);
		}

		$request['query'] = null;
		$response = ProblemController::apiList($request);
		$total = $response['total'];
		$pages = intval(($total + PROBLEMS_PER_PAGE - 1) / PROBLEMS_PER_PAGE);

		// The following tests will try the different scenarios that can occur
		// with the additions of the three features to apiList(), that is, paging,
		// order by column and order mode: Call apiList() with and without
		// pagination, for each allowed ordering and each possible order mode. 
		$modes = array('asc', 'desc');
		$columns = array('title', 'submissions', 'accepted', 'ratio', 'points', 'score');
		$counter = 0;
		for ($paging = 0; $paging <= 1; $paging++) {
			foreach ($columns as $col) {
				foreach ($modes as $mode) {
					$first = null;
					$last = null;
					$request['mode'] = $mode;
					$request['order_by'] = $col;
					if ($paging == 1) {
						$request['page'] = 1;
						$response = ProblemController::apiList($request);
						$first = $response['results'];
						$request['page'] = $pages;
						$response = ProblemController::apiList($request);
						$last = $response['results'];

						// Test number of problems per page
						$this->assertEquals(count($first), PROBLEMS_PER_PAGE);
					} else {
						$request['page']= null;
						$response = ProblemController::apiList($request);
						$first = $response['results'];
						$last = $first;
					}

					$i = 0;
					$j = count($last) - 1;
					if ($col === 'title') {
						$comp = strcmp($first[$i]['title'], $last[$j]['title']);
						if ($mode === 'asc') {
							$this->assertTrue($comp <= 0);
						} else {
							$this->assertTrue($comp >= 0);
						}
					} else {
						if ($mode === 'asc') {
							$this->assertTrue($first[$i][$col] <= $last[$j][$col]);
						} else {
							$this->assertTrue($first[$i][$col] >= $last[$j][$col]);
						}
					}
				}
			}
		}
	}
}

