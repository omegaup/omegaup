<?php

/**
 * Description of DetailsProblem
 *
 * @author joemmanuel
 */

class ProblemDetailsTest extends OmegaupTestCase {

	/**
	 *
	 */
	public function testViewProblemInAContestDetailsValid() {

		// Get a contest
		$contestData = ContestsFactory::createContest();

		// Get a user to be the author
		$author = UserFactory::createUser();

		// Get a problem
		$problemData = ProblemsFactory::createProblem(null, null, 1, $author);

		// Add the problem to the contest
		ContestsFactory::addProblemToContest($problemData, $contestData);

		// Get a user for our scenario
		$contestant = UserFactory::createUser();

		// Prepare our request
		$r = new Request();
		$r["contest_alias"] = $contestData["request"]["alias"];
		$r["problem_alias"] = $problemData["request"]["alias"];

		// Log in the user
		$r["auth_token"] = $this->login($contestant);

		// Call api
		$response = ProblemController::apiDetails($r);

		// Get problem and contest from DB to check it
        $problemDAO = ProblemsDAO::getByAlias($problemData["request"]["alias"]);
		$contestDAO = ContestsDAO::getByAlias($contestData["request"]["alias"]);
		$contestantsDAO = UsersDAO::search(new Users(array("username" => $contestant->getUsername())));
		$contestantDAO = $contestantsDAO[0];

        // Assert data
        $this->assertEquals($response["title"], $problemDAO->getTitle());
        $this->assertEquals($response["alias"], $problemDAO->getAlias());
        $this->assertEquals($response["validator"], $problemDAO->getValidator());
        $this->assertEquals($response["time_limit"], $problemDAO->getTimeLimit());
        $this->assertEquals($response["memory_limit"], $problemDAO->getMemoryLimit());
        $this->assertEquals($response["problemsetter"]['username'], $author->username);
        $this->assertEquals($response["problemsetter"]['name'], $author->name);
        $this->assertEquals($response["source"], $problemDAO->getSource());
        $this->assertContains("<h1>Entrada</h1>", $response["problem_statement"]);
        $this->assertEquals($response["order"], $problemDAO->getOrder());
		$this->assertEquals($response["score"], 0);

        // Default data
        $this->assertEquals(0, $problemDAO->getVisits());
        $this->assertEquals(0, $problemDAO->getSubmissions());
        $this->assertEquals(0, $problemDAO->getAccepted());
        $this->assertEquals(0, $problemDAO->getDifficulty());

        // Verify that we have an empty array of runs
        $this->assertEquals(0, count($response["runs"]));

        // Verify that problem was marked as Opened
        $problem_opened = ContestProblemOpenedDAO::getByPK($contestDAO->getContestId(), $problemDAO->getProblemId(), $contestantDAO->getUserId());
        $this->assertNotNull($problem_opened);

        // Verify open time
        $this->assertEquals(Utils::GetPhpUnixTimestamp(), Utils::GetPhpUnixTimestamp($problem_opened->getOpenTime()));

	}

	/**
	 * Common code for testing the statement's source.
	 */
	public function internalViewProblemStatement($type, $expected_text) {

		// Get a contest
		$contestData = ContestsFactory::createContest();

		// Get a problem
		$problemData = ProblemsFactory::createProblem();

		// Add the problem to the contest
		ContestsFactory::addProblemToContest($problemData, $contestData);

		// Get a user for our scenario
		$contestant = UserFactory::createUser();

		// Prepare our request
		$r = new Request();
		$r["problem_alias"] = $problemData["request"]["alias"];

		// Log in the user
		$r["auth_token"] = $this->login($contestant);


		// Call api
		$r["statement_type"] = $type;
		$response = ProblemController::apiDetails($r);

				// Assert data
				$this->assertContains($expected_text, $response["problem_statement"]);
	}

	/**
	 * Problem statmeent is returned in HTML.
	 */
	public function testViewProblemStatementHtml() {
		$this->internalViewProblemStatement("html", "<h1>Entrada</h1>");
	}

	/**
	 * Problem statmeent is returned in Markdown.
	 */
	public function testViewProblemStatementMarkdown() {
		$this->internalViewProblemStatement("markdown", "# Entrada");
	}

	/**
	 * @expectedException NotFoundException
	 */
	public function testViewProblemStatementInvalidType() {
		$this->internalViewProblemStatement("not_html_or_markdown", "");
	}

	public function testProblemDetailsNotInContest() {

		// Get 1 problem public
		$problemData = ProblemsFactory::createProblem(null, null, 1 /* public */);

		// Get a user for our scenario
		$contestant = UserFactory::createUser();

		// Prepare our request
		$r = new Request();
		$r["problem_alias"] = $problemData["request"]["alias"];

		// Log in the user
		$r["auth_token"] = $this->login($contestant);

		// Call api
		$response = ProblemController::apiDetails($r);

		$this->assertEquals($response["alias"], $problemData["request"]["alias"]);
	}

	/**
	 * User not invited to private contest can't see problem details
	 *
	 * @expectedException ForbiddenAccessException
	 */
	public function testPrivateProblemDetailsNotInContest() {

		// Get 1 problem public
		$problemData = ProblemsFactory::createProblem(null, null, 0 /* private */);

		// Get a user for our scenario
		$contestant = UserFactory::createUser();

		// Prepare our request
		$r = new Request();
		$r["problem_alias"] = $problemData["request"]["alias"];

		// Log in the user
		$r["auth_token"] = $this->login($contestant);

		// Call api
		$response = ProblemController::apiDetails($r);
	}

	/**
	 * Best score is returned
	 */
	public function testScoreInDetailsOutsideContest() {

		// Create problem
		$problemData = ProblemsFactory::createProblem();

		// Create contestant
		$contestant = UserFactory::createUser();

		// Create 2 runs, 100 and 50.
		$runData = RunsFactory::createRunToProblem($problemData, $contestant);
		$runDataPA = RunsFactory::createRunToProblem($problemData, $contestant);
		RunsFactory::gradeRun($runData);
		RunsFactory::gradeRun($runDataPA, 0.5, "PA");

		// Call API
		$response = ProblemController::apiDetails(new Request(array(
			"auth_token" => $this->login($contestant),
			"problem_alias" => $problemData["request"]["alias"]
		)));

		$this->assertEquals(100.00, $response["score"]);
	}

	/**
	 * Best score is returned, problem inside a contest
	 */
	public function testScoreInDetailsInsideContest() {

		// Create problem and contest
		$problemData = ProblemsFactory::createProblem();
		$contestData = ContestsFactory::createContest();
		ContestsFactory::addProblemToContest($problemData, $contestData);

		// Create contestant
		$contestant = UserFactory::createUser();

		// Create 2 runs, 100 and 50.
		$runDataOutsideContest = RunsFactory::createRunToProblem($problemData, $contestant);
		$runDataInsideContest = RunsFactory::createRun($problemData, $contestData, $contestant);
		RunsFactory::gradeRun($runDataOutsideContest);
		RunsFactory::gradeRun($runDataInsideContest, 0.5, "PA");

		// Call API
		$response = ProblemController::apiDetails(new Request(array(
			"auth_token" => $this->login($contestant),
			"problem_alias" => $problemData["request"]["alias"],
			"contest_alias" => $contestData["request"]["alias"]
		)));

		$this->assertEquals(50.00, $response["score"]);
	}
}

