<?php

class CourseCloneTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Create clone of a course
     */
    public function testCreateCourseClone() {
        $homeworkCount = 2;
        $testCount = 2;
        $problemsPerAssignment = 2;
        $studentCount = 2;
        $problemAssignmentsMap = [];

        // Create course with assignments
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithNAssignmentsPerType([
            'homework' => $homeworkCount,
            'test' => $testCount
        ]);

        // Add problems to assignments
        $adminLogin = self::login($courseData['admin']);
        for ($i = 0; $i < $homeworkCount + $testCount; $i++) {
            $assignmentAlias = $courseData['assignment_aliases'][$i];
            $problemAssignmentsMap[$assignmentAlias] = [];

            for ($j = 0; $j < $problemsPerAssignment; $j++) {
                $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
                \OmegaUp\Controllers\Course::apiAddProblem(new \OmegaUp\Request([
                    'auth_token' => $adminLogin->auth_token,
                    'course_alias' => $courseData['course_alias'],
                    'assignment_alias' => $assignmentAlias,
                    'problem_alias' => $problemData['request']['problem_alias'],
                ]));
                $problemAssignmentsMap[$assignmentAlias][] = $problemData;
            }
        }

        // Create & add students to course
        $studentsUsername = [];
        $studentsData = null;
        for ($i = 0; $i < $studentCount; $i++) {
            $studentsData = \OmegaUp\Test\Factories\Course::addStudentToCourse(
                $courseData
            );
            $studentsUsername[] = $studentsData->username;
        }

        $courseAlias = \OmegaUp\Test\Utils::createRandomString();

        // Clone the course
        $adminLogin = self::login($courseData['admin']);
        $courseClonedData = \OmegaUp\Controllers\Course::apiClone(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'alias' => $courseAlias,
            'start_time' => \OmegaUp\Time::get()
        ]));

        $this->assertEquals($courseAlias, $courseClonedData['alias']);
        $this->assertArrayContainsWithPredicateExactlyOnce(
            \OmegaUp\DAO\CourseCloneLog::getAll(),
            fn (\OmegaUp\DAO\VO\CourseCloneLog $courseLog) =>
                $courseLog->course_id === $courseData['course_id']
        );

        $assignments = \OmegaUp\Controllers\Course::apiListAssignments(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias']
        ]));
        foreach ($assignments['assignments'] as $key => $assignment) {
            $this->assertEquals(
                $courseData['assignment_aliases'][$key],
                $assignment['alias']
            );
            $problems = \OmegaUp\Controllers\Course::apiAssignmentDetails(new \OmegaUp\Request([
                'assignment' => $assignment['alias'],
                'course' => $courseAlias,
                'auth_token' => $adminLogin->auth_token
            ]));
            foreach ($problems['problems'] as $index => $problem) {
                $this->assertEquals(
                    $problemAssignmentsMap[$courseData[
                    'assignment_aliases'][$key]][$index]['problem']->alias,
                    $problem['alias']
                );
            }
        }
        $students = \OmegaUp\Controllers\Course::apiListStudents(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseAlias
        ]));
        $this->assertEmpty($students['students']);
    }

    /**
     * Creating a clone with the original course alias
     */
    public function testCreateCourseCloneWithTheSameAlias() {
        $homeworkCount = 2;
        $testCount = 2;
        $problemsPerAssignment = 2;
        $studentCount = 2;
        $problemAssignmentsMap = [];

        // Create course with assignments
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithNAssignmentsPerType([
            'homework' => $homeworkCount,
            'test' => $testCount
        ]);

        // Add problems to assignments
        $adminLogin = self::login($courseData['admin']);
        for ($i = 0; $i < $homeworkCount + $testCount; $i++) {
            $assignmentAlias = $courseData['assignment_aliases'][$i];
            $problemAssignmentsMap[$assignmentAlias] = [];

            for ($j = 0; $j < $problemsPerAssignment; $j++) {
                $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
                \OmegaUp\Controllers\Course::apiAddProblem(new \OmegaUp\Request([
                    'auth_token' => $adminLogin->auth_token,
                    'course_alias' => $courseData['course_alias'],
                    'assignment_alias' => $assignmentAlias,
                    'problem_alias' => $problemData['request']['problem_alias'],
                ]));
                $problemAssignmentsMap[$assignmentAlias][] = $problemData;
            }
        }

        // Create & add students to course
        $studentsUsername = [];
        $studentsData = null;
        for ($i = 0; $i < $studentCount; $i++) {
            $studentsData = \OmegaUp\Test\Factories\Course::addStudentToCourse(
                $courseData
            );
            $studentsUsername[] = $studentsData->username;
        }

        // Clone the course
        $adminLogin = self::login($courseData['admin']);
        try {
            \OmegaUp\Controllers\Course::apiClone(new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
                'name' => \OmegaUp\Test\Utils::createRandomString(),
                'alias' => $courseData['course_alias'],
                'start_time' => \OmegaUp\Time::get()
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            $this->assertEquals('aliasInUse', $e->getMessage());
        }
    }

    /**
     * Create clone of a course with problems that have been changed their
     * visibility mode from public to private
     */
    public function testCreateCourseCloneWithPrivateProblems() {
        $homeworkCount = 2;
        $testCount = 2;
        $problemsPerAssignment = 2;
        $studentCount = 2;
        $assignmentProblemsMap = [];

        // Create course with assignments
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithNAssignmentsPerType([
            'homework' => $homeworkCount,
            'test' => $testCount
        ]);

        // Add problems to assignments
        $adminLogin = self::login($courseData['admin']);
        for ($i = 0; $i < $homeworkCount + $testCount; $i++) {
            $assignmentAlias = $courseData['assignment_aliases'][$i];
            $assignmentProblemsMap[$assignmentAlias] = [];

            for ($j = 0; $j < $problemsPerAssignment; $j++) {
                $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
                \OmegaUp\Controllers\Course::apiAddProblem(new \OmegaUp\Request([
                    'auth_token' => $adminLogin->auth_token,
                    'course_alias' => $courseData['course_alias'],
                    'assignment_alias' => $assignmentAlias,
                    'problem_alias' => $problemData['request']['problem_alias'],
                ]));
                $assignmentProblemsMap[$assignmentAlias][] = $problemData;
            }
        }
        foreach ($assignmentProblemsMap as $assignment => $problems) {
            foreach ($problems as $problem) {
                // All users can see public problems
                $problem = \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
                    'auth_token' => $adminLogin->auth_token,
                    'problem_alias' => $problem['problem']->alias,
                ]));

                $this->assertEquals(
                    2,
                    $problem['visibility'],
                    'Problem visibility must be public'
                );
            }

            // Update visibility mode to private for some problems
            $authorLogin = self::login($problems[0]['author']);

            \OmegaUp\Controllers\Problem::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $authorLogin->auth_token,
                'problem_alias' => $problems[0]['problem']->alias,
                'visibility' => 'private',
                'message' => 'public -> private',
            ]));

            try {
                $problem = \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
                    'auth_token' => $adminLogin->auth_token,
                    'problem_alias' => $problems[0]['problem']->alias,
                ]));
                $this->fail('Only creator can see private problem');
            } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
                // Expected
                $this->assertEquals('problemIsPrivate', $e->getMessage());
            }

            $problem = \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
                'auth_token' => $authorLogin->auth_token,
                'problem_alias' => $problems[0]['problem']->alias,
            ]));

            $this->assertEquals(
                0,
                $problem['visibility'],
                'Problem visibility must be private'
            );
        }

        $courseAlias = \OmegaUp\Test\Utils::createRandomString();

        $clonedCourseData = \OmegaUp\Controllers\Course::apiClone(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'alias' => $courseAlias,
            'start_time' => \OmegaUp\Time::get()
        ]));

        $this->assertEquals($courseAlias, $clonedCourseData['alias']);

        $response = \OmegaUp\Controllers\Course::apiDetails(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'alias' => $courseAlias
        ]));

        foreach ($response['assignments'] as $assignment) {
            $problems = \OmegaUp\Controllers\Course::apiAssignmentDetails(new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'assignment' => $assignment['alias'],
                'course' => $courseAlias
            ]));

            // All cloned assignments must have the same number of problems than the original ones
            $this->assertEquals(
                $problemsPerAssignment,
                count(
                    $problems['problems']
                )
            );
        }
    }

    public function testClonePrivateCourse() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $courseAlias = \OmegaUp\Test\Utils::createRandomString();

        $login = self::login($identity);
        try {
            \OmegaUp\Controllers\Course::apiClone(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'course_alias' => $courseData['course_alias'],
                    'name' => \OmegaUp\Test\Utils::createRandomString(),
                    'alias' => $courseAlias,
                    'start_time' => \OmegaUp\Time::get()
                ])
            );
            $this->fail('Should handle error');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    public function testClonePublicCourse() {
        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createUser();
        $adminLogin = self::login($admin);

        $courseData = \OmegaUp\Test\Factories\Course::createCourse(
            $admin,
            $adminLogin,
            \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC
        );

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $courseAlias = \OmegaUp\Test\Utils::createRandomString();

        $login = self::login($identity);
        $courseClonedData = \OmegaUp\Controllers\Course::apiClone(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'course_alias' => $courseData['course_alias'],
                'name' => \OmegaUp\Test\Utils::createRandomString(),
                'alias' => $courseAlias,
                'start_time' => \OmegaUp\Time::get()
            ])
        );

        $this->assertEquals($courseAlias, $courseClonedData['alias']);
    }

    public function testGenerateCloneCourseToken() {
        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createUser();
        $adminLogin = self::login($admin);

        $courseData = \OmegaUp\Test\Factories\Course::createCourse(
            $admin,
            $adminLogin,
            \OmegaUp\Controllers\Course::ADMISSION_MODE_PRIVATE
        );

        [
            'token' => $token,
        ] = \OmegaUp\Controllers\Course::apiGenerateTokenForCloneCourse(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
            ])
        );
    }
}
