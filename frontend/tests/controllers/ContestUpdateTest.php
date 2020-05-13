<?php

/**
 * Description of ContestUpdateContest
 *
 * @author joemmanuel
 */
class ContestUpdateTest extends \OmegaUp\Test\ControllerTestCase {
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
        ]);

        // Call API
        $response = \OmegaUp\Controllers\Contest::apiUpdate($r);

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
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'title' => \OmegaUp\Test\Utils::createRandomString(),
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
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
            $this->assertEquals(
                'contestPublicRequiresProblem',
                $e->getMessage()
            );
        }
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
        $response = \OmegaUp\Controllers\Contest::apiUpdate($r);

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
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createAdminUser();
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
            $this->assertEquals('userNotAllowed', $e->getMessage());
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
            $this->assertEquals('contestLengthTooLong', $e->getMessage());
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
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Our contestant has to open the contest before sending a run
        \OmegaUp\Test\Factories\Contest::openContest($contestData, $identity);

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
            $response = \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'start_time' => $contestData['request']['start_time'] + 1,
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals(
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
        ]);

        // Call API
        $response = \OmegaUp\Controllers\Contest::apiUpdate($r);

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
        ]);

        // Call API
        $response = \OmegaUp\Controllers\Contest::apiUpdate($r);

        // Check contest data from DB
        $this->assertContest($contestData['request']);
    }

    /**
     * Contestant submits runs and admin edits the penalty type of an
     * active contest
     */
    public function testUpdatePenaltyTypeFromAContest() {
        $originalTime = new \OmegaUp\Timestamp(\OmegaUp\Time::get());

        // Create a contest with one problem.
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(new \OmegaUp\Test\Factories\ContestParams([
            'startTime' => $originalTime,
            'lastUpdated' => $originalTime,
            'finishTime' => new \OmegaUp\Timestamp(
                $originalTime->time + 60 * 60
            ),
        ]));
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create a run
        {
            \OmegaUp\Time::setTimeForTesting($originalTime->time + 5 * 60);
            ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
            \OmegaUp\Test\Factories\Contest::addUser($contestData, $identity);

            // The problem is opened 5 minutes after contest starts.
            \OmegaUp\Test\Factories\Contest::openContest(
                $contestData,
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
        $this->assertEquals(100, $response['runs'][0]['contest_score']);
        $originalPenalty = $response['runs'][0]['penalty'];

        // Update penalty type to runtime
        {
            \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $directorLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'penalty_type' => 'runtime',
            ]));
            $response = \OmegaUp\Controllers\Contest::apiRuns(new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $directorLogin->auth_token,
            ]));
            $this->assertEquals(
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
            ]));
            $response = \OmegaUp\Controllers\Contest::apiRuns(new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $directorLogin->auth_token,
            ]));
            $this->assertEquals(0, $response['runs'][0]['penalty']);
        }

        // Update penalty type to problem open.
        {
            \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $directorLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'penalty_type' => 'problem_open',
            ]));
            $response = \OmegaUp\Controllers\Contest::apiRuns(new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $directorLogin->auth_token,
            ]));
            $this->assertEquals(5, $response['runs'][0]['penalty']);
        }

        // Update penalty type back to contest start.
        {
            \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $directorLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'penalty_type' => 'contest_start',
            ]));
            $response = \OmegaUp\Controllers\Contest::apiRuns(new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $directorLogin->auth_token,
            ]));
            $this->assertEquals(
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
        ['user' => $contestant, 'identity' => $contestantIdentity] = \OmegaUp\Test\Factories\User::createUser();
        ['user' => $contestant2, 'identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();

        // Create a run
        $runData = \OmegaUp\Test\Factories\Run::createRun(
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
        $runData = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity2
        );

        $this->assertNull(
            $contest['window_length'],
            'Window length is not setted'
        );

        // Call API
        $response = \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
            'window_length' => 0,
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
        $response = \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
            'window_length' => $windowLength,
        ]));
        $contest = \OmegaUp\Controllers\Contest::apiDetails(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
        ]));

        $this->assertEquals($windowLength, $contest['window_length']);

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
            $this->assertEquals('runNotInsideContest', $e->getMessage());
        }

        // Call API
        $windowLength = 40;
        $response = \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
            'window_length' => $windowLength,
        ]));
        $contest = \OmegaUp\Controllers\Contest::apiDetails(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
        ]));

        $this->assertEquals($windowLength, $contest['window_length']);

        // Trying to create a run inside contest time
        $runData = \OmegaUp\Test\Factories\Run::createRun(
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
            $this->assertEquals('parameterNotANumber', $e->getMessage());
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
                $this->assertEquals(
                    $identity['end_time']->time,
                    $identity['access_time']->time + 60 * 60
                );
            } else {
                // Other identities keep end time with window length
                $this->assertEquals(
                    $identity['end_time']->time,
                    $identity['access_time']->time + $windowLength * 60
                );
            }
        }

        // Updating window_length in the contest, to check whether user keeps the extension
        $windowLength = 20;
        $response = \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'window_length' => $windowLength,
        ]));

        $identities = \OmegaUp\Controllers\Contest::apiUsers(new \OmegaUp\Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]));

        foreach ($identities['users'] as $identity) {
            // End time for all participants has been updated and extended time for the identity is no longer available
            $this->assertEquals(
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
            $this->assertEquals('parameterNumberTooLarge', $e->getMessage());
        }
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
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Add contestant to contest
        \OmegaUp\Test\Factories\Contest::addUser($contest, $identity);

        // User joins the contest 4 hours and 50 minutes after it starts
        $updatedTime = $originalTime->time + 290 * 60;
        \OmegaUp\Time::setTimeForTesting($updatedTime);
        \OmegaUp\Test\Factories\Contest::openContest($contest, $identity);
        $directorLogin = self::login($contest['director']);

        // Update window_length
        $response = \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'contest_alias' => $contest['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
            'window_length' => 30,
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
            $this->assertEquals('runNotInsideContest', $e->getMessage());
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
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Add contestant to contest
        \OmegaUp\Test\Factories\Contest::addUser($contest, $identity);

        // User joins contest immediatly it was created
        \OmegaUp\Test\Factories\Contest::openContest($contest, $identity);

        $directorLogin = self::login($contest['director']);

        // Director extends finish_time one more hour
        $response = \OmegaUp\Controllers\Contest::apiUpdate(new \OmegaUp\Request([
            'contest_alias' => $contest['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
            'finish_time' => $originalTime->time + 60 * 5 * 60,
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
            $this->assertEquals('runNotInsideContest', $e->getMessage());
        } finally {
            \OmegaUp\Time::setTimeForTesting($originalTime->time);
        }
    }

    /**
     * Director extends time to user that joined the contest when it is almost is over
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
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Add contestant to contest
        \OmegaUp\Test\Factories\Contest::addUser($contest, $identity);

        // User joins the contest 4 hours and 30 minutes after it starts
        $updatedTime = $originalTime + 270 * 60;
        \OmegaUp\Time::setTimeForTesting($updatedTime);
        \OmegaUp\Test\Factories\Contest::openContest($contest, $identity);
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

        $identities = \OmegaUp\Controllers\Contest::apiUsers(new \OmegaUp\Request([
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
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Add contestant to contest
        \OmegaUp\Test\Factories\Contest::addUser($contest, $identity);

        // User joins the contest 10 minutes after it starts
        $updatedTime = $originalTime + 10 * 60;
        \OmegaUp\Time::setTimeForTesting($updatedTime);
        \OmegaUp\Test\Factories\Contest::openContest($contest, $identity);
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
            $this->assertEquals('runNotInsideContest', $e->getMessage());
        }

        $directorLogin = self::login($contest['director']);

        $r = new \OmegaUp\Request([
            'contest_alias' => $contest['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
            'window_length' => 60
        ]);

        // Call API
        $response = \OmegaUp\Controllers\Contest::apiUpdate($r);

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
            $this->assertEquals('runNotInsideContest', $e->getMessage());
        }

        // Now, director disables window_length
        $r['window_length'] = 0;

        // Call API
        $response = \OmegaUp\Controllers\Contest::apiUpdate($r);

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
        $response = \OmegaUp\Controllers\Contest::apiUpdate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'admission_mode' => 'public',
                'languages' => '',
            ])
        );

        $this->createRunInContest($contestData);
    }

    public function testCreateContestWithInitialPartialScoreAndThenUpdated() {
        // Get a contest, partial_score default value is true
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['partialScore' => true]
            )
        );

        $login = self::login($contestData['director']);

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

        $this->assertTrue($contest['partial_score']);

        // Call API to update partial score
        $response = \OmegaUp\Controllers\Contest::apiUpdate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'partial_score' => false,
            ])
        );
        $contest = \OmegaUp\Controllers\Contest::apiDetails(
            new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $login->auth_token,
            ])
        );

        $this->assertFalse($contest['partial_score']);
    }

    public function testCreateContestWithInitialPartialScoreFalseAndThenUpdated() {
        // Get a contest, partial_score value is false
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['partialScore' => false]
            )
        );

        $login = self::login($contestData['director']);

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

        $this->assertFalse($contest['partial_score']);

        // Call API to update partial score
        $response = \OmegaUp\Controllers\Contest::apiUpdate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'partial_score' => true,
            ])
        );
        $contest = \OmegaUp\Controllers\Contest::apiDetails(
            new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $login->auth_token,
            ])
        );

        $this->assertTrue($contest['partial_score']);
    }
}
