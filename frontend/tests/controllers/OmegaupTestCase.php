<?php

/**
 * Parent class of all Test cases for Omegaup
 * Implements common methods for setUp and asserts
 *
 * @author joemmanuel
 */
class OmegaupTestCase extends PHPUnit_Framework_TestCase {
    public $mockClarificationController = null;
    private static $logObj = null;

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
     * tearDown function gets executed after each test (thanks to phpunit)
     */
    public function tearDown() {
        parent::tearDown();
        self::logout();
    }

    public static function logout() {
        $session = new SessionController();
        if ($session->CurrentSessionAvailable()) {
            $session->InvalidateCache();
        }
        if (isset($_COOKIE[OMEGAUP_AUTH_TOKEN_COOKIE_NAME])) {
            unset($_COOKIE[OMEGAUP_AUTH_TOKEN_COOKIE_NAME]);
        }
        if (isset($_REQUEST[OMEGAUP_AUTH_TOKEN_COOKIE_NAME])) {
            unset($_REQUEST[OMEGAUP_AUTH_TOKEN_COOKIE_NAME]);
        }
        $session->InvalidateLocalCache();
    }

    /**
     * Override session_start, phpunit doesn't like it, but we still validate that it is called once
     */
    public function mockSessionManager() {
        $sessionManagerMock =
            $this->getMockBuilder('SessionManager')->getMock();
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
                    'user_id' => $user->user_id
                ));
        $auth_tokens_bd = AuthTokensDAO::search($authTokenKey);

        // Validar que el token se guardó en la BDD
        if (!is_null($auth_token)) {
            $exists = false;
            foreach ($auth_tokens_bd as $token_db) {
                if (strcmp($token_db->token, $auth_token) === 0) {
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
     * @param Users $user the user to be logged in
     *
     * @return string auth_token
     */
    public static function login(Users $user) {
        UserController::$sendEmailOnVerify = false;

        // Deactivate cookie setting
        $oldCookieSetting = SessionController::$setCookieOnRegisterSession;
        SessionController::$setCookieOnRegisterSession = false;

        // Inflate request with user data
        $r = new Request(array(
            'usernameOrEmail' => $user->username,
            'password' => $user->password,
        ));

        // Call the API
        $response = UserController::apiLogin($r);

        // Sanity check
        self::assertEquals('ok', $response['status']);

        // Clean up leftovers of Login API
        unset($_REQUEST);

        // Set cookie setting as it was before the login
        SessionController::$setCookieOnRegisterSession = $oldCookieSetting;

        return new ScopedLoginToken($response['auth_token']);
    }

    /**
     * Assert that contest in the request actually exists in the DB
     *
     * @param Request $r
     */
    public function assertContest(Request $r) {
        // Validate that data was written to DB by getting the contest by title
        $contest = new Contests();
        $contest->title = $r['title'];
        $contests = ContestsDAO::search($contest);
        $contest = $contests[0];

        // Assert that we found our contest
        $this->assertNotNull($contest);
        $this->assertNotNull($contest->contest_id);

        // Assert data was correctly saved
        $this->assertEquals($r['description'], $contest->description);

        $this->assertGreaterThanOrEqual($r['start_time'] - 1, Utils::GetPhpUnixTimestamp($contest->start_time));
        $this->assertGreaterThanOrEqual($r['start_time'], Utils::GetPhpUnixTimestamp($contest->start_time) + 1);

        $this->assertGreaterThanOrEqual($r['finish_time'] - 1, Utils::GetPhpUnixTimestamp($contest->finish_time));
        $this->assertGreaterThanOrEqual($r['finish_time'], Utils::GetPhpUnixTimestamp($contest->finish_time) + 1);

        $this->assertEquals($r['window_length'], $contest->window_length);
        $this->assertEquals($r['public'], $contest->public);
        $this->assertEquals($r['alias'], $contest->alias);
        $this->assertEquals($r['points_decay_factor'], $contest->points_decay_factor);
        $this->assertEquals($r['partial_score'], $contest->partial_score);
        $this->assertEquals($r['submissions_gap'], $contest->submissions_gap);
        $this->assertEquals($r['feedback'], $contest->feedback);
        $this->assertEquals($r['penalty'], $contest->penalty);
        $this->assertEquals($r['scoreboard'], $contest->scoreboard);
        $this->assertEquals($r['penalty_type'], $contest->penalty_type);
        $this->assertEquals($r['penalty_calc_policy'], $contest->penalty_calc_policy);
        $this->assertEquals($r['recommended'], $contest->recommended);
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
     * Find a string into a keyed array. Should appear exactly once.
     *
     * @param array $array
     * @param string $key
     * @param string $needle
     */
    public function assertArrayContainsInKeyExactlyOnce($array, $key, $needle) {
        $count = 0;
        foreach ($array as $a) {
            if ($a[$key] === $needle) {
                $count++;
            }
        }
        if ($count == 0) {
            $this->fail("$needle not found in array");
        }
        if ($count > 1) {
            $this->fail("$needle found multiple times in array");
        }
    }

    /**
     * Asserts that string is not present in keyed array
     *
     * @param array $array
     * @param string $key
     * @param string $needle
     */
    public function assertArrayNotContainsInKey($array, $key, $needle) {
        foreach ($array as $a) {
            if ($a[$key] === $needle) {
                $this->fail("$needle found in array");
            }
        }
    }

    /**
     * Checks that two sets (given by char delimited strings) are equal.
     */
    public function assertEqualSets($expected, $actual, $delim = ',') {
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
        $fileUploaderMock = $this->getMockBuilder('FileUploader')->getMock();

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
        $graderMock = $this->getMockBuilder('Grader')->getMock();

        // Set expectations:
        $graderMock->expects($times)
                ->method('Grade')
                ->will($this->returnValue(true));

        // Detour all Grader::grade() calls to our mock
        RunController::$grader = $graderMock;
        ProblemController::$grader = $graderMock;
    }

    protected function detourBroadcasterCalls($times = null) {
        if (is_null($times)) {
            $times = $this->once();
        }

        $broadcasterMock = $this->getMockBuilder('Broadcaster')->getMock();
        $broadcasterMock->expects($times)
            ->method('broadcastClarification');
        ClarificationController::$broadcaster = $broadcasterMock;
    }

    /**
     * Log a message to STDERR
     *
     * @param string $message Message to log
     */
    public static function logToErr($message) {
        fwrite(STDERR, $message . "\n");
    }

    public static function log($message) {
        if (is_null(self::$logObj)) {
            self::$logObj = Logger::getLogger('tests');
        }

        self::$logObj->info('[INFO] ' . $message);
    }
}

/**
 * Simple RAII class that logs out as soon as it goes out of scope.
 */
class ScopedLoginToken {
    public $auth_token = null;

    public function __construct($auth_token) {
        $this->auth_token = $auth_token;
    }

    public function __destruct() {
        OmegaUpTestCase::logout();
    }

    // TODO: Delete this function. The existence of this allows for sessions to
    // be stored longer than intended since they will be added to Request
    // objects and then still maybe not cleaned up.
    public function __toString() {
        return $this->auth_token;
    }
}
