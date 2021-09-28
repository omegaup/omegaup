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
            /*$nAssignments=*/            2
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
                /*$runData=*/                null,
                1,
                'AC',
                null,
                $runResponse['guid']
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
                /*$runData=*/                null,
                1,
                'AC',
                null,
                $runResponse['guid']
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
     * Basic apiMyProgress test.
     */
    public function testStudentOneHoundredAssignmentProgress() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithAssignments(
            /*$nAssignments=*/            1
        );
        $studentsInCourse = 2;

        // Prepare assignment. Create 1 problem
        $adminLogin = self::login($courseData['admin']);
        $problems = [];
        $problems[] = \OmegaUp\Test\Factories\Problem::createProblem();

        // All problems will be assigned to the assignment
        foreach ($problems as $index => $problemData) {
            $assignmentAliasIndex = 0;
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
            $firstStudentLogin = \OmegaUp\Test\ControllerTestCase::login(
                $students[0]
            );

            $secondStudentLogin = \OmegaUp\Test\ControllerTestCase::login(
                $students[1]
            );

            // Add one AC run for the first student
            $runResponse = \OmegaUp\Controllers\Run::apiCreate(new \OmegaUp\Request([
                'auth_token' => $firstStudentLogin->auth_token,
                'problemset_id' => $courseData['assignment_problemset_ids'][0],
                'problem_alias' => $problems[0]['problem']->alias,
                'language' => 'c11-gcc',
                'source' => $submissionSource,
            ]));
            \OmegaUp\Test\Factories\Run::gradeRun(
                /*$runData=*/                null,
                1,
                'AC',
                null,
                $runResponse['guid']
            );

            // Add a WA runs for the second student
            $runResponse = \OmegaUp\Controllers\Run::apiCreate(new \OmegaUp\Request([
                'auth_token' => $secondStudentLogin->auth_token,
                'problemset_id' => $courseData['assignment_problemset_ids'][0],
                'problem_alias' => $problems[0]['problem']->alias,
                'language' => 'c11-gcc',
                'source' => $submissionSource,
            ]));
            \OmegaUp\Test\Factories\Run::gradeRun(
                /*$runData=*/                null,
                0.9,
                'PA',
                null,
                $runResponse['guid']
            );

            $firstResponse = \OmegaUp\Controllers\Course::apiMyProgress(
                new \OmegaUp\Request([
                    'auth_token' => $firstStudentLogin->auth_token,
                    'alias' => $courseData['course_alias'],
                    'usernameOrEmail' => $students[0]->username,
                ])
            );

            $secondResponse = \OmegaUp\Controllers\Course::apiMyProgress(
                new \OmegaUp\Request([
                    'auth_token' => $secondStudentLogin->auth_token,
                    'alias' => $courseData['course_alias'],
                    'usernameOrEmail' => $students[1]->username,
                ])
            );
        }

        // The first student solved the problem in the assignment, so
        // 100% of progress for that problem was achived
        $this->assertEquals(
            $firstResponse['assignments'][$courseData['assignment_aliases'][0]]['score'],
            100
        );
        $this->assertEquals(
            $firstResponse['assignments'][$courseData['assignment_aliases'][0]]['max_score'],
            100
        );

        // The second student got a PA, so 90% of progress
        // for that problem was achived
        $this->assertEquals(
            $secondResponse['assignments'][$courseData['assignment_aliases'][0]]['score'],
            90
        );
        $this->assertEquals(
            $secondResponse['assignments'][$courseData['assignment_aliases'][0]]['max_score'],
            100
        );
    }
}
