<?php

namespace OmegaUp\Test;

/**
 * Parent class of all controller test cases for omegaUp.
 * Implements common methods for setUp and asserts
 * @psalm-suppress PropertyNotSetInConstructor For some reason psalm is complaining about some phpunit statics.
 */
class ControllerTestCase extends \PHPUnit\Framework\TestCase {
    /** @var \Monolog\Logger|null */
    private static $logObj = null;

    /**
     * setUp function gets executed before each test (thanks to phpunit)
     */
    public function setUp(): void {
        parent::setUp();

        self::log("===== Start {$this->toString()}");
        \OmegaUp\Controllers\User::$sendEmailOnVerify = false;
        \OmegaUp\Controllers\Session::setCookieOnRegisterSessionForTesting(
            false
        );
        \OmegaUp\Controllers\Session::invalidateCache();
        \OmegaUp\Controllers\Session::invalidateLocalCache();

        // Mock time
        $currentTime = time();
        \OmegaUp\Time::setTimeForTesting($currentTime);
        \OmegaUp\MySQLConnection::getInstance()->Execute(
            "SET TIMESTAMP = {$currentTime};"
        );

        //Clean $_REQUEST before each test
        unset($_REQUEST);

        \OmegaUp\Test\Utils::cleanupProblemFiles();
        // Disable rate limiting by default in tests to avoid
        // interfering with existing tests that create many items.
        \OmegaUp\RateLimiter::setForTesting(false);
        \OmegaUp\MySQLConnection::getInstance()->StartTrans();
    }

    /**
     * tearDown function gets executed after each test (thanks to phpunit)
     */
    public function tearDown(): void {
        parent::tearDown();
        self::logout();

        \OmegaUp\MySQLConnection::getInstance()->FailTrans();
        \OmegaUp\MySQLConnection::getInstance()->CompleteTrans();
        \OmegaUp\Test\Utils::cleanupDBForTearDown();

        self::log("===== Stop {$this->toString()}");
    }

    public static function logout(): void {
        if (\OmegaUp\Controllers\Session::currentSessionAvailable()) {
            \OmegaUp\Controllers\Session::invalidateCache();
        }
        if (isset($_COOKIE[OMEGAUP_AUTH_TOKEN_COOKIE_NAME])) {
            unset($_COOKIE[OMEGAUP_AUTH_TOKEN_COOKIE_NAME]);
        }
        if (isset($_REQUEST[OMEGAUP_AUTH_TOKEN_COOKIE_NAME])) {
            unset($_REQUEST[OMEGAUP_AUTH_TOKEN_COOKIE_NAME]);
        }
        \OmegaUp\Controllers\Session::invalidateLocalCache();
    }

    /**
     * Override session_start, validating that it is called once.
     */
    public function mockSessionManager(): void {
        \OmegaUp\Controllers\Session::setSessionManagerForTesting(
            $this->getMockBuilder(\OmegaUp\SessionManager::class)->getMock()
        );
    }

    /**
     * Given an Identity, checks that login let state as supposed
     */
    public function assertLogin(
        \OmegaUp\DAO\VO\Identities $identity,
        string $authToken
    ): void {
        $authTokens = \OmegaUp\DAO\AuthTokens::getByIdentityId(
            intval($identity->identity_id)
        );

        $exists = false;
        foreach ($authTokens as $token) {
            if (strcmp(strval($token->token), $authToken) === 0) {
                $exists = true;
                break;
            }
        }

        $this->assertTrue($exists, "Token {$authToken} not in DB.");
    }

    /**
     * Logs in an identity and returns the auth_token
     */
    public static function login(
        \OmegaUp\DAO\VO\Identities $identity
    ): ScopedLoginToken {
        \OmegaUp\Controllers\User::$sendEmailOnVerify = false;

        // Deactivate cookie setting
        $oldCookieSetting = \OmegaUp\Controllers\Session::setCookieOnRegisterSessionForTesting(
            false
        );

        $response = \OmegaUp\Controllers\User::apiLogin(new \OmegaUp\Request([
            'usernameOrEmail' => $identity->username,
            'password' => $identity->password,
        ]));
        self::assertNotEmpty($response['auth_token']);

        // Clean up leftovers of Login API
        unset($_REQUEST);

        // Set cookie setting as it was before the login
        \OmegaUp\Controllers\Session::setCookieOnRegisterSessionForTesting(
            $oldCookieSetting
        );

        return new ScopedLoginToken($response['auth_token']);
    }

    /**
     * Assert that contest in the request actually exists in the DB
     *
     * @omegaup-request-param string $title
     *
     * @param \OmegaUp\Request $r
     */
    public function assertContest(\OmegaUp\Request $r): void {
        // Validate that data was written to DB by getting the contest by title
        $contests = \OmegaUp\DAO\Contests::getByTitle(
            $r->ensureString(
                'title'
            )
        );
        $this->assertNotEmpty($contests);
        $contest = $contests[0];

        // Assert that we found our contest
        $this->assertNotNull($contest->contest_id);

        // Assert data was correctly saved
        $this->assertSame($r['description'], $contest->description);

        $this->assertGreaterThanOrEqual(
            intval($r['start_time']) - 1,
            $contest->start_time->time
        );
        $this->assertGreaterThanOrEqual(
            intval($r['start_time']),
            $contest->start_time->time + 1
        );

        $this->assertGreaterThanOrEqual(
            intval($r['finish_time']) - 1,
            $contest->finish_time->time
        );
        $this->assertGreaterThanOrEqual(
            intval($r['finish_time']),
            $contest->finish_time->time + 1
        );

        $this->assertSame($r['window_length'], $contest->window_length);
        $this->assertSame($r['admission_mode'], $contest->admission_mode);
        $this->assertSame($r['alias'], $contest->alias);
        $this->assertSame(
            floatval($r['points_decay_factor']),
            $contest->points_decay_factor
        );
        $this->assertSame($r['score_mode'], $contest->score_mode);
        $this->assertSame(
            intval(
                $r['submissions_gap']
            ),
            $contest->submissions_gap
        );
        $this->assertSame($r['feedback'], $contest->feedback);
        $this->assertSame($r['penalty'], $contest->penalty);
        $this->assertSame($r['scoreboard'], $contest->scoreboard);
        $this->assertSame($r['penalty_type'], $contest->penalty_type);
        $this->assertSame(
            $r['penalty_calc_policy'],
            $contest->penalty_calc_policy
        );
        $this->assertSame(boolval($r['recommended']), $contest->recommended);
    }

    /**
     * Find a string into a keyed array
     *
     * @param list<array<string, string>> $array
     * @param string $key
     * @param string $needle
     */
    public function assertArrayContainsInKey($array, $key, $needle): void {
        foreach ($array as $a) {
            if ($a[$key] === $needle) {
                return;
            }
        }
        $this->fail("{$needle} not found in array");
    }

    /**
     * Find a string into a keyed array. Should appear exactly once.
     *
     * @param list<array<string, string>> $array
     * @param string $key
     * @param string $needle
     */
    public function assertArrayContainsInKeyExactlyOnce(
        $array,
        $key,
        $needle
    ): void {
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
     * @psalm-template T
     * @param list<T> $array
     * @param callable(T):bool $predicate
     */
    public function assertArrayContainsWithPredicate($array, $predicate): void {
        foreach ($array as $_ => $value) {
            if ($predicate($value)) {
                return;
            }
        }
        $this->fail('No elements in array satisfied predicate');
    }

    /**
     * Asserts that $array has exactly one element that matches $predicate.
     *
     * @psalm-template T
     * @param list<T> $array
     * @param callable(T):bool $predicate
     */
    public function assertArrayContainsWithPredicateExactlyOnce(
        $array,
        $predicate
    ): void {
        $count = 0;
        foreach ($array as $_ => $value) {
            if ($predicate($value)) {
                $count++;
            }
        }
        if ($count == 0) {
            $this->fail('No elements in array satisfied predicate');
        }
        if ($count > 1) {
            $this->fail('Multiple elements in array satisfied predicate');
        }
    }

    /**
     * Asserts that $array has no elements that matches $predicate.
     *
     * @psalm-template T
     * @param list<T> $array
     * @param callable(T):bool $predicate
     */
    public function assertArrayNotContainsWithPredicate(
        $array,
        $predicate
    ): void {
        foreach ($array as $_ => $value) {
            if ($predicate($value)) {
                $this->fail(
                    'At least one element in array satisfied predicate'
                );
            }
        }
    }

    /**
     * Finds the first element in $array that matches $predicate.
     *
     * @psalm-template T
     * @param list<T> $array
     * @param callable(T):bool $predicate
     * @return T|null
     */
    public function findByPredicate($array, $predicate) {
        foreach ($array as $_key => $value) {
            if ($predicate($value)) {
                return $value;
            }
        }
        return null;
    }

    /**
     * Asserts that string is not present in keyed array
     *
     * @param list<array<string, string>> $array
     * @param string $key
     * @param string $needle
     */
    public function assertArrayNotContainsInKey($array, $key, $needle): void {
        foreach ($array as $a) {
            if ($a[$key] === $needle) {
                $this->fail("{$needle} found in array");
            }
        }
    }

    /**
     * Checks that two sets (given by char delimited strings) are equal.
     */
    public function assertSameSets(
        string $expected,
        string $actual,
        string $delim = ','
    ): void {
        $expectedSet = explode($delim, $expected);
        sort($expectedSet);
        $actualSet = explode($delim, $actual);
        sort($actualSet);
        $this->assertEquals($expectedSet, $actualSet);
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
     *
     * @psalm-suppress InternalMethod It's fine to call PHPUnit mock internals.
     * That's they way they are declared in the docs.
     */
    public function createFileUploaderMock(): \OmegaUp\FileUploader {
        // Create fileUploader mock
        $fileUploaderMock = $this->getMockBuilder(\OmegaUp\FileUploader::class)
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
    public function isUploadedFile($filename): bool {
        return file_exists($filename);
    }

    /**
     * Redefinition of \OmegaUp\FileUploader::moveUploadedFile
     */
    public function moveUploadedFile(): bool {
        /** @var string */
        $filename = func_get_arg(0);
        /** @var string */
        $targetpath = func_get_arg(1);

        return copy($filename, $targetpath);
    }

    protected function detourBroadcasterCalls(
        ?\PHPUnit\Framework\MockObject\Rule\InvokedCount $times = null
    ): void {
        if (is_null($times)) {
            $times = $this->once();
        }

        $broadcasterMock = $this->getMockBuilder(\OmegaUp\Broadcaster::class)
                                ->getMock();
        /**
         * @psalm-suppress InternalMethod It's fine to call PHPUnit mock
         * internals.  That's they way they are declared in the docs.
         */
        $broadcasterMock
            ->expects($times)
            ->method('broadcastClarification');
        \OmegaUp\Controllers\Clarification::$broadcaster = $broadcasterMock;
    }

    public static function log(string $message): void {
        if (is_null(self::$logObj)) {
            self::$logObj = \Monolog\Registry::omegaup()->withName('tests');
        }

        self::$logObj->info($message);
    }
}

/**
 * Simple RAII class that logs out as soon as it goes out of scope.
 */
class ScopedLoginToken {
    /**
     * @var string|null
     */
    public $auth_token = null;

    public function __construct(string $authToken) {
        \OmegaUp\Authorization::clearCacheForTesting();
        $this->auth_token = $authToken;
    }

    public function __destruct() {
        \OmegaUp\Test\ControllerTestCase::logout();
        \OmegaUp\Authorization::clearCacheForTesting();
    }
}

/**
 * Simple RAII class to enable Test runs on Scoreboard
 */
class ScopedScoreboardTestRun {
    public function __construct() {
        \OmegaUp\Scoreboard::setIsTestRunForTesting(true);
    }

    public function __destruct() {
        \OmegaUp\Scoreboard::setIsTestRunForTesting(false);
    }
}

class FakeEmailSender implements \OmegaUp\EmailSender {
    /** @var array{email: string[], subject: string, body: string}[] */
    public $listEmails = [];

    /**
     * @param string[] $emails
     * @param string $subject
     * @param string $body
     */
    public function sendEmail(
        array $emails,
        string $subject,
        string $body
    ): void {
        $this->listEmails[] = [
            'email' => $emails,
            'subject' => $subject,
            'body' => $body,
        ];
    }
}

class ScopedEmailSender {
    public function __construct(\OmegaUp\EmailSender &$sender) {
        \OmegaUp\Email::setEmailSenderForTesting($sender);
    }

    public function __destruct() {
        \OmegaUp\Email::setEmailSenderForTesting(null);
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

    public function grade(\OmegaUp\DAO\VO\Runs $run, string $source): void {
        $sql = '
            SELECT
                s.guid
            FROM
                Submissions s
            WHERE
                s.submission_id = ?;
        ';
        /** @var string */
        $guid = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$run->submission_id]
        );
        $this->_submissions[$guid] = $source;
        array_push($this->_runs, $run);
    }

    /**
     * @param list<\OmegaUp\DAO\VO\Runs> $runs
     */
    public function rejudge(array $runs, bool $debug): void {
        $this->_runs += $runs;
    }

    public function getSource(string $guid): string {
        return $this->_submissions[$guid];
    }

    public function status(): array {
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
        ?int $problemsetId,
        ?string $problemAlias,
        string $message,
        bool $public,
        ?string $username,
        int $userId = -1,
        bool $userOnly = false
    ): void {
    }

    public function getGraderResource(
        \OmegaUp\DAO\VO\Runs $run,
        string $filename,
        bool $missingOk = false
    ): ?string {
        $path = "{$run->run_id}/{$filename}";
        if (!array_key_exists($path, $this->_resources)) {
            if (!$missingOk) {
                throw new \Exception("Resource {$path} not found");
            }
            return null;
        }

        return $this->_resources[$path];
    }

    public function getGraderResourcePassthru(
        \OmegaUp\DAO\VO\Runs $run,
        string $filename,
        bool $missingOk = false,
        array $fileHeaders = []
    ): ?bool {
        $path = "{$run->run_id}/{$filename}";
        if (!array_key_exists($path, $this->_resources)) {
            if (!$missingOk) {
                throw new \Exception("Resource {$path} not found");
            }
            return null;
        }

        $out = fopen('php://output', 'w');
        fputs($out, $this->_resources[$path]);
        fclose($out);
        return true;
    }

    public function setGraderResourceForTesting(
        \OmegaUp\DAO\VO\Runs $run,
        string $filename,
        string $contents
    ): void {
        $path = "{$run->run_id}/{$filename}";
        $this->_resources[$path] = $contents;
    }

    public function getRuns(): array {
        return $this->_runs;
    }
}

/**
 * Simple RAII class to detour grader calls to a mock instance.
 */
class ScopedGraderDetour {
    /** @var \OmegaUp\Grader */
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

    public function getGraderCallCount(): int {
        return count($this->_instance->getRuns());
    }

    public function getRuns(): array {
        return $this->_instance->getRuns();
    }
}
