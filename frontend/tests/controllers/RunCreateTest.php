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
    private function setValidRequest($admission_mode = 'public') {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $this->contestData = ContestsFactory::createContest(new ContestParams(['admission_mode' => $admission_mode]));

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $this->contestData);

        // Create our contestant
        $this->contestant = UserFactory::createUser();

        // If the contest is private, add the user
        if ($admission_mode === 'private') {
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
            'problem_alias' => $problemData['request']['problem_alias'],
            'language' => 'c',
            'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
        ]);

        return $r;
    }

    /**
     * @return Request
     */
    private function setUpAssignment($startTimeDelay = 0) {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Create course and add user as a student
        $this->courseData = CoursesFactory::createCourseWithOneAssignment(null, null, false, 'no', 'false', $startTimeDelay);

        // Student user
        $this->student = UserFactory::createUser();
        CoursesFactory::addStudentToCourse($this->courseData, $this->student);

        // Non-student user
        $this->non_student = UserFactory::createUser();

        // Get the actual DB entries for later
        $this->course = CoursesDAO::getByAlias($this->courseData['course_alias']);
        $this->assignment = AssignmentsDAO::getByAliasAndCourse(
            $this->courseData['assignment_alias'],
            $this->course->course_id
        );

        $adminLogin = self::login($this->courseData['admin']);

        // Add the problem to the contest
        CourseController::apiAddProblem(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $this->courseData['course_alias'],
            'assignment_alias' => $this->assignment->alias,
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));

        // Create an empty request
        $r = new Request([
            'problemset_id' => $this->assignment->problemset_id,
            'problem_alias' => $problemData['request']['problem_alias'],
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

        // Get submissionn from DB
        $submission = SubmissionsDAO::getByGuid($response['guid']);
        $this->assertNotNull($submission);

        // Get contest from DB to check times with respect to contest start
        $contest = ContestsDAO::getByAlias($r['contest_alias']);

        // Validate data
        $this->assertEquals($r['language'], $submission->language);
        $this->assertNotNull($submission->guid);

        // Validate file created
        $fileContent = SubmissionController::getSource($submission->guid);
        $this->assertEquals($r['source'], $fileContent);

        // Validate defaults
        $run = RunsDAO::getByPK($submission->current_run_id);
        $this->assertEquals('new', $run->status);
        $this->assertEquals(0, $run->runtime);
        $this->assertEquals(0, $run->memory);
        $this->assertEquals(0, $run->score);
        $this->assertEquals(0, $run->contest_score);

        // Validate next submission timestamp
        $submission_gap = isset($contest->submissions_gap) ? $contest->submissions_gap : RunController::$defaultSubmissionGap;
        $this->assertEquals(Utils::GetPhpUnixTimestamp() + $submission_gap, $response['nextSubmissionTimestamp']);

        $log = SubmissionLogDAO::getByPK($submission->submission_id);

        $this->assertNotNull($log);
        $this->assertEquals(ip2long('127.0.0.1'), $log->ip);

        if (!is_null($contest)) {
            $this->assertEquals(
                (Utils::GetPhpUnixTimestamp() - intval(strtotime($contest->start_time))) / 60,
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
        $detourGrader = new ScopedGraderDetour();

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
        $r = $this->setValidRequest('private' /* admission mode */);
        $detourGrader = new ScopedGraderDetour();

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
        $r = $this->setValidRequest('private' /* admission mode */);

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
        $detourGrader = new ScopedGraderDetour();

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
        $detourGrader = new ScopedGraderDetour();

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
        $r['problem_alias'] = $problemData2['request']['problem_alias'];

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
        $detourGrader = new ScopedGraderDetour();

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
        $detourGrader = new ScopedGraderDetour();

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
        $problemset_identity = ProblemsetIdentitiesDAO::getByPK($this->contestant->main_identity_id, $contest->problemset_id);
        $problemset_identity->access_time = date('Y-m-d H:i:s', Utils::GetPhpUnixTimestamp() - 21 * 60); //Window length is in minutes
        ProblemsetIdentitiesDAO::save($problemset_identity);

        // Call API
        RunController::apiCreate($r);
    }

    /**
     * Admin is god, is able to submit even when contest has not started yet
     */
    public function testRunWhenContestNotStartedForContestDirector() {
        // Set the context for the first contest
        $r = $this->setValidRequest();
        $detourGrader = new ScopedGraderDetour();

        // Log as contest director
        $login = self::login($this->contestData['director']);
        $r['auth_token'] = $login->auth_token;

        // Manually set the contest start 10 mins in the future
        $contest = ContestsDAO::getByAlias($r['contest_alias']);
        $contest->start_time = Utils::GetTimeFromUnixTimestamp(Utils::GetPhpUnixTimestamp() + 10);
        ContestsDAO::save($contest);

        // Call API
        $response = RunController::apiCreate($r);

        $this->assertRun($r, $response);
    }

    /**
     * Admin is god, but even he is unable to submit even when contest has ended
     *
     * @expectedException NotAllowedToSubmitException
     */
    public function testRunWhenContestEndedForContestDirector() {
        // Set the context for the first contest
        $r = $this->setValidRequest();

        // Log as contest director
        $login = self::login($this->contestData['director']);
        $r['auth_token'] = $login->auth_token;

        // Manually set the contest start 10 mins in the future
        $contest = ContestsDAO::getByAlias($r['contest_alias']);
        $contest->finish_time = Utils::GetTimeFromUnixTimestamp(Utils::GetPhpUnixTimestamp() - 1);
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
        $detourGrader = new ScopedGraderDetour();

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
    public function testRunToPublicProblemWhileInsideAContest() {
        // Create public problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant
        $this->contestant = UserFactory::createUser();

        // Create an empty request
        $login = self::login($this->contestant);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => '', // Not inside a contest
            'problem_alias' => $problemData['request']['problem_alias'],
            'language' => 'c',
            'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
        ]);

        // Call API
        $detourGrader = new ScopedGraderDetour();
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
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => 1,
            'languages' => 'cpp'
        ]));

        // Create our contestant
        $contestant = UserFactory::createUser();

        // Create an empty request
        $login = self::login($contestant);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'problem_alias' => $problemData['request']['problem_alias'],
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
        $contestData = ContestsFactory::createContest(new ContestParams(['languages' => ['cpp']]));

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
            'problem_alias' => $problemData['request']['problem_alias'],
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
    public function testRunToPrivateProblemWhileInsideAPublicContest() {
        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Create public problem
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => 0,
            'author' => $contestData['director']
        ]));

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant
        $this->contestant = UserFactory::createUser();

        $login = self::login($this->contestant);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => '', // Not inside a contest
            'problem_alias' => $problemData['request']['problem_alias'],
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
                'problem_alias' => $problemData['request']['problem_alias'],
                'language' => 'c',
                'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
            ]);

            // Call API
            $detourGrader = new ScopedGraderDetour();
            $response = RunController::apiCreate($r);

            // Validate the run
            $this->assertRun($r, $response);

            // Call API
            $response = RunController::apiCreate($r);
        } finally {
            RunController::$defaultSubmissionGap = $originalGap;
        }
    }

    public function testRunWithProblemsetId() {
        $r = $this->setValidRequest();
        $r['problemset_id'] = $this->contestData['contest']->problemset_id;
        unset($r['contest_alias']);

        // Call API
        $response = RunController::apiCreate($r);

        $this->assertRun($r, $response);

        // Check problem submissions (1)
        $problem = ProblemsDAO::getByAlias($r['problem_alias']);
        $this->assertEquals(1, $problem->submissions);
    }

    /**
     * Can't set both params at the same time
     *
     * @expectedException InvalidParameterException
     */
    public function testRunWithProblemsetIdAndContestAlias() {
        $r = $this->setValidRequest();
        $r['problemset_id'] = $this->contestData['contest']->problemset_id;

        // Call API
        $response = RunController::apiCreate($r);
    }

    /**
     * Run from a student.
     */
    public function testRunInAssignmentFromStudent() {
        $r = $this->setUpAssignment();
        $login = self::login($this->student);
        $r['auth_token'] = $login->auth_token;

        // Call API
        $response = RunController::apiCreate($r);
    }

    /**
     * Can't submit by a user that is not enrolled in a course.
     *
     * @expectedException NotAllowedToSubmitException
     */
    public function testRunInAssignmentFromNonStudent() {
        $r = $this->setUpAssignment();
        $login = self::login($this->non_student);
        $r['auth_token'] = $login->auth_token;

        // Call API
        $response = RunController::apiCreate($r);
    }

    /**
     * Run from a student before assignment opens.
     *
     * @expectedException NotAllowedToSubmitException
     */
    public function testRunInAssignmentFromStudentBeforeStart() {
        $r = $this->setUpAssignment(10);

        $login = self::login($this->student);
        $r['auth_token'] = $login->auth_token;

        // Call API
        $response = RunController::apiCreate($r);
    }

    /**
     * Run from a student after the deadline passed.
     *
     * @expectedException NotAllowedToSubmitException
     */
    public function testRunInAssignmentFromStudentAfterDeadline() {
        $r = $this->setUpAssignment();

        $adminLogin = self::login($this->courseData['admin']);
        CourseController::apiUpdate(new Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => $this->courseData['request']['course']->name,
            'alias' => $this->courseData['request']['course']->alias,
            'course_alias' => $this->courseData['request']['course']->alias,
            'description' => $this->courseData['request']['course']->description,
            'start_time' => Utils::GetPhpUnixTimestamp() - 10,
            'finish_time' => Utils::GetPhpUnixTimestamp() - 1,
        ]));
        // Creating a submission in the future
        Time::setTimeForTesting(Time::get() + 3600);

        $login = self::login($this->student);
        $r['auth_token'] = $login->auth_token;

        // Call API
        $response = RunController::apiCreate($r);
    }

    /**
     * Should not allow sending to banned public problems.
     * @expectedException NotFoundException
     */
    public function testShouldNotAllowToSendPubliclyBannedProblems() {
        $problemData = ProblemsFactory::createProblem();
        $login = self::login($problemData['author']);
        $problem = $problemData['problem'];

        // Change the visibility to public banned.
        ProblemController::apiUpdate(new Request([
             'auth_token' => $login->auth_token,
             'problem_alias' => $problem->alias,
             'visibility' => ProblemController::VISIBILITY_PUBLIC_BANNED,
             'message' => 'public_banned',
        ]));

        // Call API
        RunController::apiCreate(new Request([
             'auth_token' => $login->auth_token,
             'problem_alias' => $problem->alias,
             'language' => 'c',
             'source'   => "#include <stdio.h>\nint main() {printf(\"3\"); return 0; }",
        ]));
    }

     /**
     * Should not allow sending to privately banned problems.
     * @expectedException NotFoundException
     */
    public function testShouldNotAllowToSendPrivatelyBannedProblems() {
        $problemData = ProblemsFactory::createProblem();
        $login = self::login($problemData['author']);
        $problem = $problemData['problem'];

        // Change the visibility to private banned.
        ProblemController::apiUpdate(new Request([
             'auth_token' => $login->auth_token,
             'problem_alias' => $problem->alias,
             'visibility' => ProblemController::VISIBILITY_PRIVATE_BANNED,
             'message' => 'private_banned',
        ]));

        // Call API
        RunController::apiCreate(new Request([
             'auth_token' => $login->auth_token,
             'problem_alias' => $problem->alias,
             'language' => 'c',
             'source'   => "#include <stdio.h>\nint main() {printf(\"3\"); return 0; }",
        ]));
    }

    /**
     * User can send runs and view details of it after they have solved it.
     */
    public function testRunDetailsAfterSolving() {
        // Create public problem
        $problemData = ProblemsFactory::createProblem();

        // Create contestant
        $contestant = UserFactory::createUser();

        $login = self::login($contestant);
        $waRunData = RunsFactory::createRunToProblem($problemData, $contestant, $login);
        RunsFactory::gradeRun($waRunData, 0, 'WA', 60);

        // Contestant should be able to view run (but not the run details).
        $contestantIdentity = IdentityController::resolveIdentity(
            $contestant->username
        );
        $this->assertFalse(Authorization::isProblemAdmin(
            $contestantIdentity,
            $problemData['problem']
        ));
        $response = RunController::apiDetails(new Request([
            'run_alias' => $waRunData['response']['guid'],
            'auth_token' => $login->auth_token,
        ]));
        $this->assertFalse(array_key_exists('details', $response));

        $acRunData = RunsFactory::createRunToProblem($problemData, $contestant, $login);
        RunsFactory::gradeRun($acRunData, 1, 'AC', 65);

        // Contestant should be able to view run and details after solving it.
        $response = RunController::apiDetails(new Request([
            'run_alias' => $acRunData['response']['guid'],
            'auth_token' => $login->auth_token,
        ]));
        $this->assertTrue(array_key_exists('details', $response));
        $response = RunController::apiDetails(new Request([
            'run_alias' => $waRunData['response']['guid'],
            'auth_token' => $login->auth_token,
        ]));
        $this->assertTrue(array_key_exists('details', $response));

        // But having solved a problem does not grant permission to view
        // details to runs that the user would otherwise not had permission to
        // view.
        $contestant2 = UserFactory::createUser();
        $login2 = self::login($contestant2);
        $runData = RunsFactory::createRunToProblem($problemData, $contestant2, $login2);
        RunsFactory::gradeRun($runData, 1, 'AC', 30);
        try {
            RunController::apiDetails(new Request([
                'run_alias' => $runData['response']['guid'],
                'auth_token' => $login->auth_token,
            ]));
            $this->fail('User should not have been able to view another users\' run details');
        } catch (ForbiddenAccessException $e) {
            // OK
        }
    }
}
