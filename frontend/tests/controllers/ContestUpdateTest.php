<?php

/**
 * Description of UpdateContest
 *
 * @author joemmanuel
 */
class UpdateContestTest extends OmegaupTestCase {
    /**
     * Only update the contest title. Rest should stay the same
     */
    public function testUpdateContestTitle() {
        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Update title.
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'title' => Utils::CreateRandomString(),
        ]);

        // Call API
        $response = ContestController::apiUpdate($r);

        // To validate, we update the title to the original request and send
        // the entire original request to assertContest. Any other parameter
        // should not be modified by Update api
        $contestData['request']['title'] = $r['title'];
        $this->assertContest($contestData['request']);
    }

    /**
     *
     * @expectedException ForbiddenAccessException
     */
    public function testUpdateContestNonDirector() {
        // Get a contest
        $contestData = ContestsFactory::createContest();
        // Update title
        $login = self::login(UserFactory::createUser());
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'title' => Utils::CreateRandomString(),
        ]);

        // Call API
        ContestController::apiUpdate($r);
    }

    /**
     * Update from private to public. Should fail if no problems in contest
     *
     * @expectedException \OmegaUp\Exceptions\InvalidParameterException
     */
    public function testUpdatePrivateContestToPublicWithoutProblems() {
        // Get a contest
        $contestData = ContestsFactory::createContest(new ContestParams(['admission_mode' => 'private']));

        // Update public
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'admission_mode' => 'public',
        ]);

        // Call API
        $response = ContestController::apiUpdate($r);
    }

    /**
     * Update from private to public with problems added
     *
     */
    public function testUpdatePrivateContestToPublicWithProblems() {
        // Get a contest
        $contestData = ContestsFactory::createContest(new ContestParams(['admission_mode' => 'private']));

        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Update public
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'admission_mode' => 'public',
        ]);

        // Call API
        $response = ContestController::apiUpdate($r);

        $contestData['request']['admission_mode'] = $r['admission_mode'];
        $this->assertContest($contestData['request']);
    }

     /**
      * Set Recommended flag to a given contest
      */
    public function testSetRecommendedFlag() {
        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Update value
        $login = self::login(UserFactory::createAdminUser());
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'value' => 1,
        ]);

        // Call API
        ContestController::apiSetRecommended($r);

        // Verify setting
        $contestData['request']['recommended'] = $r['value'];
        $this->assertContest($contestData['request']);

        // Turn flag down
        $r['value'] = 0;

        // Call API again
        ContestController::apiSetRecommended($r);

        // Verify setting
        $contestData['request']['recommended'] = $r['value'];
        $this->assertContest($contestData['request']);
    }

     /**
      * Set Recommended flag to a given contest from non admin account
      *
      * @expectedException ForbiddenAccessException
      */
    public function testSetRecommendedFlagNonAdmin() {
        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Update value
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'value' => 1,
        ]);

        // Call API
        ContestController::apiSetRecommended($r);
    }

    /**
     * Contest length can't be too long
     *
     * @expectedException \OmegaUp\Exceptions\InvalidParameterException
     */
    public function testUpdateContestLengthTooLong() {
        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Update length
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'start_time' => 0,
            'finish_time' => 60 * 60 * 24 * 32,
        ]);

        // Call API
        $response = ContestController::apiUpdate($r);
    }

    /**
     * Submit a run into a contest helper
     *
     */
    private function createRunInContest($contestData) {
        // STEP 1: Create a problem and add it to the contest
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // STEP 2: Get contestant ready to create a run
        // Create our contestant
        $contestant = UserFactory::createUser();

        // Our contestant has to open the contest before sending a run
        ContestsFactory::openContest($contestData, $contestant);

        // Then we need to open the problem
        ContestsFactory::openProblemInContest($contestData, $problemData, $contestant);

        // STEP 3: Send a new run
        $login = self::login($contestant);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
            'language' => 'c',
            'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
        ]);

        RunController::apiCreate($r);
    }

    /**
     * Contest start can't be updated if already contains runs
     *
     * @expectedException \OmegaUp\Exceptions\InvalidParameterException
     */
    public function testUpdateContestStartWithRuns() {
        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Submit a run
        $this->createRunInContest($contestData);

        // Update length
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'start_time' => $contestData['request']['start_time'] + 1,
        ]);

        // Call API
        $response = ContestController::apiUpdate($r);
    }

    /**
     * Contest length can be updated if no runs
     *
     */
    public function testUpdateContestStartNoRuns() {
        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Update length
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'start_time' => $contestData['request']['start_time'] + 1,
        ]);

        // Call API
        $response = ContestController::apiUpdate($r);

        // Check contest data from DB
        $contestData['request']['start_time'] = $r['start_time'];
        $this->assertContest($contestData['request']);
    }

    /**
     * Contest title can be updated if already contains runs and start time does not change
     *
     */
    public function testUpdateContestTitleWithRuns() {
        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Submit a run
        $this->createRunInContest($contestData);

        // Update title
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'start_time' => $contestData['request']['start_time'],
            'title' => 'New title',
        ]);

        // Call API
        $response = ContestController::apiUpdate($r);

        // Check contest data from DB
        $contestData['request']['start_time'] = $r['start_time'];
        $contestData['request']['title'] = $r['title'];
        $this->assertContest($contestData['request']);
    }

    /**
     * Contestant submits runs and admin edits the penalty type of an
     * active contest
     */
    public function testUpdatePenaltyTypeFromAContest() {
        $originalTime = \OmegaUp\Time::get();

        // Create a contest with one problem.
        $contestData = ContestsFactory::createContest(new ContestParams([
            'start_time' => $originalTime,
            'last_updated' => $originalTime,
            'finish_time' => $originalTime + 60 * 60,
        ]));
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create a run
        {
            \OmegaUp\Time::setTimeForTesting($originalTime + 5 * 60);
            $contestant = UserFactory::createUser();
            ContestsFactory::addUser($contestData, $contestant);

            // The problem is opened 5 minutes after contest starts.
            ContestsFactory::openContest($contestData, $contestant);
            ContestsFactory::openProblemInContest($contestData, $problemData, $contestant);

            // The run is sent 10 minutes after contest starts.
            \OmegaUp\Time::setTimeForTesting($originalTime + 10 * 60);
            $runData = RunsFactory::createRun($problemData, $contestData, $contestant);
            RunsFactory::gradeRun($runData, 1.0, 'AC', 10);
            \OmegaUp\Time::setTimeForTesting($originalTime);
        }

        $directorLogin = self::login($contestData['director']);

        // Get the original penalty, since the contest was created with the
        // 'contest_start' penalty type.
        $response = ContestController::apiRuns(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
        ]));
        $this->assertEquals(100, $response['runs'][0]['contest_score']);
        $originalPenalty = $response['runs'][0]['penalty'];

        // Update penalty type to runtime
        {
            ContestController::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $directorLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'penalty_type' => 'runtime',
            ]));
            $response = ContestController::apiRuns(new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $directorLogin->auth_token,
            ]));
            $this->assertEquals($response['runs'][0]['penalty'], $response['runs'][0]['runtime']);
        }

        // Update penalty type to none
        {
            ContestController::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $directorLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'penalty_type' => 'none',
            ]));
            $response = ContestController::apiRuns(new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $directorLogin->auth_token,
            ]));
            $this->assertEquals(0, $response['runs'][0]['penalty']);
        }

        // Update penalty type to problem open.
        {
            ContestController::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $directorLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'penalty_type' => 'problem_open',
            ]));
            $response = ContestController::apiRuns(new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $directorLogin->auth_token,
            ]));
            $this->assertEquals(5, $response['runs'][0]['penalty']);
        }

        // Update penalty type back to contest start.
        {
            ContestController::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $directorLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'penalty_type' => 'contest_start',
            ]));
            $response = ContestController::apiRuns(new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $directorLogin->auth_token,
            ]));
            $this->assertEquals($originalPenalty, $response['runs'][0]['penalty']);
        }
    }

    /**
     * Update window_length
     *
     */
    public function testUpdateWindowLength() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestants
        $contestant = UserFactory::createUser();
        $contestant2 = UserFactory::createUser();

        // Create a run
        $runData = RunsFactory::createRun($problemData, $contestData, $contestant);

        $directorLogin = self::login($contestData['director']);

        $r = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
        ]);

        // Explicitly join contest
        ContestController::apiOpen($r);

        $contest = ContestController::apiDetails($r);

        // Create a run
        $runData = RunsFactory::createRun($problemData, $contestData, $contestant2);

        $this->assertNull($contest['window_length'], 'Window length is not setted');

        $r['window_length'] = 0;
        // Call API
        $response = ContestController::apiUpdate($r);

        $contest = ContestController::apiDetails($r);

        $this->assertNull($contest['window_length'], 'Window length is not setted, because 0 is not a valid value');

        $windowLength = 10;
        $r['window_length'] = $windowLength;
        // Call API
        $response = ContestController::apiUpdate($r);

        $contest = ContestController::apiDetails($r);

        $this->assertEquals($windowLength, $contest['window_length']);

        // Update time for testing
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 700);

        try {
            // Trying to create a run out of contest time
            RunsFactory::createRun($problemData, $contestData, $contestant);
            $this->fail('User could not create a run when is out of contest time');
        } catch (NotAllowedToSubmitException $e) {
            // Pass
            $this->assertEquals('runNotInsideContest', $e->getMessage());
        }

        $windowLength = 40;
        $r['window_length'] = $windowLength;
        // Call API
        $response = ContestController::apiUpdate($r);

        $contest = ContestController::apiDetails($r);

        $this->assertEquals($windowLength, $contest['window_length']);

        // Trying to create a run inside contest time
        $runData = RunsFactory::createRun($problemData, $contestData, $contestant);

        $r['window_length'] = 'Not valid';

        try {
            // Call API
            ContestController::apiUpdate($r);
            $this->fail('Only numbers are allowed in window_length field');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            // Pass
            $this->assertEquals('parameterNotANumber', $e->getMessage());
        }

        $identities = ContestController::apiUsers(new \OmegaUp\Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]));

        $index = array_search(
            $contestant->username,
            array_column($identities['users'], 'username')
        );

        // Extend end_time for an indentity
        ContestController::apiUpdateEndTimeForIdentity(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
            'username' => $contestant->username,
            'end_time' => $identities['users'][$index]['access_time'] + 60 * 60,
        ]));

        $identities = ContestController::apiUsers(new \OmegaUp\Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]));

        foreach ($identities['users'] as $identity) {
            if ($identity['username'] == $contestant->username) {
                // Identity with extended time
                $this->assertEquals(
                    $identity['end_time'],
                    $identity['access_time'] + 60 * 60
                );
            } else {
                // Other identities keep end time with window length
                $this->assertEquals(
                    $identity['end_time'],
                    $identity['access_time'] + $windowLength * 60
                );
            }
        }

        // Updating window_length in the contest, to check whether user keeps the extension
        $windowLength = 20;
        $r['window_length'] = $windowLength;
        $response = ContestController::apiUpdate($r);

        $identities = ContestController::apiUsers(new \OmegaUp\Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]));

        foreach ($identities['users'] as $identity) {
            // End time for all participants has been updated and extended time for the identity is no longer available
            $this->assertEquals(
                $identity['end_time'],
                $identity['access_time'] + $windowLength * 60
            );
        }

        // Updating window_length in the contest out of the valid range
        $windowLength = 140;
        $r['window_length'] = $windowLength;
        try {
            ContestController::apiUpdate($r);
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
        $problem = ProblemsFactory::createProblem();

        $originalTime = \OmegaUp\Time::get();

        // Create contest with 5 hours and a window length 20 of minutes
        $contest = ContestsFactory::createContest(
            new ContestParams([
                'window_length' => 20,
                'start_time' => $originalTime,
                'finish_time' => $originalTime + 60 * 5 * 60,
            ])
        );

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problem, $contest);

        // Create a contestant
        $contestant = UserFactory::createUser();

        // Add contestant to contest
        ContestsFactory::addUser($contest, $contestant);

        // User joins the contest 4 hours and 50 minutes after it starts
        $updatedTime = $originalTime + 290 * 60;
        \OmegaUp\Time::setTimeForTesting($updatedTime);
        ContestsFactory::openContest($contest, $contestant);
        $directorLogin = self::login($contest['director']);

        // Update window_length
        $response = ContestController::apiUpdate(new \OmegaUp\Request([
            'contest_alias' => $contest['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
            'window_length' => 30,
        ]));

        // 15 minutes later User can not create a run because the contest is over
        $updatedTime = $updatedTime + 15 * 60;
        \OmegaUp\Time::setTimeForTesting($updatedTime);
        try {
            RunsFactory::createRun($problem, $contest, $contestant);
            $this->fail('Contestant should not create a run after contest finishes');
        } catch (NotAllowedToSubmitException $e) {
            // Pass
            $this->assertEquals('runNotInsideContest', $e->getMessage());
        } finally {
            \OmegaUp\Time::setTimeForTesting($originalTime);
        }
    }

    /**
     * Creates a contest with window length, and then update finish_time
     */
    public function testUpdateFinishTimeInAContestWithWindowLength() {
        // Get a problem
        $problem = ProblemsFactory::createProblem();

        $originalTime = \OmegaUp\Time::get();

        // Create contest with 5 hours and a window length 60 of minutes
        $contest = ContestsFactory::createContest(
            new ContestParams([
                'window_length' => 60,
                'start_time' => $originalTime,
                'finish_time' => $originalTime + 60 * 5 * 60,
            ])
        );

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problem, $contest);

        // Create a contestant
        $contestant = UserFactory::createUser();

        // Add contestant to contest
        ContestsFactory::addUser($contest, $contestant);

        // User joins contest immediatly it was created
        ContestsFactory::openContest($contest, $contestant);

        $directorLogin = self::login($contest['director']);

        // Director extends finish_time one more hour
        $response = ContestController::apiUpdate(new \OmegaUp\Request([
            'contest_alias' => $contest['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
            'finish_time' => $originalTime + 60 * 5 * 60,
        ]));

        // User creates a run 50 minutes later, it is ok
        $updatedTime = $originalTime + 50 * 60;
        \OmegaUp\Time::setTimeForTesting($updatedTime);
        $run = RunsFactory::createRun($problem, $contest, $contestant);
        RunsFactory::gradeRun($run, 1.0, 'AC', 10);

        // 20 minutes later is no longer available because window_length has
        // expired
        $updatedTime = $updatedTime + 20 * 60;
        \OmegaUp\Time::setTimeForTesting($updatedTime);
        try {
            RunsFactory::createRun($problem, $contest, $contestant);
            $this->fail('Contestant should not create a run after window length expires');
        } catch (NotAllowedToSubmitException $e) {
            // Pass
            $this->assertEquals('runNotInsideContest', $e->getMessage());
        } finally {
            \OmegaUp\Time::setTimeForTesting($originalTime);
        }
    }

    /**
     * Director extends time to user that joined the contest when it is almost is over
     */
    public function testExtendTimeAtTheEndOfTheContest() {
        // Get a problem
        $problem = ProblemsFactory::createProblem();

        $originalTime = \OmegaUp\Time::get();

        // Create contest with 5 hours and a window length 60 of minutes
        $contest = ContestsFactory::createContest(
            new ContestParams([
                'window_length' => 60,
                'start_time' => $originalTime,
                'finish_time' => $originalTime + 60 * 5 * 60,
            ])
        );

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problem, $contest);

        // Create a contestant
        $contestant = UserFactory::createUser();

        // Add contestant to contest
        ContestsFactory::addUser($contest, $contestant);

        // User joins the contest 4 hours and 30 minutes after it starts
        $updatedTime = $originalTime + 270 * 60;
        \OmegaUp\Time::setTimeForTesting($updatedTime);
        ContestsFactory::openContest($contest, $contestant);
        ContestsFactory::openProblemInContest($contest, $problem, $contestant);

        // User creates a run 20 minutes later, it is ok
        $updatedTime = $updatedTime + 20 * 60;
        \OmegaUp\Time::setTimeForTesting($updatedTime);
        $run = RunsFactory::createRun($problem, $contest, $contestant);
        RunsFactory::gradeRun($run, 1.0, 'AC', 10);

        $directorLogin = self::login($contest['director']);

        $identities = ContestController::apiUsers(new \OmegaUp\Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contest['request']['alias'],
        ]));

        // Extend end_time for the user, now should be valid create runs after contest finishes
        ContestController::apiUpdateEndTimeForIdentity(new \OmegaUp\Request([
            'contest_alias' => $contest['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
            'username' => $contestant->username,
            'end_time' => $updatedTime + 30 * 60,
        ]));

        $updatedTime = $updatedTime + 20 * 60;
        \OmegaUp\Time::setTimeForTesting($updatedTime);
        $run = RunsFactory::createRun($problem, $contest, $contestant);
        RunsFactory::gradeRun($run, 1.0, 'AC', 10);
    }

    public function testUpdateDisableWindowLength() {
        // Get a problem
        $problem = ProblemsFactory::createProblem();

        $originalTime = \OmegaUp\Time::get();

        // Create contest with 5 hours and a window length 60 of minutes
        $contest = ContestsFactory::createContest(
            new ContestParams([
                'window_length' => 30,
                'start_time' => $originalTime,
                'finish_time' => $originalTime + 60 * 2 * 60,
            ])
        );

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problem, $contest);

        // Create a contestant
        $contestant = UserFactory::createUser();

        // Add contestant to contest
        ContestsFactory::addUser($contest, $contestant);

        // User joins the contest 10 minutes after it starts
        $updatedTime = $originalTime + 10 * 60;
        \OmegaUp\Time::setTimeForTesting($updatedTime);
        ContestsFactory::openContest($contest, $contestant);
        ContestsFactory::openProblemInContest($contest, $problem, $contestant);

        // User creates a run 20 minutes later, it is ok
        $updatedTime = $updatedTime + 20 * 60;
        \OmegaUp\Time::setTimeForTesting($updatedTime);
        $run = RunsFactory::createRun($problem, $contest, $contestant);
        RunsFactory::gradeRun($run, 1.0, 'AC', 10);

        // User tries to create another run 20 minutes later, it should fail
        $updatedTime = $updatedTime + 20 * 60;
        \OmegaUp\Time::setTimeForTesting($updatedTime);
        try {
            RunsFactory::createRun($problem, $contest, $contestant);
            $this->fail('User should not be able to send runs beause window_length is over');
        } catch (NotAllowedToSubmitException $e) {
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
        $response = ContestController::apiUpdate($r);

        // User tries to create run agaian, it should works fine
        $run = RunsFactory::createRun($problem, $contest, $contestant);
        RunsFactory::gradeRun($run, 1.0, 'AC', 10);

        // User tries to create another run 30 minutes later, it should fail
        $updatedTime = $updatedTime + 30 * 60;
        \OmegaUp\Time::setTimeForTesting($updatedTime);
        try {
            RunsFactory::createRun($problem, $contest, $contestant);
            $this->fail('User should not be able to send runs beause window_length is over');
        } catch (NotAllowedToSubmitException $e) {
            // Pass
            $this->assertEquals('runNotInsideContest', $e->getMessage());
        }

        // Now, director disables window_length
        $r['window_length'] = 0;

        // Call API
        $response = ContestController::apiUpdate($r);

        // Now user can submit run until contest finishes
        $run = RunsFactory::createRun($problem, $contest, $contestant);
        RunsFactory::gradeRun($run, 1.0, 'AC', 10);
    }
}
