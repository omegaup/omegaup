<?php

/**
 * Description of CreateRun
 *
 * @author joemmanuel
 */

class RunCreateTest extends \OmegaUp\Test\ControllerTestCase {
    private $contestData;
    private $contestant;
    private $contestantIdentity;
    private $student;
    private $studentIdentity;
    private $non_student;
    private $non_student_identity;

    /**
     * Prepares the context to submit a run to a problem. Creates the contest,
     * problem and opens them.
     *
     * @return \OmegaUp\Request
     */
    private function setValidRequest(
        ?\OmegaUp\Test\Factories\ContestParams $contestParams = null
    ): \OmegaUp\Request {
        if (is_null($contestParams)) {
            $contestParams = new \OmegaUp\Test\Factories\ContestParams();
        }
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a contest
        $this->contestData = \OmegaUp\Test\Factories\Contest::createContest(
            $contestParams
        );

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $this->contestData
        );

        // Create our contestant
        ['user' => $this->contestant, 'identity' => $this->contestantIdentity] = \OmegaUp\Test\Factories\User::createUser();

        // If the contest is private, add the user
        if ($contestParams->admissionMode === 'private') {
            \OmegaUp\Test\Factories\Contest::addUser(
                $this->contestData,
                $this->contestantIdentity
            );
        }

        // Our contestant has to open the contest before sending a run
        \OmegaUp\Test\Factories\Contest::openContest(
            $this->contestData,
            $this->contestantIdentity
        );

        // Then we need to open the problem
        \OmegaUp\Test\Factories\Contest::openProblemInContest(
            $this->contestData,
            $problemData,
            $this->contestantIdentity
        );

        // Create an empty request
        $login = self::login($this->contestantIdentity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $this->contestData['request']['alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
            'language' => 'c11-gcc',
            'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
        ]);

        return $r;
    }

    /**
     * @return \OmegaUp\Request
     */
    private function setUpAssignment($startTimeDelay = 0) {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Create course and add user as a student
        $this->courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            null,
            null,
            false,
            'no',
            'false',
            $startTimeDelay
        );

        // Student user
        ['user' => $this->student, 'identity' => $this->studentIdentity] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $this->courseData,
            $this->studentIdentity
        );

        // Non-student user
        ['user' => $this->non_student, 'identity' => $this->non_student_identity] = \OmegaUp\Test\Factories\User::createUser();

        // Get the actual DB entries for later
        $this->course = \OmegaUp\DAO\Courses::getByAlias(
            $this->courseData['course_alias']
        );
        $this->assignment = \OmegaUp\DAO\Assignments::getByAliasAndCourse(
            $this->courseData['assignment_alias'],
            $this->course->course_id
        );

        $adminLogin = self::login($this->courseData['admin']);

        // Add the problem to the contest
        \OmegaUp\Controllers\Course::apiAddProblem(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $this->courseData['course_alias'],
            'assignment_alias' => $this->assignment->alias,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));

        // Create an empty request
        $r = new \OmegaUp\Request([
            'problemset_id' => $this->assignment->problemset_id,
            'problem_alias' => $problemData['request']['problem_alias'],
            'language' => 'c11-gcc',
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
        $this->assertArrayHasKey('guid', $response);

        // Get submissionn from DB
        $submission = \OmegaUp\DAO\Submissions::getByGuid($response['guid']);
        $this->assertNotNull($submission);

        // Get contest from DB to check times with respect to contest start
        $contest = \OmegaUp\DAO\Contests::getByAlias($r['contest_alias'] ?? '');

        // Validate data
        $this->assertEquals($r['language'], $submission->language);
        $this->assertNotNull($submission->guid);

        // Validate file created
        $fileContent = \OmegaUp\Controllers\Submission::getSource(
            $submission->guid
        );
        $this->assertEquals($r['source'], $fileContent);

        // Validate defaults
        $run = \OmegaUp\DAO\Runs::getByPK($submission->current_run_id);
        $this->assertEquals('new', $run->status);
        $this->assertEquals(0, $run->runtime);
        $this->assertEquals(0, $run->memory);
        $this->assertEquals(0, $run->score);
        $this->assertEquals(0, $run->contest_score);

        // Validate next submission timestamp
        $submission_gap = isset(
            $contest->submissions_gap
        ) ? $contest->submissions_gap : \OmegaUp\Controllers\Run::$defaultSubmissionGap;
        $this->assertEquals(
            \OmegaUp\Time::get() + $submission_gap,
            $response['nextSubmissionTimestamp']
        );

        $log = \OmegaUp\DAO\SubmissionLog::getByPK($submission->submission_id);

        $this->assertNotNull($log);
        $this->assertEquals(ip2long('127.0.0.1'), $log->ip);

        if (!is_null($contest)) {
            $this->assertEquals(
                (\OmegaUp\Time::get() - $contest->start_time) / 60,
                $run->penalty,
                '',
                0.5
            );
        }

        $this->assertEquals('JE', $run->verdict);
    }

    /**
     * Basic new run test
     */
    public function testNewRunValid() {
        $r = $this->setValidRequest();
        $detourGrader = new \OmegaUp\Test\ScopedGraderDetour();

        // Call API
        $response = \OmegaUp\Controllers\Run::apiCreate($r);

        $this->assertRun($r, $response);

        // Check problem submissions (1)
        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        $this->assertEquals(1, $problem->submissions);
    }

    /**
     * Submission should have same school_id than the submitter
     */
    public function testSubmissionSchool() {
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        $submission = \OmegaUp\DAO\Submissions::getByGuid(
            $runData['response']['guid']
        );
        // school_id on Submission, must be null
        $this->assertNull($submission->school_id);

        // Add user's school
        $login = self::login($identity);
        $school = SchoolsFactory::createSchool();
        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'school_id' => $school['school']->school_id,
        ]));
        $identityUpdated = \OmegaUp\DAO\Identities::getByPK(
            $identity->identity_id
        );

        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        $submission = \OmegaUp\DAO\Submissions::getByGuid(
            $runData['response']['guid']
        );

        // school_id from identity school should be equal to submission's school_id
        $schoolId = null;
        if (!is_null($identityUpdated->current_identity_school_id)) {
            $identitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
                $identityUpdated->current_identity_school_id
            );
            if (!is_null($identitySchool)) {
                $schoolId = $identitySchool->school_id;
            }
        }

        $this->assertEquals(
            $schoolId,
            $submission->school_id
        );
    }

    /**
     * Cannot submit run when contest ended
     */
    public function testRunWhenContestExpired() {
        $startTime = \OmegaUp\Time::get() - 60 * 60;
        $r = $this->setValidRequest(new \OmegaUp\Test\Factories\ContestParams([
            'startTime' => $startTime,
            'finishTime' => $startTime + 2 * 60 * 60
        ]));

        // Now is one second after contest finishes
        \OmegaUp\Time::setTimeForTesting($startTime + (2 * 60 * 60) + 1);

        try {
            // Call API
            \OmegaUp\Controllers\Run::apiCreate($r);
            $this->fail(
                'api should have not created run, because contest has expired.'
            );
        } catch (\OmegaUp\Exceptions\NotAllowedToSubmitException $e) {
            $this->assertEquals('runNotInsideContest', $e->getMessage());
        }
    }

    /**
     * Test a valid submission to a private contest
     */
    public function testRunToValidPrivateContest() {
        $r = $this->setValidRequest(new \OmegaUp\Test\Factories\ContestParams([
            'admissionMode' => 'private'
        ]));
        $detourGrader = new \OmegaUp\Test\ScopedGraderDetour();

        // Call API
        $response = \OmegaUp\Controllers\Run::apiCreate($r);

        // Validate
        $this->assertArrayHasKey('guid', $response);
    }

    /**
     * Test a invalid submission to a private contest
     *
     * @expectedException \OmegaUp\Exceptions\NotAllowedToSubmitException
     */
    public function testRunPrivateContestWithUserNotRegistred() {
        $r = $this->setValidRequest(new \OmegaUp\Test\Factories\ContestParams([
            'admissionMode' => 'private'
        ]));

        // Create a second user not regitered to private contest
        ['user' => $contestant2, 'identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();

        // Log in this second user
        $login = self::login($identity2);
        $r['auth_token'] = $login->auth_token;

        // Call API
        \OmegaUp\Controllers\Run::apiCreate($r);
    }

    /**
     * Cannot submit run when contest not started yet
     */
    public function testRunWhenContestNotStarted() {
        $startTime = \OmegaUp\Time::get();
        $r = $this->setValidRequest(new \OmegaUp\Test\Factories\ContestParams([
            'startTime' => $startTime,
            'finishTime' => $startTime + 2 * 60 * 60
        ]));

        // get back in time ten minutes before Contest starts
        \OmegaUp\Time::setTimeForTesting($startTime - (10 * 60));

        try {
            // Call API
            \OmegaUp\Controllers\Run::apiCreate($r);
            $this->fail(
                'api should have not created run, because contest has not started yet.'
            );
        } catch (\OmegaUp\Exceptions\NotAllowedToSubmitException $e) {
            $this->assertEquals('runNotInsideContest', $e->getMessage());
        }
    }

    /**
     * Test that a user cannot submit once he has already submitted something
     * and the submissions gap time has not expired
     *
     * @expectedException \OmegaUp\Exceptions\NotAllowedToSubmitException
     */
    public function testInvalidRunInsideSubmissionsGap() {
        // Set the context
        $r = $this->setValidRequest();
        $detourGrader = new \OmegaUp\Test\ScopedGraderDetour();

        // Set submissions gap of 20 seconds
        $contest = \OmegaUp\DAO\Contests::getByAlias($r['contest_alias']);
        $contest->submissions_gap = 20;
        \OmegaUp\DAO\Contests::update($contest);

        // Call API
        $response = \OmegaUp\Controllers\Run::apiCreate($r);

        // Validate the run
        $this->assertRun($r, $response);

        // Send a second run. This one should fail
        $response = \OmegaUp\Controllers\Run::apiCreate($r);
    }

    /**
     * Submission gap is per problem, not per contest
     */
    public function testSubmissionGapIsPerProblem() {
        // Set the context
        $r = $this->setValidRequest();

        // Prepare the Grader mock, validate that grade is called 2 times
        $detourGrader = new \OmegaUp\Test\ScopedGraderDetour();

        // Add a second problem to the contest
        $problemData2 = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData2,
            $this->contestData
        );

        // Set submissions gap of 20 seconds
        $contest = \OmegaUp\DAO\Contests::getByAlias($r['contest_alias']);
        $contest->submissions_gap = 20;
        \OmegaUp\DAO\Contests::update($contest);

        // Call API, send a run for the first problem
        $response = \OmegaUp\Controllers\Run::apiCreate($r);
        $this->assertRun($r, $response);

        // Set the second problem as the target
        $r['problem_alias'] = $problemData2['request']['problem_alias'];

        // Send a run to the 2nd problem
        $response = \OmegaUp\Controllers\Run::apiCreate($r);
        $this->assertRun($r, $response);
    }

    /**
     * Test that grabbing a problem from a contest A and using it as
     * parameter of contest B does not work
     *
     * @expectedException \OmegaUp\Exceptions\InvalidParameterException
     */
    public function testInvalidContestProblemCombination() {
        // Set the context for the first contest
        $r1 = $this->setValidRequest();

        // Set the context for the second contest
        $r2 = $this->setValidRequest();

        // Mix problems
        $r2['problem_alias'] = $r1['problem_alias'];

        // Call API
        $response = \OmegaUp\Controllers\Run::apiCreate($r2);
    }

    /**
     * Test that a run can't be send with missing parameters
     */
    public function testMissingParameters() {
        // Set the context for the first contest
        $original_r = $this->setValidRequest();
        $detourGrader = new \OmegaUp\Test\ScopedGraderDetour();

        $needed_keys = [
            'problem_alias',
            'contest_alias',
            'language',
            'source',
        ];

        foreach ($needed_keys as $key) {
            // Make a copy of the array
            $r = $original_r;

            // Erase the key
            unset($r[$key]);

            try {
                \OmegaUp\Controllers\Run::apiCreate($r);
                $this->fail('apiCreate did not return expected exception');
            } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
                $this->assertEquals('parameterEmpty', $e->getMessage());
            }
        }
    }

    /**
     * Test valid window length
     */
    public function testNewRunInWindowLengthPublicContest() {
        // Set the context for the first contest, with 20 minutes of window length
        $r = $this->setValidRequest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['windowLength' => 20]
            )
        );
        $detourGrader = new \OmegaUp\Test\ScopedGraderDetour();

        // Call API
        $response = \OmegaUp\Controllers\Run::apiCreate($r);

        $this->assertRun($r, $response);
    }

    /**
     * Test sending runs after the window length expired
     */
    public function testNewRunOutWindowLengthPublicContest() {
        // Set the context for the first contest, with 20 minutes of window length
        $r = $this->setValidRequest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['windowLength' => 20]
            )
        );

        // Alter time for testing such that contestant started
        // 21 minutes ago, this is, window length has expired by 1 minute
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + (21 * 60));

        try {
            // Call API
            \OmegaUp\Controllers\Run::apiCreate($r);
            $this->fail(
                'Contestant should not submitted a run because windows length has expired'
            );
        } catch (\OmegaUp\Exceptions\NotAllowedToSubmitException $e) {
            $this->assertEquals('runNotInsideContest', $e->getMessage());
        }
    }

    /**
     * Admin is god, is able to submit even when contest has not started yet
     */
    public function testRunWhenContestNotStartedForContestDirector() {
        // Set the context for the first contest
        $r = $this->setValidRequest();
        $detourGrader = new \OmegaUp\Test\ScopedGraderDetour();

        // Log as contest director
        $login = self::login($this->contestData['director']);
        $r['auth_token'] = $login->auth_token;

        // Manually set the contest start 10 mins in the future
        $contest = \OmegaUp\DAO\Contests::getByAlias($r['contest_alias']);
        $contest->start_time = \OmegaUp\Time::get() + 10;
        \OmegaUp\DAO\Contests::update($contest);

        // Call API
        $response = \OmegaUp\Controllers\Run::apiCreate($r);

        $this->assertRun($r, $response);
    }

    /**
     * Admin is god, but even he is unable to submit even when contest has ended
     *
     * @expectedException \OmegaUp\Exceptions\NotAllowedToSubmitException
     */
    public function testRunWhenContestEndedForContestDirector() {
        $startTime = \OmegaUp\Time::get() - 60 * 60;
        $r = $this->setValidRequest(new \OmegaUp\Test\Factories\ContestParams([
            'startTime' => $startTime,
            'finishTime' => $startTime + 2 * 60 * 60
        ]));

        // Now is one second after contest finishes
        \OmegaUp\Time::setTimeForTesting($startTime + (2 * 60 * 60) + 1);

        // Log as contest director
        $login = self::login($this->contestData['director']);
        $r['auth_token'] = $login->auth_token;

        // Call API
        $response = \OmegaUp\Controllers\Run::apiCreate($r);

        $this->assertRun($r, $response);
    }

    /**
     * Contest director is god, should be able to submit whenever he wants
     * for testing purposes
     */
    public function testInvalidRunInsideSubmissionsGapForContestDirector() {
        // Set the context for the first contest
        $r = $this->setValidRequest();
        $detourGrader = new \OmegaUp\Test\ScopedGraderDetour();

        // Log as contest director
        $login = self::login($this->contestData['director']);
        $r['auth_token'] = $login->auth_token;

        // Set submissions gap of 20 seconds
        $contest = \OmegaUp\DAO\Contests::getByAlias($r['contest_alias']);
        $contest->submissions_gap = 20;
        \OmegaUp\DAO\Contests::update($contest);

        // Call API
        $response = \OmegaUp\Controllers\Run::apiCreate($r);

        // Validate the run
        $this->assertRun($r, $response);

        // Send a second run. This one should not fail
        $response = \OmegaUp\Controllers\Run::apiCreate($r);

        // Validate the run
        $this->assertRun($r, $response);
    }

    /**
     * User can send runs to a public problem, regardless of it being
     * in a contest
     */
    public function testRunToPublicProblemWhileInsideAContest() {
        // Create public problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create our contestant
        ['user' => $this->contestant, 'identity' => $this->contestantIdentity] = \OmegaUp\Test\Factories\User::createUser();

        // Create an empty request
        $login = self::login($this->contestantIdentity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => '', // Not inside a contest
            'problem_alias' => $problemData['request']['problem_alias'],
            'language' => 'c11-gcc',
            'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
        ]);

        // Call API
        $detourGrader = new \OmegaUp\Test\ScopedGraderDetour();
        $response = \OmegaUp\Controllers\Run::apiCreate($r);

        // Validate the run
        $this->assertRun($r, $response);
    }

    /**
     * Languages must be validated against the problem's allowed languages.
     */
    public function testRunInvalidProblemLanguage() {
        // Create public problem without C as an option.
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 1,
            'languages' => 'cpp11-gcc'
        ]));

        // Create our contestant
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create an empty request
        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\Run::apiCreate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problem_alias' => $problemData['request']['problem_alias'],
                'language' => 'c11-gcc',
                'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
            ]));
            $this->fail('apiCreate did not return expected exception');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals('parameterNotInExpectedSet', $e->getMessage());
        }
    }

    /**
     * Languages must be validated against the problem's allowed languages.
     */
    public function testRunInvalidContestLanguage() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['languages' => ['cpp11-gcc']]
            )
        );

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

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

        $login = self::login($identity);

        // Call API
        try {
            \OmegaUp\Controllers\Run::apiCreate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'problem_alias' => $problemData['request']['problem_alias'],
                'language' => 'c11-gcc',
                'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
            ]));
            $this->fail('apiCreate did not return expected exception');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals('parameterNotInExpectedSet', $e->getMessage());
        }
    }

    /**
     * User cannot send runs to a private problem, regardless of it being
     * in a contest
     *
     * @expectedException \OmegaUp\Exceptions\NotAllowedToSubmitException
     */
    public function testRunToPrivateProblemWhileInsideAPublicContest() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Create public problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 0,
            'author' => $contestData['director']
        ]));

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create our contestant
        ['user' => $this->contestant, 'identity' => $this->contestantIdentity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($this->contestantIdentity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => '', // Not inside a contest
            'problem_alias' => $problemData['request']['problem_alias'],
            'language' => 'c11-gcc',
            'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
        ]);

        // Call API
        $response = \OmegaUp\Controllers\Run::apiCreate($r);
    }

    /**
     * User should wait between consecutive runs.
     *
     * @expectedException \OmegaUp\Exceptions\NotAllowedToSubmitException
     */
    public function testRunsToPublicProblemInsideSubmissionGap() {
        $originalGap = \OmegaUp\Controllers\Run::$defaultSubmissionGap;
        \OmegaUp\Controllers\Run::$defaultSubmissionGap = 60;
        try {
            // Create public problem
            $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

            // Create our contestant
            ['user' => $this->contestant, 'identity' => $this->contestantIdentity] = \OmegaUp\Test\Factories\User::createUser();

            // Create an empty request
            $r = new \OmegaUp\Request();

            $login = self::login($this->contestantIdentity);
            $r = new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => '', // Not inside a contest
                'problem_alias' => $problemData['request']['problem_alias'],
                'language' => 'c11-gcc',
                'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
            ]);

            // Call API
            $detourGrader = new \OmegaUp\Test\ScopedGraderDetour();
            $response = \OmegaUp\Controllers\Run::apiCreate($r);

            // Validate the run
            $this->assertRun($r, $response);

            // Call API
            $response = \OmegaUp\Controllers\Run::apiCreate($r);
        } finally {
            \OmegaUp\Controllers\Run::$defaultSubmissionGap = $originalGap;
        }
    }

    public function testRunWithProblemsetId() {
        $r = $this->setValidRequest();
        $r['problemset_id'] = $this->contestData['contest']->problemset_id;
        unset($r['contest_alias']);

        // Call API
        $response = \OmegaUp\Controllers\Run::apiCreate($r);

        $this->assertRun($r, $response);

        // Check problem submissions (1)
        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        $this->assertEquals(1, $problem->submissions);
    }

    /**
     * Can't set both params at the same time
     *
     * @expectedException \OmegaUp\Exceptions\InvalidParameterException
     */
    public function testRunWithProblemsetIdAndContestAlias() {
        $r = $this->setValidRequest();
        $r['problemset_id'] = $this->contestData['contest']->problemset_id;

        // Call API
        $response = \OmegaUp\Controllers\Run::apiCreate($r);
    }

    /**
     * Run from a student.
     */
    public function testRunInAssignmentFromStudent() {
        $r = $this->setUpAssignment();
        $login = self::login($this->studentIdentity);
        $r['auth_token'] = $login->auth_token;

        // Call API
        $response = \OmegaUp\Controllers\Run::apiCreate($r);
    }

    /**
     * Can't submit by a user that is not enrolled in a course.
     *
     * @expectedException \OmegaUp\Exceptions\NotAllowedToSubmitException
     */
    public function testRunInAssignmentFromNonStudent() {
        $r = $this->setUpAssignment();
        $login = self::login($this->non_student_identity);
        $r['auth_token'] = $login->auth_token;

        // Call API
        $response = \OmegaUp\Controllers\Run::apiCreate($r);
    }

    /**
     * Run from a student before assignment opens.
     *
     * @expectedException \OmegaUp\Exceptions\NotAllowedToSubmitException
     */
    public function testRunInAssignmentFromStudentBeforeStart() {
        $r = $this->setUpAssignment(10);

        $login = self::login($this->studentIdentity);
        $r['auth_token'] = $login->auth_token;

        // Call API
        $response = \OmegaUp\Controllers\Run::apiCreate($r);
    }

    /**
     * Run from a student after the deadline passed.
     *
     * @expectedException \OmegaUp\Exceptions\NotAllowedToSubmitException
     */
    public function testRunInAssignmentFromStudentAfterDeadline() {
        $r = $this->setUpAssignment();

        $adminLogin = self::login($this->courseData['admin']);
        \OmegaUp\Controllers\Course::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => $this->courseData['request']['course']->name,
            'alias' => $this->courseData['request']['course']->alias,
            'course_alias' => $this->courseData['request']['course']->alias,
            'description' => $this->courseData['request']['course']->description,
            'start_time' => \OmegaUp\Time::get() - 10,
            'finish_time' => \OmegaUp\Time::get() - 1,
        ]));
        // Creating a submission in the future
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60 * 60);

        $login = self::login($this->studentIdentity);
        $r['auth_token'] = $login->auth_token;

        // Call API
        $response = \OmegaUp\Controllers\Run::apiCreate($r);
    }

    /**
     * Should not allow sending to banned public problems.
     * @expectedException \OmegaUp\Exceptions\NotFoundException
     */
    public function testShouldNotAllowToSendPubliclyBannedProblems() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $login = self::login($problemData['author']);
        $problem = $problemData['problem'];

        // Change the visibility to public banned.
        \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
             'auth_token' => $login->auth_token,
             'problem_alias' => $problem->alias,
             'visibility' => \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_BANNED,
             'message' => 'public_banned',
        ]));

        // Call API
        \OmegaUp\Controllers\Run::apiCreate(new \OmegaUp\Request([
             'auth_token' => $login->auth_token,
             'problem_alias' => $problem->alias,
             'language' => 'c11-gcc',
             'source'   => "#include <stdio.h>\nint main() {printf(\"3\"); return 0; }",
        ]));
    }

     /**
     * Should not allow sending to privately banned problems.
     * @expectedException \OmegaUp\Exceptions\NotFoundException
     */
    public function testShouldNotAllowToSendPrivatelyBannedProblems() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $login = self::login($problemData['author']);
        $problem = $problemData['problem'];

        // Change the visibility to private banned.
        \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
             'auth_token' => $login->auth_token,
             'problem_alias' => $problem->alias,
             'visibility' => \OmegaUp\ProblemParams::VISIBILITY_PRIVATE_BANNED,
             'message' => 'private_banned',
        ]));

        // Call API
        \OmegaUp\Controllers\Run::apiCreate(new \OmegaUp\Request([
             'auth_token' => $login->auth_token,
             'problem_alias' => $problem->alias,
             'language' => 'c11-gcc',
             'source'   => "#include <stdio.h>\nint main() {printf(\"3\"); return 0; }",
        ]));
    }

    /**
     * User can send runs and view details of it after they have solved it.
     */
    public function testRunDetailsAfterSolving() {
        // Create public problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Create contestant
        ['user' => $contestant, 'identity' => $contestantIdentity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($contestantIdentity);
        $waRunData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $contestantIdentity,
            $login
        );
        \OmegaUp\Test\Factories\Run::gradeRun($waRunData, 0, 'WA', 60);

        // Contestant should be able to view run (but not the run details).
        $this->assertFalse(\OmegaUp\Authorization::isProblemAdmin(
            $contestantIdentity,
            $problemData['problem']
        ));
        $response = \OmegaUp\Controllers\Run::apiDetails(new \OmegaUp\Request([
            'run_alias' => $waRunData['response']['guid'],
            'auth_token' => $login->auth_token,
        ]));
        $this->assertFalse(array_key_exists('details', $response));

        $acRunData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $contestantIdentity,
            $login
        );
        \OmegaUp\Test\Factories\Run::gradeRun($acRunData, 1, 'AC', 65);

        // Contestant should be able to view run and details after solving it.
        $response = \OmegaUp\Controllers\Run::apiDetails(new \OmegaUp\Request([
            'run_alias' => $acRunData['response']['guid'],
            'auth_token' => $login->auth_token,
        ]));
        $this->assertTrue(array_key_exists('details', $response));
        $response = \OmegaUp\Controllers\Run::apiDetails(new \OmegaUp\Request([
            'run_alias' => $waRunData['response']['guid'],
            'auth_token' => $login->auth_token,
        ]));
        $this->assertTrue(array_key_exists('details', $response));

        // But having solved a problem does not grant permission to view
        // details to runs that the user would otherwise not had permission to
        // view.
        ['user' => $contestant2, 'identity' => $contestantIdentity2] = \OmegaUp\Test\Factories\User::createUser();
        $login2 = self::login($contestantIdentity2);
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $contestantIdentity2,
            $login2
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData, 1, 'AC', 30);
        try {
            \OmegaUp\Controllers\Run::apiDetails(new \OmegaUp\Request([
                'run_alias' => $runData['response']['guid'],
                'auth_token' => $login->auth_token,
            ]));
            $this->fail(
                'User should not have been able to view another users\' run details'
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            // OK
        }
    }
}
