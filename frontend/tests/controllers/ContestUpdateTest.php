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
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant
        $contestant = UserFactory::createUser();

        // Create a run
        $runData = RunsFactory::createRun($problemData, $contestData, $contestant);

        // Grade the run
        RunsFactory::gradeRun($runData);

        // Build request
        $directorLogin = self::login($contestData['director']);

        // Call API
        $response = ContestController::apiRuns(new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
        ]));
        $penalty_contestant_start = $response['runs'][0]['penalty'];

        $this->assertEquals(100, $response['runs'][0]['contest_score']);

        // Update penalty type to runtime
        ContestController::apiUpdate(new Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'penalty_type' => 'runtime',
        ]));

        // Call API
        $response = ContestController::apiRuns(new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
        ]));

        $this->assertEquals($response['runs'][0]['penalty'], $response['runs'][0]['runtime']);

        // Update penalty type to none
        ContestController::apiUpdate(new Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'penalty_type' => 'none',
        ]));

        // Call API
        $response = ContestController::apiRuns(new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
        ]));

        $this->assertEquals(0, $response['runs'][0]['penalty']);

        // Update penalty type to contest start
        ContestController::apiUpdate(new Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'penalty_type' => 'contest_start',
        ]));

        // Call API
        $response = ContestController::apiRuns(new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
        ]));

        $this->assertEquals($penalty_contestant_start, $response['runs'][0]['penalty']);
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

        // Create our contestant
        $contestant = UserFactory::createUser();

        // Create a run
        $runData = RunsFactory::createRun($problemData, $contestData, $contestant);

        $directorLogin = self::login($contestData['director']);

        $r = new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
        ]);

        // Explicitly join contest
        ContestController::apiOpen($r);

        $contest = ContestController::apiDetails($r);

        // Create a run
        $runData = RunsFactory::createRun($problemData, $contestData, $contestant);

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
        Time::setTimeForTesting(Time::get() + 700);

        try {
            // Trying to create a run out of contest time
            $runData = RunsFactory::createRun($problemData, $contestData, $contestant);
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
            $response = ContestController::apiUpdate($r);
            $this->fail('Only numbers are allowed in window_length field');
        } catch (InvalidParameterException $e) {
            // Pass
            $this->assertEquals('parameterNotANumber', $e->getMessage());
        }

        $identities = ContestController::apiUsers(new Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]));

        $index = array_search($contestant->username, array_column($identities['users'], 'username'));

        // Extend end_time for an indentity
        ContestController::apiUpdateEndTimeForIdentity(new Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $directorLogin->auth_token,
            'username' => $contestant->username,
            'end_time' => strtotime($identities['users'][$index]['access_time']) + 60 * 60,
        ]));

        $identities = ContestController::apiUsers(new Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]));

        foreach ($identities['users'] as $identity) {
            if ($identity['username'] == $contestant->username) {
                // Identity with extended time
                $this->assertEquals($identity['end_time'], strtotime($identity['access_time']) + 60 * 60);
            } else {
                // Other identities keep end time with window length
                $this->assertEquals($identity['end_time'], strtotime($identity['access_time']) + $windowLength * 60);
            }
        }
    }
}
