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
		
		parent::setUp();
		UserController::$sendEmailOnVerify = false;
		SessionController::$setCookieOnRegisterSession = false;
				
		//Clean $_REQUEST before each test
		unset($_REQUEST);
	}
	
	/**
	 * Override session_start, phpunit doesn't like it, but we still validate that it is called once
	 */
	public function mockSessionManager() {
						
		$sessionManagerMock = $this->getMock('SessionManager', array('sessionStart'));
		$sessionManagerMock->expects($this->once())
				->method('sessionStart')
				->will($this->returnValue(''));		
		SessionController::$_sessionManager = $sessionManagerMock;
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
		$auth_tokens_bd = AuthTokensDAO::search($authTokenKey);


		// Validar que el token se guardó en la BDD		
		if (!is_null($auth_token)) {
			$exists = false;
			foreach ($auth_tokens_bd as $token_db) {
				if (strcmp($token_db->getToken(), $auth_token) === 0) {
					$exists = true;
					break;
				}
			}

			if ($exists === false) {
				$this->fail("Token $auth_token not in DB.");
			}
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

		UserController::$sendEmailOnVerify = false;
		
		// Deactivate cookie setting
		$oldCookieSetting = SessionController::$setCookieOnRegisterSession;
		SessionController::$setCookieOnRegisterSession = false;
		
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
		
		// Set cookie setting as it was before the login
		SessionController::$setCookieOnRegisterSession = $oldCookieSetting;
		
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
		
		$this->assertGreaterThanOrEqual($r["start_time"] - 1, Utils::GetPhpUnixTimestamp($contest->getStartTime()));
		$this->assertGreaterThanOrEqual($r["start_time"], Utils::GetPhpUnixTimestamp($contest->getStartTime()) + 1);
		
		$this->assertGreaterThanOrEqual($r["finish_time"] - 1, Utils::GetPhpUnixTimestamp($contest->getFinishTime()));
		$this->assertGreaterThanOrEqual($r["finish_time"], Utils::GetPhpUnixTimestamp($contest->getFinishTime()) + 1);
		
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
	
	
	/**
	 * Find a string into a keyed array
	 * 
	 * @param array $array
	 * @param string $key
	 * @param string $needle	 
	 */
	public function assertArrayContainsInKey($array, $key, $needle) {		
		foreach ($array as $a) {			
			if ($a[$key] === $needle) {
				return;
			} 						
		}
		$this->fail("$needle not found in array");
	}

	/**
	 * Checks that two sets (given by char delimited strings) are equal.
	 */
	public function assertEqualSets($expected, $actual, $delim = ",") {
		$expected_set = explode($delim, $expected);
		sort($expected_set);
		$actual_set = explode($delim, $actual);
		sort($actual_set);
		$this->assertEquals($expected_set, $actual_set);
	}

	/**
	 * Problem: PHPUnit does not support is_uploaded_file and move_uploaded_file
	 * native functions of PHP to move files around needed for store zip contents
	 * in the required places.
	 * 
	 * Solution: We abstracted those PHP native functions in an object FileUploader.
	 * We need to create a new FileUploader object that uses our own implementations.
	 * 
	 * Here we create a FileUploader and set our own implementations of is_uploaded_file 
	 * and move_uploaded_file. PHPUnit will intercept those calls and use our owns instead (mock). 
	 * Moreover, it will validate that they were actually called.
	 * 
	 * @return $fileUploaderMock
	 */
	public function createFileUploaderMock() {

		// Create fileUploader mock                        
		$fileUploaderMock = $this->getMock('FileUploader', array('IsUploadedFile', 'MoveUploadedFile'));

		// Detour IsUploadedFile function inside FileUploader to our own IsUploadedFile
		$fileUploaderMock->expects($this->any())
				->method('IsUploadedFile')
				->will($this->returnCallback(array($this, 'IsUploadedFile')));

		// Detour MoveUploadedFile function inside FileUploader to our own MoveUploadedFile
		$fileUploaderMock->expects($this->any())
				->method('MoveUploadedFile')
				->will($this->returnCallback(array($this, 'MoveUploadedFile')));

		return $fileUploaderMock;
	}

	/**
	 * Redefinition of IsUploadedFile
	 * 
	 * @param string $filename
	 * @return type
	 */
	public function IsUploadedFile($filename) {
		return file_exists($filename);
	}

	/**
	 * Redefinition of MoveUploadedFile
	 * 
	 * @return type
	 */
	public function MoveUploadedFile() {
		$filename = func_get_arg(0);
		$targetpath = func_get_arg(1);

		return copy($filename, $targetpath);
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
	public function detourGraderCalls($times = null) {

		if (is_null($times)) {
			$times = $this->once();
		}

		// Create a fake Grader object which will always return true (see
		// next line)
		$graderMock = $this->getMock('Grader', array('Grade'));

		// Set expectations: 
		$graderMock->expects($times)
				->method('Grade')
				->will($this->returnValue(true));

		// Detour all Grader::grade() calls to our mock
		RunController::$grader = $graderMock;
		ProblemController::$grader = $graderMock;
	}

	/**
	 * Log a message to STDERR
	 *
	 * @param string $message Message to log
	 */
	public static function log($message) {
		fwrite(STDERR, $message . "\n");
	}

}

