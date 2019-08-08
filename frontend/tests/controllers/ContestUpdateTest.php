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
        $r = new Request([
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
        $r = new Request([
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
     * @expectedException InvalidParameterException
     */
    public function testUpdatePrivateContestToPublicWithoutProblems() {
        // Get a contest
        $contestData = ContestsFactory::createContest(new ContestParams(['admission_mode' => 'private']));

        // Update public
        $login = self::login($contestData['director']);
        $r = new Request([
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
        $r = new Request([
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
        $r = new Request([
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
        $r = new Request([
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
     * @expectedException InvalidParameterException
     */
    public function testUpdateContestLengthTooLong() {
        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Update length
        $login = self::login($contestData['director']);
        $r = new Request([
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
        $r = new Request([
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
     * @expectedException InvalidParameterException
     */
    public function testUpdateContestStartWithRuns() {
        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Submit a run
        $this->createRunInContest($contestData);

        // Update length
        $login = self::login($contestData['director']);
        $r = new Request([
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
        $r = new Request([
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
        $r = new Request([
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
        $originalTime = Time::get();

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
            Time::setTimeForTesting($originalTime + 5 * 60);
            $contestant = UserFactory::createUser();
            ContestsFactory::addUser($contestData, $contestant);

            // The problem is opened 5 minutes after contest starts.
            ContestsFactory::openContest($contestData, $contestant);
            ContestsFactory::openProblemInContest($contestData, $problemData, $contestant);

            // The run is sent 10 minutes after contest starts.
            Time::setTimeForTesting($originalTime + 10 * 60);
            $runData = RunsFactory::createRun($problemData, $contestData, $contestant);
            RunsFactory::gradeRun($runData, 1.0, 'AC', 10);
            Time::setTimeForTesting($originalTime);
        }

        $directorLogin = self::login($contestData['director']);

        // Get the original penalty, since the contest was created with the
        // 'contest_start' penalty type.
        $response = ContestController::apiRuns(new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
        ]));
        $this->assertEquals(100, $response['runs'][0]['contest_score']);
        $originalPenalty = $response['runs'][0]['penalty'];

        // Update penalty type to runtime
        {
            ContestController::apiUpdate(new Request([
                'auth_token' => $directorLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'penalty_type' => 'runtime',
            ]));
            $response = ContestController::apiRuns(new Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $directorLogin->auth_token,
            ]));
            $this->assertEquals($response['runs'][0]['penalty'], $response['runs'][0]['runtime']);
        }

        // Update penalty type to none
        {
            ContestController::apiUpdate(new Request([
                'auth_token' => $directorLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'penalty_type' => 'none',
            ]));
            $response = ContestController::apiRuns(new Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $directorLogin->auth_token,
            ]));
            $this->assertEquals(0, $response['runs'][0]['penalty']);
        }

        // Update penalty type to problem open.
        {
            ContestController::apiUpdate(new Request([
                'auth_token' => $directorLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'penalty_type' => 'problem_open',
            ]));
            $response = ContestController::apiRuns(new Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $directorLogin->auth_token,
            ]));
            $this->assertEquals(5, $response['runs'][0]['penalty']);
        }

        // Update penalty type back to contest start.
        {
            ContestController::apiUpdate(new Request([
                'auth_token' => $directorLogin->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'penalty_type' => 'contest_start',
            ]));
            $response = ContestController::apiRuns(new Request([
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
        // Get a contest
        $contestData = ContestsFactory::createContest();

        $directorLogin = self::login($contestData['director']);

        $r = new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
        ]);

        // Explicitly join contest
        ContestController::apiOpen($r);

        $contest = ContestController::apiDetails($r);

        $this->assertNull($contest['window_length'], 'Window length is not setted');

        $r['window_length'] = 0;
        // Call API
        $response = ContestController::apiUpdate($r);

        $contest = ContestController::apiDetails($r);

        $this->assertNull($contest['window_length'], 'Window length is not setted, because 0 is not a valid value');

        $r['window_length'] = 60;
        // Call API
        $response = ContestController::apiUpdate($r);

        $contest = ContestController::apiDetails($r);

        $this->assertEquals(60, $contest['window_length']);

        $r['window_length'] = 'Not valid';

        try {
            // Call API
            $response = ContestController::apiUpdate($r);
            $this->fail('Only numbers are allowed in window_length field');
        } catch (InvalidParameterException $e) {
            // Pass
            $this->assertEquals('parameterNotANumber', $e->getMessage());
        }
    }

    /**
     * Creates a contest with window length, and then update window_length
     * again
     */
    public function testUpdateWindowLengthAtTheEndOfAContest() {
        // Get a problem
        $problem = ProblemsFactory::createProblem();

        $originalTime = Time::get();

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
        Time::setTimeForTesting($updatedTime);
        ContestsFactory::openContest($contest, $contestant);
        $directorLogin = self::login($contest['director']);

        // Update window_length
        $response = ContestController::apiUpdate(new Request([
            'contest_alias' => $contest['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
            'window_length' => 30,
        ]));

        try {
            // 15 minutes later User can not create a run because the contest is over
            $updatedTime = $updatedTime + 15 * 60;
            Time::setTimeForTesting($updatedTime);
            $run = RunsFactory::createRun($problem, $contest, $contestant);
            RunsFactory::gradeRun($run, 1.0, 'AC', 10);
            $this->fail('Contestant should not create a run after contest finishes');
        } catch (NotAllowedToSubmitException $e) {
            // Pass
            $this->assertEquals('runNotInsideContest', $e->getMessage());
        } finally {
            Time::setTimeForTesting($originalTime);
        }
    }
}
