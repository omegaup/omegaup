<?php
class AssignmentProblemsTest extends \OmegaUp\Test\ControllerTestCase {
    public function testAddProblemToAssignment() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // Create a course with an assignment
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $identity,
            $login
        );
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add one problem to the assignment
        $problem = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'public',
            'author' => $identity
        ]), $login);
        $response = \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseAlias,
            $assignmentAlias,
            [$problem]
        )[0];

        $this->assertSame('ok', $response['status']);

        // Assert that the problem was correctly added
        $getAssignmentResponse = \OmegaUp\Controllers\Course::apiAssignmentDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'course' => $courseAlias,
            'assignment' => $assignmentAlias,
        ]));
        $this->assertSame(1, sizeof($getAssignmentResponse['problems']));
        $this->assertSame(
            $problem['problem']->alias,
            $getAssignmentResponse['problems'][0]['alias']
        );
        $this->assertSame(
            $problem['problem']->commit,
            $getAssignmentResponse['problems'][0]['commit']
        );
        $this->assertSame(
            $problem['problem']->current_version,
            $getAssignmentResponse['problems'][0]['version']
        );
    }

    public function testInvitedAdminAddPrivateProblem() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $adminLogin = self::login($identity);

        // Create a course with an assignment
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $identity,
            $adminLogin
        );
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add one problem to the assignment
        $problem = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'visibility' => 'private',
                'author' => $identity
            ]),
            $adminLogin
        );

        [
            'identity' => $invitedAdmin,
        ] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Controllers\Course::apiAddAdmin(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'usernameOrEmail' => $invitedAdmin->username,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        // Invited admin tries to add a private problem into the course
        $invitedAdminLogin = self::login($invitedAdmin);

        try {
            \OmegaUp\Controllers\Course::apiAddProblem(new \OmegaUp\Request([
                'auth_token' => $invitedAdminLogin->auth_token,
                'course_alias' => $courseAlias,
                'assignment_alias' => $assignmentAlias,
                'problem_alias' => $problem['problem']->alias,
                'points' => 100,
            ]));
            $this->fail('It should fail because of the privileges');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame(
                'userNotAllowedToAddPrivateProblem',
                $e->getMessage()
            );
        }

        \OmegaUp\Controllers\Course::apiAddProblem(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseAlias,
                'assignment_alias' => $assignmentAlias,
                'problem_alias' => $problem['problem']->alias,
                'points' => 100,
            ])
        );

        // But the invited admin can update the problem in the same course
        \OmegaUp\Controllers\Course::apiAddProblem(
            new \OmegaUp\Request([
                'auth_token' => $invitedAdminLogin->auth_token,
                'course_alias' => $courseAlias,
                'assignment_alias' => $assignmentAlias,
                'problem_alias' => $problem['problem']->alias,
                'points' => 50,
            ])
        );

        $response = \OmegaUp\Controllers\Course::apiAssignmentDetails(
            new \OmegaUp\Request([
                'course' => $courseAlias,
                'assignment' => $assignmentAlias,
                'auth_token' => $invitedAdminLogin->auth_token,
            ])
        );

        self::assertSame($response['problems'][0]['points'], 50.0);
    }

    public function testDeleteProblemFromAssignment() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // Create a course with an assignment
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $identity,
            $login
        );
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add one problem to the assignment
        $problem = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'public',
            'author' => $identity,
        ]), $login);
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseAlias,
            $assignmentAlias,
            [$problem]
        );

        // Remove a problem from the assignment
        $removeProblemResponse = \OmegaUp\Controllers\Course::apiRemoveProblem(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'course_alias' => $courseAlias,
            'assignment_alias' => $assignmentAlias,
            'problem_alias' => $problem['problem']->alias,
        ]));
        $this->assertSame('ok', $removeProblemResponse['status']);

        // Assert that the problem was correctly removed
        $getAssignmentResponse = \OmegaUp\Controllers\Course::apiAssignmentDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'course' => $courseAlias,
            'assignment' => $assignmentAlias,
        ]));
        $this->assertSame(0, sizeof($getAssignmentResponse['problems']));
    }

    public function testAddRemoveProblems() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // Create a course with an assignment
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $identity,
            $login
        );
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add multiple problems to the assignment
        $problems = [
            \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
                'visibility' => 'public',
                'author' => $identity
            ]), $login),
            \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
                'visibility' => 'public',
                'author' => $identity
            ]), $login),
            \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
                'visibility' => 'public',
                'author' => $identity
            ]), $login)
        ];
        $responses = \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseAlias,
            $assignmentAlias,
            $problems
        );
        $this->assertSame('ok', $responses[0]['status']);
        $this->assertSame('ok', $responses[1]['status']);
        $this->assertSame('ok', $responses[2]['status']);

        // Assert that the problems were correctly added
        $getAssignmentResponse = \OmegaUp\Controllers\Course::apiAssignmentDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'course' => $courseAlias,
            'assignment' => $assignmentAlias,
        ]));
        $this->assertSame(3, sizeof($getAssignmentResponse['problems']));

        // Remove multiple problems from the assignment
        $removeProblemResponse = \OmegaUp\Controllers\Course::apiRemoveProblem(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'course_alias' => $courseAlias,
            'assignment_alias' => $assignmentAlias,
            'problem_alias' => $problems[0]['problem']->alias,
        ]));
        $this->assertSame('ok', $removeProblemResponse['status']);
        $removeProblemResponse = \OmegaUp\Controllers\Course::apiRemoveProblem(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'course_alias' => $courseAlias,
            'assignment_alias' => $assignmentAlias,
            'problem_alias' => $problems[2]['problem']->alias,
        ]));
        $this->assertSame('ok', $removeProblemResponse['status']);

        // Assert that the problems were correctly removed
        $getAssignmentResponse = \OmegaUp\Controllers\Course::apiAssignmentDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'course' => $courseAlias,
            'assignment' => $assignmentAlias,
        ]));
        $this->assertSame(1, sizeof($getAssignmentResponse['problems']));
        $this->assertSame(
            $problems[1]['problem']->alias,
            $getAssignmentResponse['problems'][0]['alias']
        );
    }

    /**
     * Attempts to add a problem with a normal user.
     */
    public function testAddProblemForbiddenAccess() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        $problem = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'public',
            'author' => $identity
        ]), $login);

        // Create a course with an assignment
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $identity,
            $login
        );
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add one problem to the assignment with a normal user
        ['identity' => $forbiddenIdentity] = \OmegaUp\Test\Factories\User::createUser();
        $forbiddenUserLogin = self::login($forbiddenIdentity);
        try {
            \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
                $forbiddenUserLogin,
                $courseAlias,
                $assignmentAlias,
                [$problem]
            );
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Attempts to add a problem with a student.
     */
    public function testAddProblemForbiddenAccessStudent() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        $problem = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'public',
            'author' => $identity
        ]), $login);

        // Create a course with an assignment
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $identity,
            $login
        );
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add one problem to the assignment with a student
        $forbiddenUser = \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData
        );
        $forbiddenUserLogin = self::login($forbiddenUser);
        try {
            \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
                $forbiddenUserLogin,
                $courseAlias,
                $assignmentAlias,
                [$problem]
            );
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Attempts to remove a problem with a normal user.
     */
    public function testDeleteProblemForbiddenAccess() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // Create a course with an assignment
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $identity,
            $login
        );
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add one problem to the assignment
        $problem = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'public',
            'author' => $identity
        ]), $login);
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseAlias,
            $assignmentAlias,
            [$problem]
        );

        // Remove a problem from the assignment with a normal user
        ['identity' => $forbiddenIdentity] = \OmegaUp\Test\Factories\User::createUser();
        $forbiddenUserLogin = self::login($forbiddenIdentity);
        try {
            \OmegaUp\Controllers\Course::apiRemoveProblem(new \OmegaUp\Request([
                'auth_token' => $forbiddenUserLogin->auth_token,
                'course_alias' => $courseAlias,
                'assignment_alias' => $assignmentAlias,
                'problem_alias' => $problem['problem']->alias,
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Attempts to remove a problem with a student.
     */
    public function testDeleteProblemForbiddenAccessStudent() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // Create a course with an assignment
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $identity,
            $login
        );
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add one problem to the assignment
        $problem = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'public',
            'author' => $identity
        ]), $login);
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseAlias,
            $assignmentAlias,
            [$problem]
        );

        // Remove a problem from the assignment with a student
        $forbiddenUser = \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData
        );
        $forbiddenUserLogin = self::login($forbiddenUser);
        try {
            \OmegaUp\Controllers\Course::apiRemoveProblem(new \OmegaUp\Request([
                'auth_token' => $forbiddenUserLogin->auth_token,
                'course_alias' => $courseAlias,
                'assignment_alias' => $assignmentAlias,
                'problem_alias' => $problem['problem']->alias,
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Attempts to remove an invalid problem.
     */
    public function testDeleteNonExistingProblem() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        // Create a course with an assignment
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $identity,
            $login
        );
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Remove an invalid problem from the assignment
        try {
            \OmegaUp\Controllers\Course::apiRemoveProblem(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'course_alias' => $courseAlias,
                'assignment_alias' => $assignmentAlias,
                'problem_alias' => 'noexiste',
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            $this->assertSame('problemNotFound', $e->getMessage());
        }
    }

    public function testAssignmentProblemsStatistics() {
        $problemsData = [];
        for ($i = 0; $i < 3; $i++) {
            $problemsData[] = \OmegaUp\Test\Factories\Problem::createProblem();
        }

        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        $login = self::login($courseData['admin']);

        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseAlias,
            $assignmentAlias,
            [$problemsData[0], $problemsData[1], $problemsData[2]]
        );

        $identities = [];
        [
            'identity' => $identities[]
        ] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $identities[0]
        );

        [
            'identity' => $identities[]
        ] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $identities[1]
        );

        // First student will solve problem0 and problem1, and won't try problem2
        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemsData[0],
            $courseData,
            $identities[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData, 0, 'CE');

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemsData[0],
            $courseData,
            $identities[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemsData[1],
            $courseData,
            $identities[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemsData[2],
            $courseData,
            $identities[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData, 0, 'WA');

        // Second student will solve problem1, fail (90%) on problem0 and won't try problem2
        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemsData[1],
            $courseData,
            $identities[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemsData[0],
            $courseData,
            $identities[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData, 0.9, 'PA');

        $results = \OmegaUp\DAO\Assignments::getAssignmentsProblemsStatistics(
            $courseData['course']->course_id,
            $courseData['course']->group_id
        );

        $this->assertSame($assignmentAlias, $results[0]['assignment_alias']);
        // Variance of the first problem must be greater than 0
        // Variance of the second one should be 0, all users solved it
        // Variance of the third problem should be 0, no user did anything
        $this->assertGreaterThan(0, $results[0]['variance']);
        $this->assertSame(0.0, $results[1]['variance']);
        $this->assertSame(0.0, $results[2]['variance']);
        // Average
        $this->assertSame(95.0, $results[0]['average']);
        $this->assertSame(100.0, $results[1]['average']);
        $this->assertSame(0.0, $results[2]['average']);
        // Minimum
        $this->assertSame(90.0, $results[0]['minimum']);
        $this->assertSame(100.0, $results[1]['minimum']);
        $this->assertSame(0.0, $results[2]['minimum']);
        // Maximum
        $this->assertSame(100.0, $results[0]['maximum']);
        $this->assertSame(100.0, $results[1]['maximum']);
        $this->assertSame(0.0, $results[2]['maximum']);
        // Percent over 100%
        $this->assertSame(50.0, $results[0]['completed_score_percentage']);
        $this->assertSame(100.0, $results[1]['completed_score_percentage']);
        $this->assertSame(0.0, $results[2]['completed_score_percentage']);
        // Percent over 60%
        $this->assertSame(100.0, $results[0]['high_score_percentage']);
        $this->assertSame(100.0, $results[1]['high_score_percentage']);
        $this->assertSame(0.0, $results[2]['high_score_percentage']);
        // Percent at 0%
        $this->assertSame(0.0, $results[0]['low_score_percentage']);
        $this->assertSame(0.0, $results[1]['low_score_percentage']);
        $this->assertSame(100.0, $results[2]['low_score_percentage']);
        //average runs
        $this->assertGreaterThan(1, $results[0]['avg_runs']);
        $this->assertSame(1.0, $results[1]['avg_runs']);
        $this->assertLessThan(1, $results[2]['avg_runs']);
    }

    public function testAssignmentVerdictDistribution() {
        $problemsData = [];
        for ($i = 0; $i < 3; $i++) {
            $problemsData[] = \OmegaUp\Test\Factories\Problem::createProblem();
        }

        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        $login = self::login($courseData['admin']);

        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseAlias,
            $assignmentAlias,
            [$problemsData[0], $problemsData[1], $problemsData[2]]
        );

        $identities = [];
        [
            'identity' => $identities[]
        ] = \OmegaUp\Test\Factories\User::createUser(new \OmegaUp\Test\Factories\UserParams([
            'username' => 'user0',
        ]));
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $identities[0]
        );

        [
            'identity' => $identities[]
        ] = \OmegaUp\Test\Factories\User::createUser(new \OmegaUp\Test\Factories\UserParams([
            'username' => 'user1',
        ]));
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $identities[1]
        );

        // First student will solve problem0, get 'CE', 'CE' then solve problem 1, and won't try problem2
        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemsData[0],
            $courseData,
            $identities[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemsData[1],
            $courseData,
            $identities[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData, 0, 'CE');

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemsData[1],
            $courseData,
            $identities[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData, 0, 'CE');

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemsData[1],
            $courseData,
            $identities[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        // Second student will solve problem0, get 'TLE' then solve problem 1, and fail problem2
        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemsData[0],
            $courseData,
            $identities[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemsData[1],
            $courseData,
            $identities[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData, 0, 'TLE');

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemsData[1],
            $courseData,
            $identities[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemsData[2],
            $courseData,
            $identities[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData, 0, 'WA');

        $results = \OmegaUp\DAO\Assignments::getAssignmentVerdictDistribution(
            $courseData['course']->course_id,
            $courseData['course']->group_id
        );
        //check if results include expected runs and verdicts
        $this->assertEquals(
            $results,
            [
              [
                'verdict' => 'AC',
                'runs' => 1,
                'assignment_alias' => $assignmentAlias,
                'problem_id' => $problemsData[0]['problem']->problem_id,
                'problem_alias' => $problemsData[0]['problem']->alias,
              ],
              [
                'verdict' => 'AC',
                'runs' => 1,
                'assignment_alias' => $assignmentAlias,
                'problem_id' => $problemsData[0]['problem']->problem_id,
                'problem_alias' => $problemsData[0]['problem']->alias,
              ],
              [
                'verdict' => 'AC',
                'runs' => 1,
                'assignment_alias' => $assignmentAlias,
                'problem_id' => $problemsData[1]['problem']->problem_id,
                'problem_alias' => $problemsData[1]['problem']->alias,
              ],
              [
                'verdict' => 'AC',
                'runs' => 1,
                'assignment_alias' => $assignmentAlias,
                'problem_id' => $problemsData[1]['problem']->problem_id,
                'problem_alias' => $problemsData[1]['problem']->alias,
              ],
              [
                'verdict' => 'TLE',
                'runs' => 1,
                'assignment_alias' => $assignmentAlias,
                'problem_id' => $problemsData[1]['problem']->problem_id,
                'problem_alias' => $problemsData[1]['problem']->alias,
              ],
              [
                'verdict' => 'CE',
                'runs' => 2,
                'assignment_alias' => $assignmentAlias,
                'problem_id' => $problemsData[1]['problem']->problem_id,
                'problem_alias' => $problemsData[1]['problem']->alias,
              ],
              [
                'verdict' => 'WA',
                'runs' => 1,
                'assignment_alias' => $assignmentAlias,
                'problem_id' => $problemsData[2]['problem']->problem_id,
                'problem_alias' => $problemsData[2]['problem']->alias,
              ],
            ]
        );
    }

    public function testCreateAssignmentWithPrivateProblems() {
        [
            'identity' => $problemAdminIdentity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        $problemAdminLogin = self::login($problemAdminIdentity);

        [
            'identity' => $courseAdminIdentity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        $courseAdminLogin = self::login($courseAdminIdentity);

        $courseAlias = \OmegaUp\Test\Utils::createRandomString();

        // Call api
        $response = \OmegaUp\Controllers\Course::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => $courseAdminLogin->auth_token,
                'name' => \OmegaUp\Test\Utils::createRandomString(),
                'alias' => $courseAlias,
                'description' => \OmegaUp\Test\Utils::createRandomString(),
                'start_time' => (\OmegaUp\Time::get() + 60),
                'finish_time' => (\OmegaUp\Time::get() + 120)
            ])
        );
        $this->assertSame('ok', $response['status']);

        // Create problems
        $mappingProblemsVisibility = [
            'problem_1' => 'public',
            'problem_2' => 'private',
            'problem_3' => 'public',
        ];
        $problemsData = [];
        foreach ($mappingProblemsVisibility as $alias => $problemVisibility) {
            $problemRequest = \OmegaUp\Test\Factories\Problem::createProblem(
                new \OmegaUp\Test\Factories\ProblemParams([
                    'alias' => $alias,
                    'visibility' => $problemVisibility,
                ]),
                $problemAdminLogin
            )['request'];
            $currentProblemData = ['alias' => $problemRequest['problem_alias']];
            $problemsData[] = $currentProblemData;
        }

        // Create the assignment
        $assignmentAlias = \OmegaUp\Test\Utils::createRandomString();
        try {
            \OmegaUp\Controllers\Course::apiCreateAssignment(
                new \OmegaUp\Request([
                    'auth_token' => $courseAdminLogin->auth_token,
                    'name' => \OmegaUp\Test\Utils::createRandomString(),
                    'alias' => $assignmentAlias,
                    'description' => \OmegaUp\Test\Utils::createRandomString(),
                    'start_time' => (\OmegaUp\Time::get() + 60),
                    'finish_time' => (\OmegaUp\Time::get() + 120),
                    'course_alias' => $courseAlias,
                    'assignment_type' => 'homework',
                    'problems' => json_encode($problemsData)
                ])
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame(
                'userNotAllowedToAddPrivateProblem',
                $e->getMessage()
            );
        }
    }
}
