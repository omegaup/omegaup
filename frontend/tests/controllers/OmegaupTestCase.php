<?php

/**
 * Parent class of all Test cases for Omegaup
 * Implements common methods for setUp and asserts
 *
 * @author joemmanuel
 */
class OmegaupTestCase extends PHPUnit_Framework_TestCase {

	/**
	 * setUp function gets executed before each test (thanks to phpunit)
	 */
	public function setUp() {

		//Clean $_REQUEST before each test
		unset($_REQUEST);
	}

	/**
	 * Given an User, checks that login let state as supposed
	 * 
	 * @param Users $user
	 * @param type $auth_token
	 */
	public function assertLogin(Users $user, $auth_token = null) {

		// Check auth token
		$authTokenKey = new AuthTokens(array(
					"user_id" => $user->getUserId()
				));
		$auth_token_bd = AuthTokensDAO::search($authTokenKey);

		// Checar que tenemos exactamente 1 token vivo
		$this->assertEquals(1, count($auth_token_bd));

		// Validar que el token que esta en la DB es el mismo que tenemos por 
		// parametro
		if (!is_null($auth_token)) {
			$this->assertEquals($auth_token, $auth_token_bd[0]->getToken());
		}

		// @todo check last access time
	}

	/**
	 * Logs in a user an returns the auth_token
	 * 
	 * @param Users $user
	 * @return string auth_token
	 */
	public static function login(Users $user) {

		// Inflate request with user data
		$r = new Request(array(
					"usernameOrEmail" => $user->getUsername(),
					"password" => $user->getPassword()
				));

		// Call the API
		$response = UserController::apiLogin($r);

		// Sanity check
		self::assertEquals("ok", $response["status"]);

		// Clean up leftovers of Login API
		unset($_REQUEST);

		return $response["auth_token"];
	}

	/**
	 * Assert that contest in the request actually exists in the DB
	 * 
	 * @param Request $r
	 */
	public function assertContest(Request $r) {

		// Validate that data was written to DB by getting the contest by title
		$contest = new Contests();
		$contest->setTitle($r["title"]);
		$contests = ContestsDAO::search($contest);
		$contest = $contests[0];

		// Assert that we found our contest       
		$this->assertNotNull($contest);
		$this->assertNotNull($contest->getContestId());

		// Assert data was correctly saved
		$this->assertEquals($r["description"], $contest->getDescription());
		$this->assertEquals($r["start_time"], Utils::GetPhpUnixTimestamp($contest->getStartTime()));
		$this->assertEquals($r["finish_time"], Utils::GetPhpUnixTimestamp($contest->getFinishTime()));
		$this->assertEquals($r["window_length"], $contest->getWindowLength());
		$this->assertEquals($r["public"], $contest->getPublic());
		$this->assertEquals($r["alias"], $contest->getAlias());
		$this->assertEquals($r["points_decay_factor"], $contest->getPointsDecayFactor());
		$this->assertEquals($r["partial_score"], $contest->getPartialScore());
		$this->assertEquals($r["submissions_gap"], $contest->getSubmissionsGap());
		$this->assertEquals($r["feedback"], $contest->getFeedback());
		$this->assertEquals($r["penalty"], $contest->getPenalty());
		$this->assertEquals($r["scoreboard"], $contest->getScoreboard());
		$this->assertEquals($r["penalty_time_start"], $contest->getPenaltyTimeStart());
		$this->assertEquals($r["penalty_calc_policy"], $contest->getPenaltyCalcPolicy());
	}

}

