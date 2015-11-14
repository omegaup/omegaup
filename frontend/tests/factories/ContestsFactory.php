<?php

/**
 * ContestsFactory
 *
 * @author joemmanuel
 */

class ContestsFactory {

	/**
	 * Returns a Request object with complete context to create a contest
	 *
	 * @param string $title
	 * @param string $public
	 * @param Users $contestDirector
	 * @return Request
	 */
	public static function getRequest($title = null, $public = 0, Users $contestDirector = null, $languages = null) {

		if (is_null($contestDirector)) {
			$contestDirector = UserFactory::createUser();
		}

		if (is_null($title)) {
			$title = Utils::CreateRandomString();
		}

		// Set context
		$r = new Request();
		$r["title"] = $title;
		$r["description"] = "description";
		$r["start_time"] = Utils::GetPhpUnixTimestamp() - 60 * 60;
		$r["finish_time"] = Utils::GetPhpUnixTimestamp() + 60 * 60;
		$r["window_length"] = null;
		$r["public"] = $public;
		$r["alias"] = substr($title, 0, 20);
		$r["points_decay_factor"] = ".02";
		$r["partial_score"] = "0";
		$r["submissions_gap"] = "0";
		$r["feedback"] = "yes";
		$r["penalty"] = 100;
		$r["scoreboard"] = 100;
		$r["penalty_type"] = "contest_start";
		$r["penalty_calc_policy"] = "sum";
		$r['languages'] = $languages;

		return array(
			"request" => $r,
			"director" => $contestDirector);
	}

	public static function createContest($title = null, $public = 1, Users $contestDirector = null, $languages = null) {

		// Create a valid contest Request object
		$contestData = ContestsFactory::getRequest($title, 0, $contestDirector, $languages);
		$r = $contestData["request"];
		$contestDirector = $contestData["director"];

		// Log in the user and set the auth token in the new request
		$r["auth_token"] = OmegaupTestCase::login($contestDirector);

		// Call the API
		$response = ContestController::apiCreate($r);

		if ($public === 1) {
			self::forcePublic($contestData);
			$r["public"] = 1;
		}

		$contest = ContestsDAO::getByAlias($r["alias"]);

		return array(
			"director" => $contestData["director"],
			"request" => $r,
			"contest" => $contest
		);
	}

	public static function addProblemToContest($problemData, $contestData) {

		// Create an empty request
		$r = new Request();

		// Log in as contest director
		$r["auth_token"] = OmegaupTestCase::login($contestData["director"]);

		// Build request
		$r["contest_alias"] = $contestData["request"]["alias"];
		$r["problem_alias"] = $problemData["request"]["alias"];
		$r["points"] = 100;
		$r["order_in_contest"] = 1;

		// Call API
		$response = ContestController::apiAddProblem($r);

		// Clean up
		unset($_REQUEST);
	}

	public static function openContest($contestData, $user) {
		// Create an empty request
		$r = new Request();

		// Log in as contest director
		$r["auth_token"] = OmegaupTestCase::login($user);

		// Prepare our request
		$r["contest_alias"] = $contestData["request"]["alias"];

		// Call api
		ContestController::apiOpen($r);

		unset($_REQUEST);
	}

	public static function openProblemInContest($contestData, $problemData, $user) {

		// Prepare our request
		$r = new Request();
		$r["contest_alias"] = $contestData["request"]["alias"];
		$r["problem_alias"] = $problemData["request"]["alias"];

		// Log in the user
		$r["auth_token"] = OmegaupTestCase::login($user);

		// Call api
		ProblemController::apiDetails($r);

		unset($_REQUEST);
	}

	public static function addUser($contestData, $user) {

		// Prepare our request
		$r = new Request();
		$r["contest_alias"] = $contestData["request"]["alias"];
		$r["usernameOrEmail"] = $user->getUsername();

		// Log in the contest director
		$r["auth_token"] = OmegaupTestCase::login($contestData["director"]);

		// Call api
		ContestController::apiAddUser($r);

		unset($_REQUEST);
	}

	public static function addAdminUser($contestData, $user) {

		// Prepare our request
		$r = new Request();
		$r["contest_alias"] = $contestData["request"]["alias"];
		$r["usernameOrEmail"] = $user->getUsername();

		// Log in the contest director
		$r["auth_token"] = OmegaupTestCase::login($contestData["director"]);

		// Call api
		ContestController::apiAddAdmin($r);

		unset($_REQUEST);
	}

	public static function makeContestWindowLength($contestData, $windowLength = 20) {

		$contest = ContestsDAO::getByAlias($contestData["request"]["alias"]);
        $contest->setWindowLength($windowLength);
        ContestsDAO::save($contest);
	}

	public static function forcePublic($contestData) {
		$contest = ContestsDAO::getByAlias($contestData["request"]["alias"]);
        $contest->setPublic(1);
        ContestsDAO::save($contest);
	}

	public static function setScoreboardPercentage($contestData, $percentage) {
		$contest = ContestsDAO::getByAlias($contestData["request"]["alias"]);
		$contest->setScoreboard($percentage);
		ContestsDAO::save($contest);
	}

}

