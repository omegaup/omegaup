<?php

/**
 * Description of CreateRun
 *
 * @author joemmanuel
 */

class RunCreateTest extends OmegaupTestCase {
    private $contestData;
    private $contestant;

    /**
     * Prepares the context to submit a run to a problem. Creates the contest,
     * problem and opens them.
     *
     * @return Request
     */
    private function setValidRequest($contest_public = 1) {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $this->contestData = ContestsFactory::createContest(null, $contest_public);

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $this->contestData);

        // Create our contestant
        $this->contestant = UserFactory::createUser();

        // If the contest is private, add the user
        if ($contest_public === 0) {
            ContestsFactory::addUser($this->contestData, $this->contestant);
        }

        // Our contestant has to open the contest before sending a run
        ContestsFactory::openContest($this->contestData, $this->contestant);

        // Then we need to open the problem
        ContestsFactory::openProblemInContest($this->contestData, $problemData, $this->contestant);

        // Create an empty request
        $login = self::login($this->contestant);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $this->contestData['request']['alias'],
            'problem_alias' => $problemData['request']['alias'],
            'language' => 'c',
            'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
        ]);

        return $r;
    }

    /**
     * Validate a run
     *
     * @param type $r
     * @param type $response
     */
    private function assertRun($r, $response) {
        // Validate
        $this->assertEquals('ok', $response['status']);
        $this->assertArrayHasKey('guid', $response);

        // Get run from DB
        $run = RunsDAO::getByAlias($response['guid']);
        $this->assertNotNull($run);

        // Get contest from DB to check times with respect to contest start
        $contest = ContestsDAO::getByAlias($r['contest_alias']);

        // Validate data
        $this->assertEquals($r['language'], $run->language);
        $this->assertNotNull($run->guid);

        // Validate file created
        $filename = RunController::getSubmissionPath($run);
        $this->assertFileExists($filename);
        $fileContent = file_get_contents($filename);
        $this->assertEquals($r['source'], $fileContent);

        // Validate defaults
        $this->assertEquals('new', $run->status);
        $this->assertEquals(0, $run->runtime);
        $this->assertEquals(0, $run->memory);
        $this->assertEquals(0, $run->score);
        $this->assertEquals(0, $run->contest_score);

        $logs = SubmissionLogDAO::search([
            'run_id' => $run->run_id
        ]);

        $this->assertEquals(1, count($logs));
        $this->assertEquals(ip2long('127.0.0.1'), $logs[0]->ip);

        if (!is_null($contest)) {
            $this->assertEquals((time() - intval(strtotime($contest->start_time))) / 60, $run->penalty, '', 0.5);
        }

        $this->assertEquals('JE', $run->verdict);
    }

    /**
     * Basic new run test
     */
    public function testNewRunValid() {
        $r = $this->setValidRequest();
        $this->detourGraderCalls();

        // Call API
        $response = RunController::apiCreate($r);

        $this->assertRun($r, $response);

        // Check problem submissions (1)
        $problem = ProblemsDAO::getByAlias($r['problem_alias']);
        $this->assertEquals(1, $problem->submissions);
    }

    /**
     * Cannot submit run when contest ended
     *
     * @expectedException NotAllowedToSubmitException
     */
    public function testRunWhenContestExpired() {
        $r = $this->setValidRequest();

        // Manually expire the contest
        $contest = ContestsDAO::getByAlias($r['contest_alias']);
        $contest->finish_time = Utils::GetTimeFromUnixTimestamp(Utils::GetPhpUnixTimestamp() - 1);
        ContestsDAO::save($contest);

        // Call API
        RunController::apiCreate($r);
    }

    /**
     * Test a valid submission to a private contest
     */
    public function testRunToValidPrivateContest() {
        $r = $this->setValidRequest(0 /* private contest */);
        $this->detourGraderCalls();

        // Call API
        $response = RunController::apiCreate($r);

        // Validate
        $this->assertEquals('ok', $response['status']);
        $this->assertArrayHasKey('guid', $response);
    }

    /**
     * Test a invalid submission to a private contest
     *
     * @expectedException NotAllowedToSubmitException
     */
    public function testRunPrivateContestWithUserNotRegistred() {
        $r = $this->setValidRequest(0 /* private contest */);

        // Create a second user not regitered to private contest
        $contestant2 = UserFactory::createUser();

        // Log in this second user
        $login = self::login($contestant2);
        $r['auth_token'] = $login->auth_token;

        // Call API
        RunController::apiCreate($r);
    }

    /**
     * Cannot submit run when contest not started yet
     *
     * @expectedException NotAllowedToSubmitException
     */
    public function testRunWhenContestNotStarted() {
        $r = $this->setValidRequest();

        // Manually expire contest
        $contest = ContestsDAO::getByAlias($r['contest_alias']);
        $contest->start_time = Utils::GetTimeFromUnixTimestamp(Utils::GetPhpUnixTimestamp() + 10);
        ContestsDAO::save($contest);

        // Call API
        RunController::apiCreate($r);
    }

    /**
     * Test that a user cannot submit once he has already submitted something
     * and the submissions gap time has not expired
     *
     * @expectedException NotAllowedToSubmitException
     */
    public function testInvalidRunInsideSubmissionsGap() {
        // Set the context
        $r = $this->setValidRequest();
        $this->detourGraderCalls();

        // Set submissions gap of 20 seconds
        $contest = ContestsDAO::getByAlias($r['contest_alias']);
        $contest->submissions_gap = 20;
        ContestsDAO::save($contest);

        // Call API
        $response = RunController::apiCreate($r);

        // Validate the run
        $this->assertRun($r, $response);

        // Send a second run. This one should fail
        $response = RunController::apiCreate($r);
    }

    /**
     * Submission gap is per problem, not per contest
     */
    public function testSubmissionGapIsPerProblem() {
        // Set the context
        $r = $this->setValidRequest();

        // Prepare the Grader mock, validate that grade is called 2 times
        // (we will use 2 problems for this test)
        $this->detourGraderCalls($this->exactly(2));

        // Add a second problem to the contest
        $problemData2 = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData2, $this->contestData);

        // Set submissions gap of 20 seconds
        $contest = ContestsDAO::getByAlias($r['contest_alias']);
        $contest->submissions_gap = 20;
        ContestsDAO::save($contest);

        // Call API, send a run for the first problem
        $response = RunController::apiCreate($r);
        $this->assertRun($r, $response);

        // Set the second problem as the target
        $r['problem_alias'] = $problemData2['request']['alias'];

        // Send a run to the 2nd problem
        $response = RunController::apiCreate($r);
        $this->assertRun($r, $response);
    }

    /**
     * Test that grabbing a problem from a contest A and using it as
     * parameter of contest B does not work
     *
     * @expectedException InvalidParameterException
     */
    public function testInvalidContestProblemCombination() {
        // Set the context for the first contest
        $r1 = $this->setValidRequest();

        // Set the context for the second contest
        $r2 = $this->setValidRequest();

        // Mix problems
        $r2['problem_alias'] = $r1['problem_alias'];

        // Call API
        $response = RunController::apiCreate($r2);
    }

    /**
     * Test that a run can't be send with missing parameters
     */
    public function testMissingParameters() {
        // Set the context for the first contest
        $original_r = $this->setValidRequest();
        $this->detourGraderCalls($this->any());

        $needed_keys = [
            'problem_alias',
            'contest_alias',
            'language',
            'source'
        ];

        foreach ($needed_keys as $key) {
            // Make a copy of the array
            $r = $original_r;

            // Erase the key
            unset($r[$key]);

            try {
                // Call API
                $response = RunController::apiCreate($r);
            } catch (InvalidParameterException $e) {
                // The API should throw this exception, in this case
                // we continue
                continue;
            }

            $this->fail('apiCreate did not return expected exception');
        }
    }

    /**
     * Test valid window length
     */
    public function testNewRunInWindowLengthPublicContest() {
        // Set the context for the first contest
        $r = $this->setValidRequest();
        $this->detourGraderCalls();

        // Alter Contest window length to 20
        // This means: once I started the contest, I have 20 more mins
        // to finish it.
        $contest = ContestsDAO::getByAlias($r['contest_alias']);
        $contest->window_length = 20;
        ContestsDAO::save($contest);

        // Call API
        $response = RunController::apiCreate($r);

        $this->assertRun($r, $response);
    }

    /**
     * Test sending runs after the window length expired
     *
     * @expectedException NotAllowedToSubmitException
     */
    public function testNewRunOutWindowLengthPublicContest() {
        // Set the context for the first contest
        $r = $this->setValidRequest();

        // Alter Contest window length to 20
        // This means: once I started the contest, I have 20 more mins
        // to finish it.
        $contest = ContestsDAO::getByAlias($r['contest_alias']);
        $contest->window_length = 20;
        ContestsDAO::save($contest);

         // Alter first access time of our contestant such that he started
        // 21 minutes ago, this is, window length has expired by 1 minute
        $problemset_user = ProblemsetUsersDAO::getByPK($this->contestant->user_id, $contest->problemset_id);
        $problemset_user->access_time = date('Y-m-d H:i:s', time() - 21 * 60); //Window length is in minutes
        ProblemsetUsersDAO::save($problemset_user);

        // Call API
        RunController::apiCreate($r);
    }

    /**
     * Admin is god, is able to submit even when contest has not started yet
     */
    public function testRunWhenContestNotStartedForContestDirector() {
        // Set the context for the first contest
        $r = $this->setValidRequest();
        $this->detourGraderCalls();

        // Log as contest director
        $login = self::login($this->contestData['director']);
        $r['auth_token'] = $login->auth_token;

        // Manually set the contest	start 10 mins in the future
        $contest = ContestsDAO::getByAlias($r['contest_alias']);
        $contest->start_time = Utils::GetTimeFromUnixTimestamp(Utils::GetPhpUnixTimestamp() + 10);
        ContestsDAO::save($contest);

        // Call API
        $response = RunController::apiCreate($r);

        $this->assertRun($r, $response);
    }

    /**
     * Contest director is god, should be able to submit whenever he wants
     * for testing purposes
     */
    public function testInvalidRunInsideSubmissionsGapForContestDirector() {
        // Set the context for the first contest
        $r = $this->setValidRequest();
        $this->detourGraderCalls($this->exactly(2));

        // Log as contest director
        $login = self::login($this->contestData['director']);
        $r['auth_token'] = $login->auth_token;

        // Set submissions gap of 20 seconds
        $contest = ContestsDAO::getByAlias($r['contest_alias']);
        $contest->submissions_gap = 20;
        ContestsDAO::save($contest);

        // Call API
        $response = RunController::apiCreate($r);

        // Validate the run
        $this->assertRun($r, $response);

        // Send a second run. This one should not fail
        $response = RunController::apiCreate($r);

        // Validate the run
        $this->assertRun($r, $response);
    }

    /**
     * User can send runs to a public problem, regardless of it being
     * in a contest
     */
    public function testRunToPublicProblemWhileInsideAContest()
    {
        // Create public problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest(null, 1);

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant
        $this->contestant = UserFactory::createUser();

        // Create an empty request
        $login = self::login($this->contestant);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => '', // Not inside a contest
            'problem_alias' => $problemData['request']['alias'],
            'language' => 'c',
            'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
        ]);

        // Call API
        $this->detourGraderCalls($this->exactly(1));
        $response = RunController::apiCreate($r);

        // Validate the run
        $this->assertRun($r, $response);
    }

    /**
     * Languages must be validated against the problem's allowed languages.
     *
     * @expectedException InvalidParameterException
     */
    public function testRunInvalidProblemLanguage() {
        // Create public problem without C as an option.
        $problemData = ProblemsFactory::createProblem(null, null, 1, null, 'cpp');

        // Create our contestant
        $contestant = UserFactory::createUser();

        // Create an empty request
        $login = self::login($contestant);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['alias'],
            'language' => 'c',
            'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
        ]);

        // Call API
        $response = RunController::apiCreate($r);
    }

    /**
     * Languages must be validated against the problem's allowed languages.
     *
     * @expectedException InvalidParameterException
     */
    public function testRunInvalidContestLanguage() {
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest(null, 1, null, 'cpp');

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant
        $contestant = UserFactory::createUser();

        // Our contestant has to open the contest before sending a run
        ContestsFactory::openContest($contestData, $contestant);

        // Then we need to open the problem
        ContestsFactory::openProblemInContest($contestData, $problemData, $contestant);

        $login = self::login($contestant);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'problem_alias' => $problemData['request']['alias'],
            'language' => 'c',
            'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
        ]);

        // Call API
        $response = RunController::apiCreate($r);
    }

    /**
     * User cannot send runs to a private problem, regardless of it being
     * in a contest
     *
     * @expectedException NotAllowedToSubmitException
     */
    public function testRunToPrivateProblemWhileInsideAPublicContest()
    {
        // Get a contest
        $contestData = ContestsFactory::createContest(null, 1 /* public */);

        // Create public problem
        $problemData = ProblemsFactory::createProblem(null, null, 0 /* private */, $contestData['director']);

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant
        $this->contestant = UserFactory::createUser();

        $login = self::login($this->contestant);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => '', // Not inside a contest
            'problem_alias' => $problemData['request']['alias'],
            'language' => 'c',
            'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
        ]);

        // Call API
        $response = RunController::apiCreate($r);
    }

    /**
     * User should wait between consecutive runs.
     *
     * @expectedException NotAllowedToSubmitException
     */
    public function testRunsToPublicProblemInsideSubmissionGap() {
        $originalGap = RunController::$defaultSubmissionGap;
        RunController::$defaultSubmissionGap = 60;
        try {
            // Create public problem
            $problemData = ProblemsFactory::createProblem();

            // Create our contestant
            $this->contestant = UserFactory::createUser();

            // Create an empty request
            $r = new Request();

            $login = self::login($this->contestant);
            $r = new Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => '', // Not inside a contest
                'problem_alias' => $problemData['request']['alias'],
                'language' => 'c',
                'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
            ]);

            // Call API
            $this->detourGraderCalls($this->exactly(1));
            $response = RunController::apiCreate($r);

            // Validate the run
            $this->assertRun($r, $response);

            // Call API
            $response = RunController::apiCreate($r);
        } finally {
            RunController::$defaultSubmissionGap = $originalGap;
        }
    }
}
