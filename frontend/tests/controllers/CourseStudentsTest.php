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
        $this->assertEquals(
            $response['problems'][0]['runs'][0]['source'],
            $submissionSource
        );
        $this->assertEquals($response['problems'][0]['runs'][0]['score'], 0.5);
        $this->assertNotNull($response['problems'][0]['runs'][0]['feedback']);
        $this->assertEquals(
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
            $this->assertEquals('runNotEvenOpened', $e->getMessage());
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
        $this->assertEquals(
            $response['assignments'][$courseData['assignment_aliases'][0]]['score'],
            100
        );
        $this->assertEquals(
            $response['assignments'][$courseData['assignment_aliases'][0]]['max_score'],
            200
        );
        // In second assignment, the student solved all the problems with
        // submissions to achieve 100% of progress
        $this->assertEquals(
            $response['assignments'][$courseData['assignment_aliases'][1]]['score'],
            100
        );
        $this->assertEquals(
            $response['assignments'][$courseData['assignment_aliases'][1]]['max_score'],
            100
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
        $this->assertEquals(
            $response['templateProperties']['payload']['students'][0]['progress'][$assignmentAlias][$problemsData[0]['problem']->alias],
            90
        );

        // The studen got AC points in the second problem,
        // so 100% of progress is expected
        $this->assertEquals(
            $response['templateProperties']['payload']['students'][0]['progress'][$assignmentAlias][$problemsData[1]['problem']->alias],
            100
        );

        // The studen didn't try third problem,
        // so 0% of progress is expected
        $this->assertEquals(
            $response['templateProperties']['payload']['students'][0]['progress'][$assignmentAlias][$problemsData[2]['problem']->alias],
            0
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
}
