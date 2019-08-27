<?php

/**
 * Parent class of all Test cases for Omegaup
 * Implements common methods for setUp and asserts
 *
 * @author joemmanuel
 */
class OmegaupTestCase extends \PHPUnit\Framework\TestCase {
    public $mockClarificationController = null;
    private static $logObj = null;

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();

        $scriptFilename = __DIR__ . '/gitserver-start.sh ' .
            OMEGAUP_GITSERVER_PORT . ' /tmp/omegaup/problems.git';
        exec($scriptFilename, $output, $returnVar);
        if ($returnVar != 0) {
            throw new Exception(
                "{$scriptFilename} failed with {$returnVar}:\n" .
                implode("\n", $output)
            );
        }
    }

    public static function tearDownAfterClass() {
        parent::tearDownAfterClass();
        $scriptFilename = __DIR__ . '/gitserver-stop.sh';
        exec($scriptFilename, $output, $returnVar);
        if ($returnVar != 0) {
            throw new Exception(
                "{$scriptFilename} failed with {$returnVar}:\n" .
                implode("\n", $output)
            );
        }
    }

    /**
     * setUp function gets executed before each test (thanks to phpunit)
     */
    public function setUp() {
        parent::setUp();
        UserController::$sendEmailOnVerify = false;
        SessionController::setCookieOnRegisterSessionForTesting(false);

        // Mock time
        $currentTime = time();
        \OmegaUp\Time::setTimeForTesting($currentTime);
        \OmegaUp\MySQLConnection::getInstance()->Execute("SET TIMESTAMP = {$currentTime};");

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
        if ($session->currentSessionAvailable()) {
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
     * Override session_start, validating that it is called once.
     */
    public function mockSessionManager() : void {
        $sessionManagerMock =
            $this->getMockBuilder('\\OmegaUp\\SessionManager')->getMock();
        $sessionManagerMock->expects($this->once())
                ->method('sessionStart');
        SessionController::setSessionManagerForTesting($sessionManagerMock);
    }

    /**
     * Given an Identity, checks that login let state as supposed
     *
     * @param \OmegaUp\DAO\VO\Identities $identity
     * @param type $auth_token
     */
    public function assertLogin(\OmegaUp\DAO\VO\Identities $identity, $auth_token = null) {
        // Check auth token
        $auth_tokens_bd = AuthTokensDAO::getByIdentityId($identity->identity_id);

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
     * Logs in an identity and returns the auth_token
     *
     * @param $identity to be logged in
     *
     * @return string auth_token
     */
    public static function login($identity) : ScopedLoginToken {
        UserController::$sendEmailOnVerify = false;

        // Deactivate cookie setting
        $oldCookieSetting = SessionController::setCookieOnRegisterSessionForTesting(false);

        // Inflate request with identity data
        $r = new \OmegaUp\Request([
            'usernameOrEmail' => $identity->username,
            'password' => $identity->password,
        ]);

        // Call the API
        $response = UserController::apiLogin($r);

        // Sanity check
        self::assertEquals('ok', $response['status']);

        // Clean up leftovers of Login API
        unset($_REQUEST);

        // Set cookie setting as it was before the login
        SessionController::setCookieOnRegisterSessionForTesting($oldCookieSetting);

        return new ScopedLoginToken($response['auth_token']);
    }

    /**
     * Assert that contest in the request actually exists in the DB
     *
     * @param \OmegaUp\Request $r
     */
    public function assertContest(\OmegaUp\Request $r) {
        // Validate that data was written to DB by getting the contest by title
        $contests = ContestsDAO::getByTitle($r['title']);
        $contest = $contests[0];

        // Assert that we found our contest
        $this->assertNotNull($contest);
        $this->assertNotNull($contest->contest_id);

        // Assert data was correctly saved
        $this->assertEquals($r['description'], $contest->description);

        $this->assertGreaterThanOrEqual($r['start_time'] - 1, $contest->start_time);
        $this->assertGreaterThanOrEqual($r['start_time'], $contest->start_time + 1);

        $this->assertGreaterThanOrEqual($r['finish_time'] - 1, $contest->finish_time);
        $this->assertGreaterThanOrEqual($r['finish_time'], $contest->finish_time + 1);

        $this->assertEquals($r['window_length'], $contest->window_length);
        $this->assertEquals($r['admission_mode'], $contest->admission_mode);
        $this->assertEquals($r['alias'], $contest->alias);
        $this->assertEquals($r['points_decay_factor'], $contest->points_decay_factor);
        $this->assertEquals($r['partial_score'] == '1', $contest->partial_score);
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
     * Asserts that $array has at least one element that matches $predicate.
     *
     * @param array $array
     * @param callable $predicate
     */
    public function assertArrayContainsWithPredicate($array, $predicate) {
        foreach ($array as $key => $value) {
            if ($predicate($value)) {
                return;
            }
        }
        $this->fail('No elements in array satisfied predicate');
    }

    /**
     * Asserts that $array has no elements that matches $predicate.
     *
     * @param array $array
     * @param callable $predicate
     */
    public function assertArrayNotContainsWithPredicate($array, $predicate) {
        foreach ($array as $key => $value) {
            if ($predicate($value)) {
                $this->fail('At least one element in array satisfied predicate');
            }
        }
    }

    /**
     * Finds the first element in $array that matches $predicate.
     *
     * @param array $array
     * @param callable $predicate
     */
    public function findByPredicate($array, $predicate) {
        foreach ($array as $key => $value) {
            if ($predicate($value)) {
                return $value;
            }
        }
        return null;
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
     * Solution: We abstracted those PHP native functions in an object
     * \OmegaUp\FileUploader.  We need to create a new \OmegaUp\FileUploader
     * object that uses our own implementations.
     *
     * Here we create a \OmegaUp\FileUploader and set our own implementations
     * of is_uploaded_file and move_uploaded_file. PHPUnit will intercept those
     * calls and use our owns instead (mock).  Moreover, it will validate that
     * they were actually called.
     */
    public function createFileUploaderMock() : \OmegaUp\FileUploader {
        // Create fileUploader mock
        $fileUploaderMock = $this->getMockBuilder('\\OmegaUp\\FileUploader')
                ->getMock();

        // Detour isUploadedFile function inside \OmegaUp\FileUploader to our
        // own isUploadedFile
        $fileUploaderMock->expects($this->any())
                ->method('isUploadedFile')
                ->will($this->returnCallback([$this, 'isUploadedFile']));

        // Detour moveUploadedFile function inside \OmegaUp\FileUploader to our
        // own moveUploadedFile
        $fileUploaderMock->expects($this->any())
                ->method('moveUploadedFile')
                ->will($this->returnCallback([$this, 'moveUploadedFile']));

        return $fileUploaderMock;
    }

    /**
     * Redefinition of \OmegaUp\FileUploader::isUploadedFile
     *
     * @param string $filename
     */
    public function isUploadedFile($filename) : bool {
        return file_exists($filename);
    }

    /**
     * Redefinition of \OmegaUp\FileUploader::moveUploadedFile
     */
    public function moveUploadedFile() : bool {
        $filename = func_get_arg(0);
        $targetpath = func_get_arg(1);

        return copy($filename, $targetpath);
    }

    protected function detourBroadcasterCalls($times = null) {
        if (is_null($times)) {
            $times = $this->once();
        }

        $broadcasterMock = $this->getMockBuilder('Broadcaster')->getMock();
        $broadcasterMock
            ->expects($times)
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
        \OmegaUp\Authorization::clearCacheForTesting();
        $this->auth_token = $auth_token;
    }

    public function __destruct() {
        OmegaUpTestCase::logout();
        \OmegaUp\Authorization::clearCacheForTesting();
    }
}

/**
 * Simple RAII class to enable Test runs on Scoreboard
 */
class ScopedScoreboardTestRun {
    public function __construct() {
        Scoreboard::setIsTestRunForTesting(true);
    }

    public function __destruct() {
        Scoreboard::setIsTestRunForTesting(false);
    }
}

class ScopedEmailSender implements \OmegaUp\EmailSender {
    /** @var array{email: string[], subject: string, body: string}[] */
    public static $listEmails = [];

    public function __construct() {
        \OmegaUp\Email::setEmailSenderForTesting($this);
    }

    public function __destruct() {
        \OmegaUp\Email::setEmailSenderForTesting(null);
    }

    /**
     * @param string[] $emails
     * @param string $subject
     * @param string $body
     */
    public function sendEmail(array $emails, string $subject, string $body) : void {
        self::$listEmails[] = [
            'email' => $emails,
            'subject' => $subject,
            'body' => $body,
        ];
    }
}

/**
 * No-op version of the Grader.
 *
 * We are not testing the Grader functionallity itself, we are only validating
 * that we populate the DB correctly and that we make a call to the function
 * \OmegaUp\Grader::grade(), without executing the contents.
 */
class NoOpGrader extends \OmegaUp\Grader {
    /** @var array<string, string> */
    private $_resources = [];

    /** @var array<string, string> */
    private $_submissions = [];

    /** @var \OmegaUp\DAO\VO\Runs[] */
    private $_runs = [];

    public function grade(\OmegaUp\DAO\VO\Runs $run, string $source) : void {
        $sql = '
            SELECT
                s.guid
            FROM
                Submissions s
            WHERE
                s.submission_id = ?;
        ';
        /** @var string */
        $guid = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, [$run->submission_id]);
        $this->_submissions[$guid] = $source;
        array_push($this->_runs, $run);
    }

    public function rejudge(array $runs, bool $debug) : void {
        $this->_runs += $runs;
    }

    public function getSource(string $guid) : string {
        return $this->_submissions[$guid];
    }

    public function status() : array {
        return [
            'status' => 'ok',
            'broadcaster_sockets' => 0,
            'embedded_runner' => false,
            'queue' => [
                'running' => [],
                'run_queue_length' => 0,
                'runner_queue_length' => 0,
                'runners' => []
            ],
        ];
    }

    public function broadcast(
        ?string $contestAlias,
        ?string $problemsetId,
        ?string $problemAlias,
        string $message,
        bool $public,
        ?string $username,
        int $userId = -1,
        bool $userOnly = false
    ) : void {
    }

    public function getGraderResource(
        \OmegaUp\DAO\VO\Runs $run,
        string $filename,
        bool $missingOk = false
    ) : ?string {
        $path = "{$run->run_id}/{$filename}";
        if (!array_key_exists($path, $this->_resources)) {
            if (!$missingOk) {
                throw new Exception("Resource {$path} not found");
            }
            return null;
        }

        return $this->_resources[$path];
    }

    public function getGraderResourcePassthru(
        \OmegaUp\DAO\VO\Runs $run,
        string $filename,
        bool $missingOk = false
    ) : ?bool {
        throw new \OmegaUp\Exceptions\UnimplementedException();
    }

    public function setGraderResourceForTesting(
        \OmegaUp\DAO\VO\Runs $run,
        string $filename,
        string $contents
    ) : void {
        $path = "{$run->run_id}/{$filename}";
        $this->_resources[$path] = $contents;
    }

    public function getRuns() : array {
        return $this->_runs;
    }
}

/**
 * Simple RAII class to detour grader calls to a mock instance.
 */
class ScopedGraderDetour {
    /** @var OmegaUp\Grader */
    private $_originalInstance;

    /** @var NoOpGrader */
    private $_instance;

    public function __construct() {
        $this->_originalInstance = \OmegaUp\Grader::getInstance();
        $this->_instance = new NoOpGrader();
        \OmegaUp\Grader::setInstanceForTesting($this->_instance);
    }

    public function __destruct() {
        \OmegaUp\Grader::setInstanceForTesting($this->_originalInstance);
    }

    public function getGraderCallCount() : int {
        return count($this->_instance->getRuns());
    }

    public function getRuns() : array {
        return $this->_instance->getRuns();
    }
}
