<?php
/**
 * Description of ContestUpdateContest
 */
class ContestUpdateTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Check in DB for problem added to or updated in contest
     *
     * @param string $problemData
     * @param string $contestData
     * @param float $points
     * @param int $orderInContest
     */
    public static function assertProblemAddedToOrUpdatedInContest(
        string $problemAlias,
        string $contestAlias,
        float $points,
        int $orderInContest
    ) {
        // Get problem and contest from DB
        $problem = \OmegaUp\DAO\Problems::getByAlias(
            $problemAlias
        );
        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestAlias
        );

        // Get problem-contest and verify it
        $problemsetProblems = \OmegaUp\DAO\ProblemsetProblems::getByPK(
            $contest->problemset_id,
            $problem->problem_id
        );
        self::assertNotNull($problemsetProblems);
        self::assertSame($points, $problemsetProblems->points);
        self::assertSame($orderInContest, $problemsetProblems->order);
    }

    /**
     * Only update the contest title. Rest should stay the same
     */
    public function testUpdateContestTitle() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Update title.
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'title' => \OmegaUp\Test\Utils::createRandomString(),
            'languages' => 'c11-gcc',
        ]);

        // Call API
        \OmegaUp\Controllers\Contest::apiUpdate($r);

        // To validate, we update the title to the original request and send
        // the entire original request to assertContest. Any other parameter
        // should not be modified by Update api
        $contestData['request']['title'] = $r['title'];
        $this->assertContest($contestData['request']);
    }

    public function testUpdateContestNonDirector() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        // Update title
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'title' => \OmegaUp\Test\Utils::createRandomString(),
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Update from private to public. Should fail if no problems in contest
     */
    public function testUpdatePrivateContestToPublicWithoutProblems() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );

        // Update public
        $login = self::login($contestData['director']);

        try {
            \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'admission_mode' => 'public',
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame(
                'contestPublicRequiresProblem',
                $e->getMessage()
            );
        }
    }

    /**
     * Update from private to public.
     * And choosing default_show_all_contestants_in_scoreboard option
     */
    public function testUpdateDefaultShowAllContestantsOption() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );

        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Update to public
        $login = self::login($contestData['director']);

        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );
        $this->assertFalse(
            $contest->default_show_all_contestants_in_scoreboard
        );

        \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'admission_mode' => 'public',
            'default_show_all_contestants_in_scoreboard' => true,
        ]));

        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );
        $this->assertTrue(
            $contest->default_show_all_contestants_in_scoreboard
        );
    }

    /**
     * Update when there are too many problems in the contest.
     */
    public function testUpdateWhenTooManyProblemsInContest() {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        $login = self::login($contestData['director']);

        for ($i = 0; $i < MAX_PROBLEMS_IN_CONTEST - 1; $i++) {
            $problemData = \OmegaUp\Test\Factories\Problem::createProblemWithAuthor(
                $contestData['director'],
                $login
            );

            $response = \OmegaUp\Controllers\Contest::apiAddProblem(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['contest']->alias,
                'problem_alias' => $problemData['request']['problem_alias'],
                'points' => 100,
                'order_in_contest' => $i + 1,
            ]));
            $this->assertSame('ok', $response['status']);
            self::assertProblemAddedToOrUpdatedInContest(
                $problemData['request']['problem_alias'],
                $contestData['contest']->alias,
                100,
                $i + 1
            );
        }

        // Add the last allowed problem to the contest.
        $lastProblemData = \OmegaUp\Test\Factories\Problem::createProblemWithAuthor(
            $contestData['director'],
            $login
        );

        $lastProblemResponse = \OmegaUp\Controllers\Contest::apiAddProblem(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['contest']->alias,
            'problem_alias' => $lastProblemData['request']['problem_alias'],
            'points' => 100,
            'order_in_contest' => MAX_PROBLEMS_IN_CONTEST,
        ]));
        $this->assertSame('ok', $lastProblemResponse['status']);
        self::assertProblemAddedToOrUpdatedInContest(
            $lastProblemData['request']['problem_alias'],
            $contestData['contest']->alias,
            100,
            $i + 1
        );

        // Try to insert one more problem than is allowed, and it should fail this time.
        $problemData = \OmegaUp\Test\Factories\Problem::createProblemWithAuthor(
            $contestData['director'],
            $login
        );
        try {
            $response = \OmegaUp\Controllers\Contest::apiAddProblem(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['contest']->alias,
                'problem_alias' => $problemData['request']['problem_alias'],
                'points' => 100,
                'order_in_contest' => MAX_PROBLEMS_IN_CONTEST + 1,
            ]));
            $this->fail('Should have failed adding the problem to the contest');
        } catch (\OmegaUp\Exceptions\ApiException $e) {
            $this->assertSame(
                $e->getMessage(),
                'contestAddproblemTooManyProblems'
            );
        }

        // Update a problem that's already in the contest, it will work again.
        $lastProblemUpdateResponse = \OmegaUp\Controllers\Contest::apiAddProblem(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['contest']->alias,
                'problem_alias' => $lastProblemData['request']['problem_alias'],
                'points' => 50,
                'order_in_contest' => MAX_PROBLEMS_IN_CONTEST,
            ])
        );
        $this->assertSame('ok', $lastProblemUpdateResponse['status']);
        self::assertProblemAddedToOrUpdatedInContest(
            $lastProblemData['request']['problem_alias'],
            $contestData['contest']->alias,
            50,
            $i + 1
        );
    }

    /**
     * Update from private to public with problems added
     */
    public function testUpdatePrivateContestToPublicWithProblems() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );

        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Update public
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'admission_mode' => 'public',
        ]);

        // Call API
        \OmegaUp\Controllers\Contest::apiUpdate($r);

        $contestData['request']['admission_mode'] = $r['admission_mode'];
        $this->assertContest($contestData['request']);
    }

     /**
      * Set Recommended flag to a given contest
      */
    public function testSetRecommendedFlag() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Update value
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser();
        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'value' => 1,
        ]);

        // Call API
        \OmegaUp\Controllers\Contest::apiSetRecommended($r);

        // Verify setting
        $contestData['request']['recommended'] = $r['value'];
        $this->assertContest($contestData['request']);

        // Turn flag down
        $r['value'] = 0;

        // Call API again
        \OmegaUp\Controllers\Contest::apiSetRecommended($r);

        // Verify setting
        $contestData['request']['recommended'] = $r['value'];
        $this->assertContest($contestData['request']);
    }

     /**
      * Set Recommended flag to a given contest from non admin account
      */
    public function testSetRecommendedFlagNonAdmin() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Update value
        $login = self::login($contestData['director']);

        try {
            \OmegaUp\Controllers\Contest::apiSetRecommended(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'value' => 1,
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Contest length can't be too long
     */
    public function testUpdateContestLengthTooLong() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Update length
        $login = self::login($contestData['director']);

        try {
            \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'start_time' => 0,
                'finish_time' => 60 * 60 * 24 * 32,
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('contestLengthTooLong', $e->getMessage());
            $this->assertStringContainsString('31', $e->getErrorMessage());
        }
    }

    /**
     * Sys-admin contest admins can extend contests up to 60 days
     */
    public function testUpdateContestLengthAsSysAdmin() {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createAdminUser();
        \OmegaUp\Test\Factories\Contest::addAdminUser($contestData, $admin);

        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );
        $startTime = \OmegaUp\DAO\DAO::fromMySQLTimestamp(
            $contest->start_time
        )->time;
        $newFinishTime = $startTime + (60 * 60 * 24 * 60);

        $login = self::login($admin);
        $response = \OmegaUp\Controllers\Contest::apiUpdate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'finish_time' => $newFinishTime,
            ])
        );
        $this->assertSame('ok', $response['status']);
    }

    /**
     * Sys-admin contest admins cannot exceed 60 days
     */
    public function testUpdateContestTooLongAsSysAdmin() {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createAdminUser();
        \OmegaUp\Test\Factories\Contest::addAdminUser($contestData, $admin);

        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );
        $startTime = \OmegaUp\DAO\DAO::fromMySQLTimestamp(
            $contest->start_time
        )->time;
        $newFinishTime = $startTime + (60 * 60 * 24 * 61);

        $login = self::login($admin);
        try {
            \OmegaUp\Controllers\Contest::apiUpdate(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'contest_alias' => $contestData['request']['alias'],
                    'finish_time' => $newFinishTime,
                ])
            );
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('contestLengthTooLong', $e->getMessage());
            $this->assertStringContainsString('60', $e->getErrorMessage());
        }
    }

    /**
     * Submit a run into a contest helper
     *
     */
    private function createRunInContest($contestData) {
        // STEP 1: Create a problem and add it to the contest
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // STEP 2: Get contestant ready to create a run
        // Create our contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Our contestant has to open the contest before sending a run
        \OmegaUp\Test\Factories\Contest::openContest(
            $contestData['contest'],
            $identity
        );

        // Then we need to open the problem
        \OmegaUp\Test\Factories\Contest::openProblemInContest(
            $contestData,
            $problemData,
            $identity
        );

        // STEP 3: Send a new run
        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
            'language' => 'c11-gcc',
            'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
        ]);

        \OmegaUp\Controllers\Run::apiCreate($r);
    }

    /**
     * Contest start can't be updated if already contains runs
     */
    public function testUpdateContestStartWithRuns() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Submit a run
        $this->createRunInContest($contestData);

        // Update length
        $login = self::login($contestData['director']);

        try {
            \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'start_time' => $contestData['request']['start_time'] + 1,
                'languages' => 'c11-gcc',
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame(
                'contestUpdateAlreadyHasRuns',
                $e->getMessage()
            );
        }
    }

    /**
     * Contest length can be updated if no runs
     *
     */
    public function testUpdateContestStartNoRuns() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Update length
        $login = self::login($contestData['director']);
        $contestData['request']['start_time'] += 1;
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'start_time' => $contestData['request']['start_time'],
            'languages' => 'c11-gcc',
        ]);

        // Call API
        \OmegaUp\Controllers\Contest::apiUpdate($r);

        // Check contest data from DB
        $this->assertContest($contestData['request']);
    }

    /**
     * Contest title can be updated if already contains runs and start time does not change
     *
     */
    public function testUpdateContestTitleWithRuns() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Submit a run
        $this->createRunInContest($contestData);

        // Update title
        $login = self::login($contestData['director']);
        $contestData['request']['title'] = 'New title';
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'start_time' => $contestData['request']['start_time'],
            'title' => $contestData['request']['title'],
            'languages' => 'c11-gcc',
        ]);

        // Call API
        \OmegaUp\Controllers\Contest::apiUpdate($r);

        // Check contest data from DB
        $this->assertContest($contestData['request']);
    }

    /**
     * Contestant submits runs and admin edits the penalty type of an
     * active contest
     */
    public function testUpdatePenaltyTypeFromAContest() {
        $originalTime = new \OmegaUp\Timestamp(\OmegaUp\Time::get());

        $penaltyType = 'contest_start';

        // Create a contest with one problem.
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'penaltyType' => $penaltyType,
                'startTime' => $originalTime,
                'lastUpdated' => $originalTime,
                'finishTime' => new \OmegaUp\Timestamp(
                    $originalTime->time + 60 * 60
                ),
            ])
        );
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create a run
        {
            \OmegaUp\Time::setTimeForTesting($originalTime->time + 5 * 60);
            ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
            \OmegaUp\Test\Factories\Contest::addUser($contestData, $identity);

            // The problem is opened 5 minutes after contest starts.
            \OmegaUp\Test\Factories\Contest::openContest(
                $contestData['contest'],
                $identity
            );
            \OmegaUp\Test\Factories\Contest::openProblemInContest(
                $contestData,
                $problemData,
                $identity
            );

            // The run is sent 10 minutes after contest starts.
            \OmegaUp\Time::setTimeForTesting($originalTime->time + 10 * 60);
            $runData = \OmegaUp\Test\Factories\Run::createRun(
                $problemData,
                $contestData,
                $identity
            );
            \OmegaUp\Test\Factories\Run::gradeRun($runData, 1.0, 'AC', 10);
            \OmegaUp\Time::setTimeForTesting($originalTime->time);
        }

        $directorLogin = self::login($contestData['director']);

        // Get the original penalty, since the contest was created with the
        // 'contest_start' penalty type.
        $response = \OmegaUp\Controllers\Contest::apiRuns(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
        ]));
        $this->assertSame(100.0, $response['runs'][0]['contest_score']);
        $originalPenalty = $response['runs'][0]['penalty'];

        // Update penalty type to runtime
        {
            \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $directorLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'penalty_type' => 'runtime',
                'languages' => 'c11-gcc',
            ]));
            $response = \OmegaUp\Controllers\Contest::apiRuns(new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $directorLogin->auth_token,
            ]));
            $this->assertSame(
                $response['runs'][0]['penalty'],
                $response['runs'][0]['runtime']
            );
        }

        // Update penalty type to none
        {
            \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $directorLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'penalty_type' => 'none',
                'languages' => 'c11-gcc',
            ]));
            $response = \OmegaUp\Controllers\Contest::apiRuns(new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $directorLogin->auth_token,
            ]));
            $this->assertSame(0, $response['runs'][0]['penalty']);
        }

        // Update penalty type to problem open.
        {
            \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $directorLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'penalty_type' => 'problem_open',
                'languages' => 'c11-gcc',
            ]));
            $response = \OmegaUp\Controllers\Contest::apiRuns(new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $directorLogin->auth_token,
            ]));
            $this->assertSame(5, $response['runs'][0]['penalty']);
        }

        // Update penalty type back to contest start.
        {
            \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $directorLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'penalty_type' => 'contest_start',
                'languages' => 'c11-gcc',
            ]));
            $response = \OmegaUp\Controllers\Contest::apiRuns(new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $directorLogin->auth_token,
            ]));
            $this->assertSame(
                $originalPenalty,
                $response['runs'][0]['penalty']
            );
        }
    }

    /**
     * Update window_length
     *
     */
    public function testUpdateWindowLength() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create our contestants
        ['identity' => $contestantIdentity] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();

        // Create a run
        \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $contestantIdentity
        );

        $directorLogin = self::login($contestData['director']);

        // Explicitly join contest
        \OmegaUp\Controllers\Contest::apiOpen(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
        ]));
        $contest = \OmegaUp\Controllers\Contest::apiDetails(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
        ]));

        // Create a run
        \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity2
        );

        $this->assertNull(
            $contest['window_length'],
            'Window length is not setted'
        );

        // Call API
        \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
            'window_length' => 0,
            'languages' => 'c11-gcc',
        ]));
        $contest = \OmegaUp\Controllers\Contest::apiDetails(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
        ]));

        $this->assertNull(
            $contest['window_length'],
            'Window length is not set, because 0 is not a valid value'
        );

        // Call API
        $windowLength = 10;
        \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
            'window_length' => $windowLength,
            'languages' => 'c11-gcc',
        ]));
        $contest = \OmegaUp\Controllers\Contest::apiDetails(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
        ]));

        $this->assertSame($windowLength, $contest['window_length']);

        // Update time for testing
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 700);

        try {
            // Trying to create a run out of contest time
            \OmegaUp\Test\Factories\Run::createRun(
                $problemData,
                $contestData,
                $contestantIdentity
            );
            $this->fail(
                'Contestant should not have been able to create a run outside of contest time'
            );
        } catch (\OmegaUp\Exceptions\NotAllowedToSubmitException $e) {
            // Pass
            $this->assertSame('runNotInsideContest', $e->getMessage());
        }

        // Call API
        $windowLength = 40;
        \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
            'window_length' => $windowLength,
            'languages' => 'c11-gcc',
        ]));
        $contest = \OmegaUp\Controllers\Contest::apiDetails(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
        ]));

        $this->assertSame($windowLength, $contest['window_length']);

        // Trying to create a run inside contest time
        \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $contestantIdentity
        );

        try {
            // Call API
            \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $directorLogin->auth_token,
                'window_length' => 'Not valid',
            ]));
            $this->fail('Only numbers are allowed in window_length field');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            // Pass
            $this->assertSame('parameterNotANumber', $e->getMessage());
        }

        $identities = \OmegaUp\Controllers\Contest::apiUsers(new \OmegaUp\Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]));

        $index = array_search(
            $contestantIdentity->username,
            array_column($identities['users'], 'username')
        );

        // Extend end_time for an indentity
        \OmegaUp\Controllers\Contest::apiUpdateEndTimeForIdentity(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
            'username' => $contestantIdentity->username,
            'end_time' => $identities['users'][$index]['access_time']->time + 60 * 60,
        ]));

        $identities = \OmegaUp\Controllers\Contest::apiUsers(new \OmegaUp\Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]));

        foreach ($identities['users'] as $identity) {
            if ($identity['username'] === $contestantIdentity->username) {
                // Identity with extended time
                $this->assertSame(
                    $identity['end_time']->time,
                    $identity['access_time']->time + 60 * 60
                );
            } else {
                // Other identities keep end time with window length
                $this->assertSame(
                    $identity['end_time']->time,
                    $identity['access_time']->time + $windowLength * 60
                );
            }
        }

        // Updating window_length in the contest, to check whether user keeps the extension
        $windowLength = 20;
        \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'window_length' => $windowLength,
            'languages' => 'c11-gcc',
        ]));

        $identities = \OmegaUp\Controllers\Contest::apiUsers(new \OmegaUp\Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]));

        foreach ($identities['users'] as $identity) {
            // End time for all participants has been updated and extended time for the identity is no longer available
            $this->assertSame(
                $identity['end_time']->time,
                $identity['access_time']->time + $windowLength * 60
            );
        }

        // Updating window_length in the contest out of the valid range
        try {
            \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $directorLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'window_length' => 140,
            ]));
            $this->fail('Window length can not greater than contest length');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            // Pass
            $this->assertSame('parameterNumberTooLarge', $e->getMessage());
        }
    }

    /**
     * Test to get the submission deadline of a contest when it has been set as
     * "With different starts", and director update the time for a certain
     * contestant.
     */
    public function testUpdateSubmissionDeadlineInAContestWithDifferentStarts() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create our contestants
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create a run
        \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );

        $directorLogin = self::login($contestData['director']);

        // Updating window_length in the contest, to check whether user keeps the extension
        $windowLength = 20;
        \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'window_length' => $windowLength,
            'languages' => 'c11-gcc',
        ]));

        $identities = \OmegaUp\Controllers\Contest::apiUsers(new \OmegaUp\Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]));

        $login = self::login($identity);

        $index = array_search(
            $identity->username,
            array_column($identities['users'], 'username')
        );

        $contestDetails = \OmegaUp\Controllers\Contest::getContestDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
            ])
        )['templateProperties']['payload'];

        // Submission deadline should be 20 minutes after contestant starts the
        // contest
        $this->assertSame(
            $contestDetails['submissionDeadline']->time,
            $identities['users'][$index]['access_time']->time + 20 * 60
        );

        $endTime = $identities['users'][$index]['access_time']->time + 60 * 60;
        // Extend end_time for an indentity
        \OmegaUp\Controllers\Contest::apiUpdateEndTimeForIdentity(
            new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $directorLogin->auth_token,
                'username' => $identity->username,
                'end_time' => $endTime,
            ])
        );

        $contestDetails = \OmegaUp\Controllers\Contest::getContestDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
            ])
        )['templateProperties']['payload'];

        // Now, submission deadline should be updated to 60 minutes after
        // contestant starts the contest
        $this->assertSame(
            $contestDetails['submissionDeadline']->time,
            $endTime
        );
    }

    /**
     * Creates a contest with window length, and then update window_length
     * again
     */
    public function testUpdateWindowLengthAtTheEndOfAContest() {
        // Get a problem
        $problem = \OmegaUp\Test\Factories\Problem::createProblem();

        $originalTime = new \OmegaUp\Timestamp(\OmegaUp\Time::get());

        // Create contest with 5 hours and a window length 20 of minutes
        $contest = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'windowLength' => 20,
                'startTime' => $originalTime,
                'finishTime' => new \OmegaUp\Timestamp(
                    $originalTime->time + 60 * 5 * 60
                ),
            ])
        );

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problem,
            $contest
        );

        // Create a contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Add contestant to contest
        \OmegaUp\Test\Factories\Contest::addUser($contest, $identity);

        // User joins the contest 4 hours and 50 minutes after it starts
        $updatedTime = $originalTime->time + 290 * 60;
        \OmegaUp\Time::setTimeForTesting($updatedTime);
        \OmegaUp\Test\Factories\Contest::openContest(
            $contest['contest'],
            $identity
        );
        $directorLogin = self::login($contest['director']);

        // Update window_length
        \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'contest_alias' => $contest['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
            'window_length' => 30,
            'languages' => 'c11-gcc',
        ]));

        // 15 minutes later User can not create a run because the contest is over
        $updatedTime = $updatedTime + 15 * 60;
        \OmegaUp\Time::setTimeForTesting($updatedTime);
        try {
            \OmegaUp\Test\Factories\Run::createRun(
                $problem,
                $contest,
                $identity
            );
            $this->fail(
                'Contestant should not create a run after contest finishes'
            );
        } catch (\OmegaUp\Exceptions\NotAllowedToSubmitException $e) {
            // Pass
            $this->assertSame('runNotInsideContest', $e->getMessage());
        } finally {
            \OmegaUp\Time::setTimeForTesting($originalTime->time);
        }
    }

    /**
     * Creates a contest with window length, and then update finish_time
     */
    public function testUpdateFinishTimeInAContestWithWindowLength() {
        // Get a problem
        $problem = \OmegaUp\Test\Factories\Problem::createProblem();

        $originalTime = new \OmegaUp\Timestamp(\OmegaUp\Time::get());

        // Create contest with 5 hours and a window length 60 of minutes
        $contest = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'windowLength' => 60,
                'startTime' => $originalTime,
                'finishTime' => new \OmegaUp\Timestamp(
                    $originalTime->time + 60 * 5 * 60
                ),
            ])
        );

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problem,
            $contest
        );

        // Create a contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Add contestant to contest
        \OmegaUp\Test\Factories\Contest::addUser($contest, $identity);

        // User joins contest immediatly it was created
        \OmegaUp\Test\Factories\Contest::openContest(
            $contest['contest'],
            $identity
        );

        $directorLogin = self::login($contest['director']);

        // Director extends finish_time one more hour
        \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'contest_alias' => $contest['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
            'finish_time' => $originalTime->time + 60 * 5 * 60,
            'languages' => 'c11-gcc',
        ]));

        // User creates a run 50 minutes later, it is ok
        $updatedTime = $originalTime->time + 50 * 60;
        \OmegaUp\Time::setTimeForTesting($updatedTime);
        $run = \OmegaUp\Test\Factories\Run::createRun(
            $problem,
            $contest,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($run, 1.0, 'AC', 10);

        // 20 minutes later is no longer available because window_length has
        // expired
        $updatedTime = $updatedTime + 20 * 60;
        \OmegaUp\Time::setTimeForTesting($updatedTime);
        try {
            \OmegaUp\Test\Factories\Run::createRun(
                $problem,
                $contest,
                $identity
            );
            $this->fail(
                'Contestant should not create a run after window length expires'
            );
        } catch (\OmegaUp\Exceptions\NotAllowedToSubmitException $e) {
            // Pass
            $this->assertSame('runNotInsideContest', $e->getMessage());
        } finally {
            \OmegaUp\Time::setTimeForTesting($originalTime->time);
        }
    }

    /**
     * Director extends time to user that joined the contest when it is almost over
     */
    public function testExtendTimeAtTheEndOfTheContest() {
        // Get a problem
        $problem = \OmegaUp\Test\Factories\Problem::createProblem();

        $originalTime = \OmegaUp\Time::get();

        // Create contest with 5 hours and a window length 60 of minutes
        $contest = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'windowLength' => 60,
                'startTime' => $originalTime,
                'finishTime' => $originalTime + 60 * 5 * 60,
            ])
        );

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problem,
            $contest
        );

        // Create a contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Add contestant to contest
        \OmegaUp\Test\Factories\Contest::addUser($contest, $identity);

        // User joins the contest 4 hours and 30 minutes after it starts
        $updatedTime = $originalTime + 270 * 60;
        \OmegaUp\Time::setTimeForTesting($updatedTime);
        \OmegaUp\Test\Factories\Contest::openContest(
            $contest['contest'],
            $identity
        );
        \OmegaUp\Test\Factories\Contest::openProblemInContest(
            $contest,
            $problem,
            $identity
        );

        // User creates a run 20 minutes later, it is ok
        $updatedTime = $updatedTime + 20 * 60;
        \OmegaUp\Time::setTimeForTesting($updatedTime);
        $run = \OmegaUp\Test\Factories\Run::createRun(
            $problem,
            $contest,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($run, 1.0, 'AC', 10);

        $directorLogin = self::login($contest['director']);

        \OmegaUp\Controllers\Contest::apiUsers(new \OmegaUp\Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contest['request']['alias'],
        ]));

        // Extend end_time for the user, now should be valid create runs after contest finishes
        \OmegaUp\Controllers\Contest::apiUpdateEndTimeForIdentity(new \OmegaUp\Request([
            'contest_alias' => $contest['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
            'username' => $identity->username,
            'end_time' => $updatedTime + 30 * 60,
        ]));

        $updatedTime = $updatedTime + 20 * 60;
        \OmegaUp\Time::setTimeForTesting($updatedTime);
        $run = \OmegaUp\Test\Factories\Run::createRun(
            $problem,
            $contest,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($run, 1.0, 'AC', 10);
    }

    public function testUpdateDisableWindowLength() {
        // Get a problem
        $problem = \OmegaUp\Test\Factories\Problem::createProblem();

        $originalTime = \OmegaUp\Time::get();

        // Create contest with 5 hours and a window length 60 of minutes
        $contest = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'windowLength' => 30,
                'startTime' => $originalTime,
                'finishTime' => $originalTime + 60 * 2 * 60,
            ])
        );

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problem,
            $contest
        );

        // Create a contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Add contestant to contest
        \OmegaUp\Test\Factories\Contest::addUser($contest, $identity);

        // User joins the contest 10 minutes after it starts
        $updatedTime = $originalTime + 10 * 60;
        \OmegaUp\Time::setTimeForTesting($updatedTime);
        \OmegaUp\Test\Factories\Contest::openContest(
            $contest['contest'],
            $identity
        );
        \OmegaUp\Test\Factories\Contest::openProblemInContest(
            $contest,
            $problem,
            $identity
        );

        // User creates a run 20 minutes later, it is ok
        $updatedTime = $updatedTime + 20 * 60;
        \OmegaUp\Time::setTimeForTesting($updatedTime);
        $run = \OmegaUp\Test\Factories\Run::createRun(
            $problem,
            $contest,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($run, 1.0, 'AC', 10);

        // User tries to create another run 20 minutes later, it should fail
        $updatedTime = $updatedTime + 20 * 60;
        \OmegaUp\Time::setTimeForTesting($updatedTime);
        try {
            \OmegaUp\Test\Factories\Run::createRun(
                $problem,
                $contest,
                $identity
            );
            $this->fail(
                'User should not be able to send runs beause window_length is over'
            );
        } catch (\OmegaUp\Exceptions\NotAllowedToSubmitException $e) {
            // Pass
            $this->assertSame('runNotInsideContest', $e->getMessage());
        }

        $directorLogin = self::login($contest['director']);

        $r = new \OmegaUp\Request([
            'contest_alias' => $contest['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
            'window_length' => 60,
            'languages' => 'c11-gcc',
        ]);

        // Call API
        \OmegaUp\Controllers\Contest::apiUpdate($r);

        // User tries to create run agaian, it should works fine
        $run = \OmegaUp\Test\Factories\Run::createRun(
            $problem,
            $contest,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($run, 1.0, 'AC', 10);

        // User tries to create another run 30 minutes later, it should fail
        $updatedTime = $updatedTime + 30 * 60;
        \OmegaUp\Time::setTimeForTesting($updatedTime);
        try {
            \OmegaUp\Test\Factories\Run::createRun(
                $problem,
                $contest,
                $identity
            );
            $this->fail(
                'User should not be able to send runs beause window_length is over'
            );
        } catch (\OmegaUp\Exceptions\NotAllowedToSubmitException $e) {
            // Pass
            $this->assertSame('runNotInsideContest', $e->getMessage());
        }

        // Now, director disables window_length
        $r['window_length'] = 0;

        // Call API
        \OmegaUp\Controllers\Contest::apiUpdate($r);

        // Now user can submit run until contest finishes
        $run = \OmegaUp\Test\Factories\Run::createRun(
            $problem,
            $contest,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($run, 1.0, 'AC', 10);
    }

    public function testCreateSubmissionAfterUpdateContest() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        $login = self::login($contestData['director']);

        // Call API
        \OmegaUp\Controllers\Contest::apiUpdate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'admission_mode' => 'public',
            ])
        );

        $this->createRunInContest($contestData);
    }

    /**
     * A PHPUnit data provider for the test with different score mode values.
     *
     * @return list<array{0:string, 1: int, 2: float, 3: float}>
     */
    public function scoreModeValueProvider(): array {
        return [
            ['all_or_nothing', 1, 0.0, 0.05],
            ['partial',  1, 0.05, 0.0],
            ['all_or_nothing', 100, 0.0, 5.0],
            ['partial', 100, 5.0, 0.0],
        ];
    }

    /**
     * @dataProvider scoreModeValueProvider
     */
    public function testCreateContestWhenScoreModeIsUpdated(
        string $initialScoreMode,
        int $problemsetProblemPoints,
        float $expectedContestScoreBeforeUpdate,
        float $expectedContestScoreAfterUpdate
    ) {
        // Get user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $scoreMode = $initialScoreMode;

        // Get a contest, score_mode default value is $scoreMode
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['scoreMode' => $scoreMode]
            )
        );

        $directorLogin = self::login($contestData['director']);

        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(
            login: $directorLogin,
        );

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData,
            $problemsetProblemPoints
        );

        // Add user to contest
        \OmegaUp\Test\Factories\Contest::addUser($contestData, $identity);

        $login = self::login($identity);

        // Explicitly join contest
        \OmegaUp\Controllers\Contest::apiOpen(
            new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $login->auth_token,
            ])
        );
        $contest = \OmegaUp\Controllers\Contest::apiDetails(
            new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $login->auth_token,
            ])
        );

        $this->assertSame($scoreMode, $contest['score_mode']);

        $runData = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun(
            $runData,
            0.05,
            verdict: 'PA',
            problemsetPoints: $problemsetProblemPoints,
        );

        $directorLogin = self::login($contestData['director']);

        $this->assertAPIsShowCorrectContestScore(
            $contestData['request']['alias'],
            $contestData['contest']->problemset_id,
            $problemData['request']['problem_alias'],
            $directorLogin->auth_token,
            $runData['response']['guid'],
            $expectedContestScoreBeforeUpdate,
            $scoreMode,
            $identity->username
        );

        // Updating score mode
        $scoreMode = $initialScoreMode == 'partial' ? 'all_or_nothing' : 'partial';

        // Call API to update score mode
        \OmegaUp\Controllers\Contest::apiUpdate(
            new \OmegaUp\Request([
                'auth_token' => $directorLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'score_mode' => $scoreMode,
                'languages' => 'c11-gcc',
            ])
        );

        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );

        $login = self::login($identity);

        $contest = \OmegaUp\Controllers\Contest::apiDetails(
            new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $login->auth_token,
            ])
        );

        $this->assertSame($scoreMode, $contest['score_mode']);

        $this->assertAPIsShowCorrectContestScore(
            $contestData['request']['alias'],
            $contestData['contest']->problemset_id,
            $problemData['request']['problem_alias'],
            $directorLogin->auth_token,
            $runData['response']['guid'],
            $expectedContestScoreAfterUpdate,
            $scoreMode,
            $identity->username
        );
    }

    public function testUpdateContestForTeams() {
        // Get a problem
        $problem = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get two teams groups
        [
            'teamGroup' => $teamGroup,
        ] = \OmegaUp\Test\Factories\Groups::createTeamsGroup();
        [
            'teamGroup' => $otherTeamGroup,
        ] = \OmegaUp\Test\Factories\Groups::createTeamsGroup();

        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'contestForTeams' => true,
                'teamsGroupAlias' => $teamGroup->alias,
            ])
        );

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problem,
            $contestData
        );

        $login = self::login($contestData['director']);

        $response = \OmegaUp\Controllers\Contest::getContestEditForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
            ])
        )['templateProperties']['payload'];

        $this->assertTrue($response['details']['contest_for_teams']);
        $this->assertSame([
            'alias' => $teamGroup->alias,
            'name' =>  $teamGroup->name,
        ], $response['teams_group']);

        // Update teams group
        \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'contest_for_teams' => true,
            'teams_group_alias' => $otherTeamGroup->alias,
        ]));

        $response = \OmegaUp\Controllers\Contest::getContestEditForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
            ])
        )['templateProperties']['payload'];

        $this->assertTrue($response['details']['contest_for_teams']);
        $this->assertSame([
            'alias' => $otherTeamGroup->alias,
            'name' =>  $otherTeamGroup->name,
        ], $response['teams_group']);
    }

    public function testUpdateContestForTeamsFromPrivateToPublic() {
        [
            'teamGroup' => $teamGroup,
        ] = \OmegaUp\Test\Factories\Groups::createTeamsGroup();

        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'contestForTeams' => true,
                'teamsGroupAlias' => $teamGroup->alias,
            ])
        );

        $login = self::login($contestData['director']);

        // Update contest for teams to normal contest is not allowed
        try {
            \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'contest_for_teams' => false,
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame(
                'contestForTeamsCanNotChangeToContest',
                $e->getMessage()
            );
        }
    }

    /**
     * A PHPUnit data provider for the test with changes in problemset attributes.
     *
     * @return list<array{0: array<string: bool|string>, 1: array{0: boolean, 1: string}, 2: array{0: boolean, 1: string}}>
     */
    public function problemsetAttributeValueProvider(): array {
        return [
            [['needs_basic_information' => true], [false, 'no'], [true, 'no']],
            [[
                'requests_user_information' => 'optional',
            ], [false, 'no'], [false, 'optional']],
            [[
                'needs_basic_information' => true,
                'requests_user_information' => 'optional',
            ], [false, 'no'], [true, 'optional']],
        ];
    }

    /**
     * @dataProvider problemsetAttributeValueProvider
     *
     * @param array<string: bool|string> $attributesToUpdate
     * @param array{0: boolean, 1: string} $originalAttributesValues
     * @param array{0: boolean, 1: string} $updatedAttributesValues
     */
    public function testUpdateProblemsetAttibutesInContest(
        $attributesToUpdate,
        $originalAttributesValues,
        $updatedAttributesValues
    ) {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        $login = self::login($contestData['director']);

        $response = \OmegaUp\Controllers\Contest::apiDetails(
            new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $login->auth_token,
            ])
        );

        // Default values
        $this->assertSame(
            $response['needs_basic_information'],
            $originalAttributesValues[0]
        );
        $this->assertSame(
            $response['requests_user_information'],
            $originalAttributesValues[1]
        );

        // Updating problemset attributes values with admission_mode should
        // update them
        \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request(
            array_merge(
                [
                    'auth_token' => $login->auth_token,
                    'contest_alias' => $contestData['request']['alias'],
                    'admission_mode' => 'private'
                ],
                $attributesToUpdate
            )
        ));

        $response = \OmegaUp\Controllers\Contest::apiDetails(
            new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $login->auth_token,
            ])
        );

        // Value after updating
        $this->assertSame(
            $response['needs_basic_information'],
            $updatedAttributesValues[0]
        );
        $this->assertSame(
            $response['requests_user_information'],
            $updatedAttributesValues[1]
        );

        \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'admission_mode' => 'public',
        ]));

        // Updating admission mode, problemset attributes should not change
        $response = \OmegaUp\Controllers\Contest::apiDetails(
            new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $login->auth_token,
            ])
        );

        $this->assertSame(
            $response['needs_basic_information'],
            $updatedAttributesValues[0]
        );
        $this->assertSame(
            $response['requests_user_information'],
            $updatedAttributesValues[1]
        );
    }

    private function assertAPIsShowCorrectContestScore(
        string $contestAlias,
        int $problemsetId,
        string $problemAlias,
        string $directorToken,
        string $runGuid,
        float $expectedContestScore,
        string $scoreMode,
        string $contestantUsername
    ) {
        $runs = \OmegaUp\Controllers\Contest::apiRuns(new \OmegaUp\Request([
            'contest_alias' => $contestAlias,
            'auth_token' => $directorToken,
        ]))['runs'];
        $this->assertCount(1, $runs);
        $run = $runs[0];
        $status = \OmegaUp\Controllers\Run::apiStatus(new \OmegaUp\Request([
            'run_alias' => $runGuid,
            'auth_token' => $directorToken,
        ]));
        $reportRankingProblems = \OmegaUp\Controllers\Contest::apiReport(
            new \OmegaUp\Request([
                'contest_alias' => $contestAlias,
                'auth_token' => $directorToken,
            ])
        )['ranking'];
        $this->assertCount(1, $reportRankingProblems);
        $this->assertCount(1, $reportRankingProblems[0]['problems']);
        $report = $reportRankingProblems[0]['problems'][0];
        $scoreboardProblems = \OmegaUp\Controllers\Contest::apiScoreboard(
            new \OmegaUp\Request([
                'contest_alias' => $contestAlias,
                'auth_token' => $directorToken,
            ])
        )['ranking'];
        $this->assertCount(1, $scoreboardProblems);
        $this->assertCount(1, $scoreboardProblems[0]['problems']);
        $scoreboard = $scoreboardProblems[0]['problems'][0];
        $problemRuns = \OmegaUp\Controllers\Problem::apiRuns(
            new \OmegaUp\Request([
                'problem_alias' => $problemAlias,
                'auth_token' => $directorToken,
                'show_all' => true,
            ])
        )['runs'];
        $this->assertCount(1, $problemRuns);
        $problemRun = $problemRuns[0];
        $details = \OmegaUp\Controllers\Run::apiDetails(new \OmegaUp\Request([
            'run_alias' => $runGuid,
            'auth_token' => $directorToken,
        ]))['details'];
        $bestScore = \OmegaUp\Controllers\Problem::apiBestScore(
            new \OmegaUp\Request([
                'auth_token' => $directorToken,
                'problem_alias' => $problemAlias,
                'problemset_id' => $problemsetId,
                'username' => $contestantUsername,
            ])
        )['score'];

        // This function gets details to download contest runs details
        [$download] = \OmegaUp\DAO\Runs::getByProblemset($problemsetId);

        $this->assertSame($expectedContestScore, $run['contest_score']);
        $this->assertSame($expectedContestScore, $status['contest_score']);
        $this->assertSame($expectedContestScore, $scoreboard['points']);
        $this->assertSame($expectedContestScore, $details['contest_score']);
        $this->assertSame($expectedContestScore, $bestScore);
        $this->assertSame($expectedContestScore, $download['contest_score']);
        $this->assertSame(
            $expectedContestScore,
            $problemRun['contest_score']
        );
        if ($scoreMode == 'partial') {
            $this->assertArrayHasKey('run_details', $report);
        } elseif ($scoreMode == 'all_or_nothing') {
            $this->assertArrayNotHasKey('run_details', $report);
        }

        // Get admin
        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createAdminUser();
        $adminLogin = self::login($admin);
        $runsList = \OmegaUp\Controllers\Run::apiList(new \OmegaUp\Request([
            'problem_alias' => $problemAlias,
            'auth_token' => $adminLogin->auth_token,
        ]))['runs'];
        $this->assertCount(1, $runsList);
        $runList = $runsList[0];
        $this->assertSame(
            $expectedContestScore,
            $runList['contest_score']
        );
    }

    /**
     * A PHPUnit data provider for all the score mode to get profile details.
     *
     * @return list<array{0:string}>
     */
    public function scoreModeProvider(): array {
        return [
            ['all_or_nothing'],
            ['partial'],
        ];
    }

    /**
     * @dataProvider scoreModeProvider
     */
    public function testUpdateContestWithScoreMode(string $scoreModeExpected) {
        // Get a problem
        $problem = \OmegaUp\Test\Factories\Problem::createProblem();

        // Create contest with 2 hours and a window length 30 of minutes
        $contest = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'scoreMode' => $scoreModeExpected,
            ])
        );

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problem,
            $contest
        );

        // Create a contestant
        $login = self::login($contest['director']);
        $newScoreModeExpected = $scoreModeExpected == 'partial' ? 'all_or_nothing' : 'partial';

        \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contest['request']['alias'],
                'score_mode' => $newScoreModeExpected,
            ]));

        // Update a contest request
        $response = \OmegaUp\DAO\Contests::getByAlias(
            $contest['request']['alias']
        );

        $this->assertSame($response->score_mode, $newScoreModeExpected);
    }

    /**
     * A PHPUnit data provider for the test with different penalty types.
     *
     * @return list<array{0:string, 1: int, 2: float, 3: float}>
     */
    public function penaltyTypeProvider(): array {
        return [
            ['contest_start', 'runtime', null],
            ['contest_start', 'none', 0],
            ['contest_start', 'problem_open', 5],
            ['runtime', 'none', 0],
            ['runtime', 'problem_open', 5],
            ['runtime', 'contest_start', 10],
            ['none', 'problem_open', 5],
            ['none', 'contest_start', 10],
            ['none', 'runtime', null],
            ['problem_open', 'contest_start', 10],
            ['problem_open', 'runtime', null],
            ['problem_open', 'none', 0],
        ];
    }

    /**
     * Contestant submits runs and admin edits the problem version and then
     * edits the penalty type of an active contest
     *
     * @dataProvider penaltyTypeProvider
     */
    public function testUpdatePenaltyTypeFromAContestWhenProblemVersionIsUpdated(
        string $originalPenaltyType,
        string $updatedPenaltyType,
        ?int $expectedPenalty
    ) {
        $originalTime = new \OmegaUp\Timestamp(\OmegaUp\Time::get());

        // Create a contest with one problem.
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'penaltyType' => $originalPenaltyType,
                'startTime' => $originalTime,
                'lastUpdated' => $originalTime,
                'finishTime' => new \OmegaUp\Timestamp(
                    $originalTime->time + 60 * 60
                ),
            ])
        );
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create a run
        {
            \OmegaUp\Time::setTimeForTesting($originalTime->time + 5 * 60);
            ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
            \OmegaUp\Test\Factories\Contest::addUser($contestData, $identity);

            // The problem is opened 5 minutes after contest starts.
            \OmegaUp\Test\Factories\Contest::openContest(
                $contestData['contest'],
                $identity
            );
            \OmegaUp\Test\Factories\Contest::openProblemInContest(
                $contestData,
                $problemData,
                $identity
            );

            // The run is sent 10 minutes after contest starts.
            \OmegaUp\Time::setTimeForTesting($originalTime->time + 10 * 60);
            $runData = \OmegaUp\Test\Factories\Run::createRun(
                $problemData,
                $contestData,
                $identity
            );
            \OmegaUp\Test\Factories\Run::gradeRun($runData, 1.0, 'AC', 10);
            \OmegaUp\Time::setTimeForTesting($originalTime->time);
        }

        $problemAdminLogin = self::login($problemData['author']);

        // Change the problem to something completely different.
        $_FILES['problem_contents']['tmp_name'] = OMEGAUP_TEST_RESOURCES_ROOT . 'mrkareltastic.zip';
        $detourGrader = new \OmegaUp\Test\ScopedGraderDetour();
        $response = \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $problemAdminLogin->auth_token,
            'problem_alias' => $problemData['problem']->alias,
            'message' => 'Changed to mrkareltastic',
            'validator' => 'token',
            'time_limit' => 1000,
            'overall_wall_time_limit' => 30000,
            'validator_time_limit' => 0,
            'extra_wall_time' => 1000,
            'memory_limit' => 64000,
            'output_limit' => 20480,
        ]));
        $this->assertTrue($response['rejudged']);
        $this->assertSame(1, $detourGrader->getGraderCallCount());
        unset($_FILES['problem_contents']);
        foreach ($detourGrader->getRuns() as $run) {
            \OmegaUp\Test\Factories\Run::gradeRun(
                null,
                0,
                'WA',
                null,
                null,
                $run->run_id
            );
        }

        $versionData = \OmegaUp\Controllers\Problem::apiVersions(
            new \OmegaUp\Request([
                'auth_token' => $problemAdminLogin->auth_token,
                'problem_alias' => $problemData['problem']->alias,
            ])
        );

        $directorLogin = self::login($contestData['director']);

        // Call API
        \OmegaUp\Controllers\Contest::apiAddProblem(new \OmegaUp\Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
            'points' => 100,
            'commit' => $versionData['published'],
        ]));

        // Update penalty type
        \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'penalty_type' => $updatedPenaltyType,
            'languages' => 'c11-gcc',
        ]));
        ['runs' => $runs] = \OmegaUp\Controllers\Contest::apiRuns(
            new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $directorLogin->auth_token,
            ])
        );
        if ($updatedPenaltyType === 'runtime') {
            $expectedPenalty = $runs[0]['runtime'];
        }
        $this->assertSame($runs[0]['penalty'], $expectedPenalty);
    }

    /**
     * A PHPUnit data provider for testing contest recommended flag permissions.
     *
     * @return list<array{0: string, 1: bool}>
     */
    public function contestRecommendedPermissionsProvider(): array {
        return [
            [\OmegaUp\Authorization::SUPPORT_GROUP_ALIAS, true],
            [\OmegaUp\Authorization::QUALITY_REVIEWER_GROUP_ALIAS, false],
            [\OmegaUp\Authorization::COURSE_CURATOR_GROUP_ALIAS, false],
            [\OmegaUp\Authorization::MENTOR_GROUP_ALIAS, false],
            [\OmegaUp\Authorization::IDENTITY_CREATOR_GROUP_ALIAS, false],
            [\OmegaUp\Authorization::CERTIFICATE_GENERATOR_GROUP_ALIAS, false],
            [\OmegaUp\Authorization::TEACHING_ASSISTANT_GROUP_ALIAS, false],
        ];
    }

    /**
     * Test setting recommended flag with different user roles
     *
     * @dataProvider contestRecommendedPermissionsProvider
     */
    public function testSetRecommendedFlagPermissions(
        string $groupAlias,
        bool $shouldHaveAccess
    ) {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Create user and add to corresponding group
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Add user to the group
        $group = \OmegaUp\DAO\Groups::findByAlias($groupAlias);
        \OmegaUp\DAO\GroupsIdentities::create(new \OmegaUp\DAO\VO\GroupsIdentities([
            'group_id' => $group?->group_id,
            'identity_id' => $identity->identity_id,
        ]));

        // Login with the user
        $login = self::login($identity);

        try {
            $r = new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'value' => true,
            ]);

            // Call API
            \OmegaUp\Controllers\Contest::apiSetRecommended($r);

            if (!$shouldHaveAccess) {
                $this->fail(
                    'User should not have access to set recommended flag'
                );
            }

            // Verify setting was applied
            $contestData['request']['recommended'] = $r['value'];
            $this->assertContest($contestData['request']);

            // Try turning it off
            $r['value'] = false;
            \OmegaUp\Controllers\Contest::apiSetRecommended($r);

            // Verify setting was applied
            $contestData['request']['recommended'] = $r['value'];
            $this->assertContest($contestData['request']);
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            if ($shouldHaveAccess) {
                $this->fail('User should have access to set recommended flag');
            }
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }
}
