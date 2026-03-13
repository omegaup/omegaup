<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

/**
 * Tests that students' progress can be tracked.
 */
class CourseStudentsTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Basic apiStudentProgress test.
     */
    public function testAddStudentToCourse() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();
        $studentsInCourse = 5;

        // Prepare assignment. Create problems
        $adminLogin = self::login($courseData['admin']);
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        \OmegaUp\Controllers\Course::apiAddProblem(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'assignment_alias' => $courseData['assignment_alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));

        $problem = $problemData['problem'];

        // Add students to course
        $students = [];
        for ($i = 0; $i < $studentsInCourse; $i++) {
            $students[] = \OmegaUp\Test\Factories\Course::addStudentToCourse(
                $courseData
            );
        }

        // Add one run to one of the problems.
        $submissionSource = "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }";
        {
            $studentLogin = \OmegaUp\Test\ControllerTestCase::login(
                $students[0]
            );
            $runResponsePA = \OmegaUp\Controllers\Run::apiCreate(new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'problemset_id' => $courseData['assignment']->problemset_id,
                'problem_alias' => $problem->alias,
                'language' => 'c11-gcc',
                'source' => $submissionSource,
            ]));
            \OmegaUp\Test\Factories\Run::gradeRun(
                null /*runData*/,
                0.5,
                'PA',
                null,
                $runResponsePA['guid']
            );
        }

        // Send feedback for the submission
        $feedback = 'Test feedback';
        \OmegaUp\Controllers\Submission::apiSetFeedback(
            new \OmegaUp\Request([
                'auth_token' => self::login($courseData['admin'])->auth_token,
                'guid' => $runResponsePA['guid'],
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment_alias'],
                'feedback' => $feedback,
            ])
        );

        // Call API
        $adminLogin = self::login($courseData['admin']);
        $response = \OmegaUp\Controllers\Course::apiStudentProgress(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'assignment_alias' => $courseData['assignment_alias'],
            'usernameOrEmail' => $students[0]->username,
        ]));
        $this->assertCount(1, $response['problems']);
        $this->assertCount(1, $response['problems'][0]['runs']);
        $this->assertSame(
            $response['problems'][0]['runs'][0]['source'],
            $submissionSource
        );
        $this->assertSame($response['problems'][0]['runs'][0]['score'], 0.5);
        $this->assertNotNull($response['problems'][0]['runs'][0]['feedback']);
        $this->assertSame(
            $feedback,
            $response['problems'][0]['runs'][0]['feedback']['feedback']
        );
    }

    /**
     * apiIntroDetails test with associated identities to the course's group.
     */
    public function testAddIdentityStudentToCourse() {
        // Add a new user with identity groups creator privileges, and login
        ['user' => $creator, 'identity' => $creatorIdentity] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);

        // Create a course where course admin is a identity creator group member
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $creatorIdentity,
            $creatorLogin
        );

        // Prepare assignment. Create problems
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        \OmegaUp\Controllers\Course::apiAddProblem(new \OmegaUp\Request([
            'auth_token' => $creatorLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'assignment_alias' => $courseData['assignment_alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));

        // Get Group object
        $associatedGroup = \OmegaUp\DAO\Groups::findByAlias(
            $courseData['course_alias']
        );

        // Create identities for a group
        $password = \OmegaUp\Test\Utils::createRandomString();
        [$_, $associatedIdentity] = \OmegaUp\Test\Factories\Identity::createIdentitiesFromAGroup(
            $associatedGroup,
            $creatorLogin,
            $password
        );

        // Create an unassociated group, it does not have access to the course
        $unassociatedGroup = \OmegaUp\Test\Factories\Groups::createGroup(
            $creatorIdentity,
            null,
            null,
            null,
            $creatorLogin
        )['group'];

        [$unassociatedIdentity, $_] = \OmegaUp\Test\Factories\Identity::createIdentitiesFromAGroup(
            $unassociatedGroup,
            $creatorLogin,
            $password
        );

        // Create a valid run for assignment
        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemData,
            $courseData,
            $associatedIdentity
        );

        try {
            // Create an invalid run for assignment, because identity is not a
            // member of the course group
            $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
                $problemData,
                $courseData,
                $unassociatedIdentity
            );
            $this->fail('Unassociated identity group should not join the course' .
                        'without an explicit invitation');
        } catch (\OmegaUp\Exceptions\NotAllowedToSubmitException $e) {
            $this->assertSame('runNotEvenOpened', $e->getMessage());
        }
    }

    /**
     * Basic apiMyProgress test.
     */
    public function testStudentASsignmentProgress() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithAssignments(
            nAssignments: 2
        );
        $studentsInCourse = 2;

        // Prepare assignment. Create four problems: The first three accept
        // submissions and the last one does not.
        $adminLogin = self::login($courseData['admin']);
        $problems = [];
        for ($i = 0; $i < 3; $i++) {
            $problems[] = \OmegaUp\Test\Factories\Problem::createProblem();
        }
        $problems[] = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'languages' => '',
            ])
        );

        // Problems 1 and 2 will be assigned to the first assignment, both have
        // submissions. Problem 3 and 4 will be assigned to second assignment
        foreach ($problems as $index => $problemData) {
            $assignmentAliasIndex = ($index === 0 || $index === 1) ? 0 : 1;
            \OmegaUp\Controllers\Course::apiAddProblem(new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment_aliases'][
                    $assignmentAliasIndex
                ],
                'problem_alias' => $problemData['request']['problem_alias'],
            ]));
        }

        // Add students to course
        $students = [];
        for ($i = 0; $i < $studentsInCourse; $i++) {
            $students[] = \OmegaUp\Test\Factories\Course::addStudentToCourse(
                $courseData
            );
        }

        $submissionSource = "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }";
        {
            $studentLogin = \OmegaUp\Test\ControllerTestCase::login(
                $students[0]
            );

            // Add one run to the first problem in the first assignment.
            $runResponse = \OmegaUp\Controllers\Run::apiCreate(new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'problemset_id' => $courseData['assignment_problemset_ids'][0],
                'problem_alias' => $problems[0]['problem']->alias,
                'language' => 'c11-gcc',
                'source' => $submissionSource,
            ]));
            \OmegaUp\Test\Factories\Run::gradeRun(
                points: 1,
                verdict: 'AC',
                runGuid: $runResponse['guid']
            );

            // Add one run to the third problem in the second assignment
            $runResponse = \OmegaUp\Controllers\Run::apiCreate(new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'problemset_id' => $courseData['assignment_problemset_ids'][1],
                'problem_alias' => $problems[2]['problem']->alias,
                'language' => 'c11-gcc',
                'source' => $submissionSource,
            ]));
            \OmegaUp\Test\Factories\Run::gradeRun(
                points: 1,
                verdict: 'AC',
                runGuid: $runResponse['guid']
            );

            $response = \OmegaUp\Controllers\Course::apiMyProgress(
                new \OmegaUp\Request([
                    'auth_token' => $studentLogin->auth_token,
                    'alias' => $courseData['course_alias'],
                    'usernameOrEmail' => $students[0]->username,
                ])
            );
        }
        // In first assignment, the student only solved one of two problem with
        // submissions to achieve 50% of progress
        $this->assertSame(
            $response['assignments'][$courseData['assignment_aliases'][0]]['score'],
            100.0
        );
        $this->assertSame(
            $response['assignments'][$courseData['assignment_aliases'][0]]['max_score'],
            200.0
        );
        // In second assignment, the student solved all the problems with
        // submissions to achieve 100% of progress
        $this->assertSame(
            $response['assignments'][$courseData['assignment_aliases'][1]]['score'],
            100.0
        );
        $this->assertSame(
            $response['assignments'][$courseData['assignment_aliases'][1]]['max_score'],
            100.0
        );
    }

    /**
     * Basic getStudentProgressByAssignmentForTypeScript test.
     */
    public function testStudentProgressByAssignment() {
        // Create 3 problems
        $problemsData = [];
        for ($i = 0; $i < 3; $i++) {
            $problemsData[] = \OmegaUp\Test\Factories\Problem::createProblem();
        }

        // Create course with one assignment
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Create admin login
        $login = self::login($courseData['admin']);

        // Add problems to assignment
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseAlias,
            $assignmentAlias,
            [$problemsData[0], $problemsData[1], $problemsData[2]]
        );

        // Create one student for the course
        $identities = [];
        [
            'user' => $user,
            'identity' => $identities[]
        ] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $identities[0]
        );

        // The student will solve problem1, fail (90%) on problem0 and won't try problem2
        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemsData[1],
            $courseData,
            $identities[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemsData[0],
            $courseData,
            $identities[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData, 0.9, 'PA');

        // Create request
        $login = self::login($courseData['admin']);
        $response = \OmegaUp\Controllers\Course::getStudentProgressByAssignmentForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'course' => $courseAlias,
                'assignment_alias' => $assignmentAlias,
                'student' => $identities[0]->username,
            ])
        );

        // Test results
        // The studen only get 90 points in the first problem,
        // so 90% of progress is expected
        $this->assertSame(
            $response['templateProperties']['payload']['students'][0]['progress'][$assignmentAlias][$problemsData[0]['problem']->alias],
            90.0
        );

        // The studen got AC points in the second problem,
        // so 100% of progress is expected
        $this->assertSame(
            $response['templateProperties']['payload']['students'][0]['progress'][$assignmentAlias][$problemsData[1]['problem']->alias],
            100.0
        );

        // The studen didn't try third problem,
        // so 0% of progress is expected
        $this->assertSame(
            $response['templateProperties']['payload']['students'][0]['progress'][$assignmentAlias][$problemsData[2]['problem']->alias],
            0.0
        );
    }

    /**
     * Basic apiStudentProgress test.
     */
    public function testGiveFeedbackWithNewFields() {
        // create course with an assignment
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();
        $studentsInCourse = 5;

        // Prepare assignment. Create problems
        $adminLogin = self::login($courseData['admin']);
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // add problem
        \OmegaUp\Controllers\Course::apiAddProblem(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'assignment_alias' => $courseData['assignment_alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));

        $problem = $problemData['problem'];

        // Add students to course
        $students = [];
        for ($i = 0; $i < $studentsInCourse; $i++) {
            $students[] = \OmegaUp\Test\Factories\Course::addStudentToCourse(
                $courseData
            );
        }

        // Add one run to one of the problems.
        $submissionSource = "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }";
        {
            $studentLogin = \OmegaUp\Test\ControllerTestCase::login(
                $students[0]
            );
            $runResponsePA = \OmegaUp\Controllers\Run::apiCreate(new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'problemset_id' => $courseData['assignment']->problemset_id,
                'problem_alias' => $problem->alias,
                'language' => 'c11-gcc',
                'source' => $submissionSource,
            ]));
            \OmegaUp\Test\Factories\Run::gradeRun(
                null /*runData*/,
                0.5,
                'PA',
                null,
                $runResponsePA['guid']
            );
        }

        $adminLogin = self::login($courseData['admin']);

        // create normal user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // admin is able to add a teaching assistant
        \OmegaUp\Controllers\Course::apiAddTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'usernameOrEmail' => $identity->username,
                'course_alias' => $courseData['course_alias'],
             ])
        );

        $course = \OmegaUp\DAO\Courses::getByAlias(
            $courseData['course_alias']
        );

        // login user
        $userLogin = self::login($identity);

        $this->assertTrue(
            \OmegaUp\Authorization::isTeachingAssistant(
                $identity,
                $course
            )
        );

        // Send feedback for the submission by a teaching assistant, include the new fields
        $feedback = 'Test feedback!';
        \OmegaUp\Controllers\Submission::apiSetFeedback(
            new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
                'guid' => $runResponsePA['guid'],
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment_alias'],
                'feedback' => $feedback,
                1,
                89
            ])
        );

        // Call API
        $adminLogin = self::login($courseData['admin']);

        $response = \OmegaUp\Controllers\Course::apiStudentProgress(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'assignment_alias' => $courseData['assignment_alias'],
            'usernameOrEmail' => $students[0]->username,
        ]));

        $this->assertEquals(
            $feedback,
            $response['problems'][0]['runs'][0]['feedback']['feedback']
        );
    }

    /**
     * Test getStudentProgressForTypeScript returns correct progress data
     * for a single student.
     */
    public function testGetStudentProgressForTypeScript() {
        // Create 3 problems
        $problemsData = [];
        for ($i = 0; $i < 3; $i++) {
            $problemsData[] = \OmegaUp\Test\Factories\Problem::createProblem();
        }

        // Create course with two assignments
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithAssignments(
            nAssignments: 2
        );
        $courseAlias = $courseData['course_alias'];

        // Create admin login
        $login = self::login($courseData['admin']);

        // Add problems to first assignment
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseAlias,
            $courseData['assignment_aliases'][0],
            [$problemsData[0], $problemsData[1]]
        );

        // Add problem to second assignment
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseAlias,
            $courseData['assignment_aliases'][1],
            [$problemsData[2]]
        );

        // Create one student for the course
        [
            'user' => $user,
            'identity' => $student
        ] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $student
        );

        // Set up student login and open course/assignments
        $studentLogin = \OmegaUp\Test\ControllerTestCase::login($student);
        $submissionSource = "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }";

        // Open course and first assignment
        \OmegaUp\Test\Factories\Course::openCourse(
            $courseAlias,
            $student
        );
        \OmegaUp\Test\Factories\Course::openAssignmentCourse(
            $courseAlias,
            $courseData['assignment_aliases'][0],
            $student
        );

        // Student solves problem0 with 100%
        \OmegaUp\Test\Factories\Course::openProblemInCourseAssignment(
            $courseAlias,
            $courseData['assignment_aliases'][0],
            $problemsData[0],
            $student
        );
        // Refresh login after helper methods that create their own sessions
        $studentLogin = \OmegaUp\Test\ControllerTestCase::login($student);
        $runResponse = \OmegaUp\Controllers\Run::apiCreate(new \OmegaUp\Request([
            'auth_token' => $studentLogin->auth_token,
            'problemset_id' => $courseData['assignment_problemset_ids'][0],
            'problem_alias' => $problemsData[0]['problem']->alias,
            'language' => 'c11-gcc',
            'source' => $submissionSource,
        ]));
        \OmegaUp\Test\Factories\Run::gradeRun(
            points: 1,
            verdict: 'AC',
            runGuid: $runResponse['guid']
        );

        // Student solves problem1 with 50%
        \OmegaUp\Test\Factories\Course::openProblemInCourseAssignment(
            $courseAlias,
            $courseData['assignment_aliases'][0],
            $problemsData[1],
            $student
        );
        // Refresh login after helper methods that create their own sessions
        $studentLogin = \OmegaUp\Test\ControllerTestCase::login($student);
        $runResponse = \OmegaUp\Controllers\Run::apiCreate(new \OmegaUp\Request([
            'auth_token' => $studentLogin->auth_token,
            'problemset_id' => $courseData['assignment_problemset_ids'][0],
            'problem_alias' => $problemsData[1]['problem']->alias,
            'language' => 'c11-gcc',
            'source' => $submissionSource,
        ]));
        \OmegaUp\Test\Factories\Run::gradeRun(
            points: 0.5,
            verdict: 'PA',
            runGuid: $runResponse['guid']
        );

        // Student solves problem2 in second assignment with 100%
        \OmegaUp\Test\Factories\Course::openAssignmentCourse(
            $courseAlias,
            $courseData['assignment_aliases'][1],
            $student
        );
        \OmegaUp\Test\Factories\Course::openProblemInCourseAssignment(
            $courseAlias,
            $courseData['assignment_aliases'][1],
            $problemsData[2],
            $student
        );

        // Refresh login after helper methods that create their own sessions
        $studentLogin = \OmegaUp\Test\ControllerTestCase::login($student);
        $runResponse = \OmegaUp\Controllers\Run::apiCreate(new \OmegaUp\Request([
            'auth_token' => $studentLogin->auth_token,
            'problemset_id' => $courseData['assignment_problemset_ids'][1],
            'problem_alias' => $problemsData[2]['problem']->alias,
            'language' => 'c11-gcc',
            'source' => $submissionSource,
        ]));
        \OmegaUp\Test\Factories\Run::gradeRun(
            points: 1,
            verdict: 'AC',
            runGuid: $runResponse['guid']
        );

        // Call getStudentProgressForTypeScript
        $login = self::login($courseData['admin']);
        $response = \OmegaUp\Controllers\Course::getStudentProgressForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'course' => $courseAlias,
                'student' => $student->username,
            ])
        );

        // Verify the response structure
        $this->assertArrayHasKey('templateProperties', $response);
        $this->assertArrayHasKey('payload', $response['templateProperties']);
        $payload = $response['templateProperties']['payload'];

        $this->assertArrayHasKey('students', $payload);
        $this->assertArrayHasKey('student', $payload);
        $this->assertArrayHasKey('course', $payload);

        // Verify we get only one student
        $this->assertCount(1, $payload['students']);
        $this->assertSame(
            $student->username,
            $payload['students'][0]['username']
        );

        // Verify progress for first assignment
        $firstAssignment = $courseData['assignment_aliases'][0];
        $this->assertArrayHasKey(
            $firstAssignment,
            $payload['students'][0]['progress']
        );

        // problem0 should have 100% progress
        $this->assertSame(
            100.0,
            $payload['students'][0]['progress'][$firstAssignment][$problemsData[0]['problem']->alias]
        );

        // problem1 should have 50% progress
        $this->assertSame(
            50.0,
            $payload['students'][0]['progress'][$firstAssignment][$problemsData[1]['problem']->alias]
        );

        // Verify progress for second assignment
        $secondAssignment = $courseData['assignment_aliases'][1];
        $this->assertArrayHasKey(
            $secondAssignment,
            $payload['students'][0]['progress']
        );

        // problem2 should have 100% progress
        $this->assertSame(
            100.0,
            $payload['students'][0]['progress'][$secondAssignment][$problemsData[2]['problem']->alias]
        );
    }

    /**
     * Test getStudentProgressByAssignmentForTypeScript with multiple students
     * to verify the query correctly filters to a single student.
     */
    public function testStudentProgressByAssignmentMultipleStudents() {
        // Create 2 problems
        $problemsData = [];
        for ($i = 0; $i < 2; $i++) {
            $problemsData[] = \OmegaUp\Test\Factories\Problem::createProblem();
        }

        // Create course with one assignment
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Create admin login
        $login = self::login($courseData['admin']);

        // Add problems to assignment
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseAlias,
            $assignmentAlias,
            [$problemsData[0], $problemsData[1]]
        );

        // Create two students for the course
        $students = [];
        for ($i = 0; $i < 2; $i++) {
            [
                'user' => $user,
                'identity' => $students[]
            ] = \OmegaUp\Test\Factories\User::createUser();
            \OmegaUp\Test\Factories\Course::addStudentToCourse(
                $courseData,
                $students[$i]
            );
        }

        // Student 0 solves problem0 with 100%
        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemsData[0],
            $courseData,
            $students[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        // Student 1 solves problem1 with 80%
        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemsData[1],
            $courseData,
            $students[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData, 0.8, 'PA');

        // Request progress for student 0 only
        $login = self::login($courseData['admin']);
        $response = \OmegaUp\Controllers\Course::getStudentProgressByAssignmentForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'course' => $courseAlias,
                'assignment_alias' => $assignmentAlias,
                'student' => $students[0]->username,
            ])
        );

        $payload = $response['templateProperties']['payload'];

        // Verify we get only student 0's data
        $this->assertCount(1, $payload['students']);
        $this->assertSame(
            $students[0]->username,
            $payload['students'][0]['username']
        );

        // Verify student 0's progress: problem0 = 100%, problem1 = 0% (didn't attempt)
        $this->assertSame(
            100.0,
            $payload['students'][0]['progress'][$assignmentAlias][$problemsData[0]['problem']->alias]
        );
        $this->assertSame(
            0.0,
            $payload['students'][0]['progress'][$assignmentAlias][$problemsData[1]['problem']->alias]
        );

        // Now request progress for student 1
        $response = \OmegaUp\Controllers\Course::getStudentProgressByAssignmentForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'course' => $courseAlias,
                'assignment_alias' => $assignmentAlias,
                'student' => $students[1]->username,
            ])
        );

        $payload = $response['templateProperties']['payload'];

        // Verify we get only student 1's data
        $this->assertCount(1, $payload['students']);
        $this->assertSame(
            $students[1]->username,
            $payload['students'][0]['username']
        );

        // Verify student 1's progress: problem0 = 0% (didn't attempt), problem1 = 80%
        $this->assertSame(
            0.0,
            $payload['students'][0]['progress'][$assignmentAlias][$problemsData[0]['problem']->alias]
        );
        $this->assertSame(
            80.0,
            $payload['students'][0]['progress'][$assignmentAlias][$problemsData[1]['problem']->alias]
        );
    }
}
