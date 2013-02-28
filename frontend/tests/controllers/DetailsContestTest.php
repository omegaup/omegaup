<?php

/**
 * Description of DetailsContest
 *
 * @author joemmanuel
 */
require_once 'ProblemsFactory.php';
require_once 'ContestsFactory.php';
require_once 'UserFactory.php';

class DetailsContest extends OmegaupTestCase {

	public function testGetContestDetailsValid() {

		// Get some problems
		$numOfProblems = 3;
		$problems = array();
		for ($i = 0; $i < $numOfProblems; $i++) {
			$problems[$i] = ProblemsFactory::createProblem();
		}

		// Get a contest 
		$contestData = ContestsFactory::createContest();

		// Add contests to problem
		foreach ($problems as $p) {
			ContestsFactory::addProblemToContest($p, $contestData);
		}

		// Get a user for our scenario
		$contestant = UserFactory::createUser();

		// Prepare our request
		$r = new Request();
		$r["contest_alias"] = $contestData["request"]["alias"];

		// Log in the user
		$r["auth_token"] = $this->login($contestant);

		// Call api
		$response = ContestController::apiDetails($r);

		// To validate, grab the contest object directly from the DB
		$contest = ContestsDAO::getByAlias($contestData["request"]["alias"]);

		// Assert we are getting correct data
		$this->assertEquals($contest->getDescription(), $response["description"]);
		$this->assertEquals(Utils::GetPhpUnixTimestamp($contest->getStartTime()), $response["start_time"]);
		$this->assertEquals(Utils::GetPhpUnixTimestamp($contest->getFinishTime()), $response["finish_time"]);
		$this->assertEquals($contest->getWindowLength(), $response["window_length"]);
		$this->assertEquals($contest->getAlias(), $response["alias"]);
		$this->assertEquals($contest->getPointsDecayFactor(), $response["points_decay_factor"]);
		$this->assertEquals($contest->getPartialScore(), $response["partial_score"]);
		$this->assertEquals($contest->getSubmissionsGap(), $response["submissions_gap"]);
		$this->assertEquals($contest->getFeedback(), $response["feedback"]);
		$this->assertEquals($contest->getPenalty(), $response["penalty"]);
		$this->assertEquals($contest->getScoreboard(), $response["scoreboard"]);
		$this->assertEquals($contest->getPenaltyTimeStart(), $response["penalty_time_start"]);
		$this->assertEquals($contest->getPenaltyCalcPolicy(), $response["penalty_calc_policy"]);

		// Assert we have our problems
		$this->assertEquals($numOfProblems, count($response["problems"]));

		// Assert problem data
		$i = 0;
		foreach ($response["problems"] as $problem_array) {
			// Get problem from DB            
			$problem = ProblemsDAO::getByAlias($problems[$i]["request"]["alias"]);

			// Assert data in DB
			$this->assertEquals($problem->getTitle(), $problem_array["title"]);
			$this->assertEquals($problem->getAlias(), $problem_array["alias"]);
			$this->assertEquals($problem->getValidator(), $problem_array["validator"]);
			$this->assertEquals($problem->getTimeLimit(), $problem_array["time_limit"]);
			$this->assertEquals($problem->getMemoryLimit(), $problem_array["memory_limit"]);
			$this->assertEquals($problem->getVisits(), $problem_array["visits"]);
			$this->assertEquals($problem->getSubmissions(), $problem_array["submissions"]);
			$this->assertEquals($problem->getAccepted(), $problem_array["accepted"]);
			$this->assertEquals($problem->getOrder(), $problem_array["order"]);

			// Get points of problem from Contest-Problem relationship
			$problemInContest = ContestProblemsDAO::getByPK($contest->getContestId(), $problem->getProblemId());
			$this->assertEquals($problemInContest->getPoints(), $problem_array["points"]);

			$i++;
		}
	}

}

