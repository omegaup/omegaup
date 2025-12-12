<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

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

        $this->assertSame($courseAlias, $courseClonedData['alias']);
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
            $this->assertSame(
                $courseData['assignment_aliases'][$key],
                $assignment['alias']
            );
            $problems = \OmegaUp\Controllers\Course::apiAssignmentDetails(new \OmegaUp\Request([
                'assignment' => $assignment['alias'],
                'course' => $courseAlias,
                'auth_token' => $adminLogin->auth_token
            ]));
            foreach ($problems['problems'] as $index => $problem) {
                $this->assertSame(
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
            $this->assertSame('aliasInUse', $e->getMessage());
        }
    }

    /**
     * Trying to create a course using blank spaces in the alias
     */
    public function testCreateCourseCloneWithInvalidAlias() {
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
                'alias' => 'This is not a valid alias',
                'start_time' => \OmegaUp\Time::get()
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterInvalid', $e->getMessage());
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

                $this->assertSame(
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
                $this->assertSame('problemIsPrivate', $e->getMessage());
            }

            $problem = \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
                'auth_token' => $authorLogin->auth_token,
                'problem_alias' => $problems[0]['problem']->alias,
            ]));

            $this->assertSame(
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

        $this->assertSame($courseAlias, $clonedCourseData['alias']);

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
            $this->assertSame(
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
            $this->assertSame('userNotAllowed', $e->getMessage());
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
                'start_time' => \OmegaUp\Time::get(),
            ])
        );

        $this->assertSame($courseAlias, $courseClonedData['alias']);
    }

    public function testCloneCourseWithExtraProblems() {
        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createUser();
        $adminLogin = self::login($admin);

        $courseData = \OmegaUp\Test\Factories\Course::createCourse(
            $admin,
            $adminLogin,
            \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC
        );

        $adminLogin = self::login($courseData['admin']);
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $problems = [
            [
                'alias' => $problemData['problem']->alias,
                'is_extra_problem' => true,
            ],
        ];

        // Create the assignment with problems
        \OmegaUp\Controllers\Course::apiCreateAssignment(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'alias' => 'test-assignment',
            'assignment_type' => 'homework',
            'course_alias' => $courseData['course_alias'],
            'description' => \OmegaUp\Test\Utils::createRandomString(),
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'start_time' => \OmegaUp\Time::get(),
            'finish_time' => \OmegaUp\Time::get() + (2 * 60),
            'problems' => json_encode($problems),
        ]));

        $courseAlias = \OmegaUp\Test\Utils::createRandomString();

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        $courseClonedData = \OmegaUp\Controllers\Course::apiClone(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'course_alias' => $courseData['course_alias'],
                'name' => \OmegaUp\Test\Utils::createRandomString(),
                'alias' => $courseAlias,
                'start_time' => \OmegaUp\Time::get(),
            ])
        );
        $this->assertSame($courseAlias, $courseClonedData['alias']);

        [
            'assignments' => $assignmentsForClonedCourse,
        ] = \OmegaUp\Controllers\Course::apiDetails(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'alias' => $courseAlias
            ])
        );
        [
            'problems' => $problems,
        ] = \OmegaUp\Controllers\Course::apiAssignmentDetails(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'assignment' => $assignmentsForClonedCourse[0]['alias'],
                'course' => $courseData['course_alias']
            ])
        );
        $this->assertCount(1, $problems);
        $this->assertSame(
            $problemData['problem']->alias,
            $problems[0]['alias']
        );
        $this->assertTrue($problems[0]['is_extra_problem']);
    }

    public function testClonePublicCourseWithEmptyContent() {
        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createUser();
        $adminLogin = self::login($admin);

        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $admin,
            $adminLogin,
            \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC
        );

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $courseAliasForClonedCourse = \OmegaUp\Test\Utils::createRandomString();

        $login = self::login($identity);
        \OmegaUp\Controllers\Course::apiClone(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'course_alias' => $courseData['course_alias'],
                'name' => \OmegaUp\Test\Utils::createRandomString(),
                'alias' => $courseAliasForClonedCourse,
                'start_time' => \OmegaUp\Time::get(),
            ])
        );
        [
            'assignments' => $assignments,
        ] = \OmegaUp\Controllers\Course::apiDetails(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'alias' => $courseData['course_alias']
            ])
        );
        [
            'assignments' => $assignmentsForClonedCourse,
        ] = \OmegaUp\Controllers\Course::apiDetails(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'alias' => $courseAliasForClonedCourse
            ])
        );
        // All the fields should be the same in a cloned course, except
        // following ones
        foreach ($assignments as &$assignment) {
            unset($assignment['problemset_id']);
            unset($assignment['scoreboard_url']);
            unset($assignment['scoreboard_url_admin']);
        }
        foreach ($assignmentsForClonedCourse as &$assignment) {
            unset($assignment['problemset_id']);
            unset($assignment['scoreboard_url']);
            unset($assignment['scoreboard_url_admin']);
        }

        $this->assertEquals($assignments, $assignmentsForClonedCourse);
    }

    private function createCourseWithCloneToken(int $creationTime = (2 * 60)) {
        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createUser();
        $adminLogin = self::login($admin);

        $courseData = \OmegaUp\Test\Factories\Course::createCourse(
            $admin,
            $adminLogin,
            \OmegaUp\Controllers\Course::ADMISSION_MODE_PRIVATE
        );

        $currentTime = \OmegaUp\Time::get();
        \OmegaUp\Time::setTimeForTesting($currentTime - $creationTime);

        [
            'token' => $token,
        ] = \OmegaUp\Controllers\Course::apiGenerateTokenForCloneCourse(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        \OmegaUp\Time::setTimeForTesting($currentTime);

        return [
            'alias' => $courseData['course_alias'],
            'token' => $token,
            'username' => $admin->username,
            'userId' => $admin->user_id,
        ];
    }

    public function testGenerateCloneCourseToken() {
        ['token' => $token] = $this->createCourseWithCloneToken();

        $this->assertNotEmpty($token);
        $this->assertStringContainsString('v2.', $token);
        $this->assertStringContainsString('local.', $token);
    }

    public function testDecodeCloneCourseToken() {
        [
            'token' => $token,
            'alias' => $originalAlias,
        ] = $this->createCourseWithCloneToken();

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        $newAlias = \OmegaUp\Test\Utils::createRandomString();
        $clonedCourse = \OmegaUp\Controllers\Course::apiClone(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'token' => $token,
                'course_alias' => $originalAlias,
                'alias' => $newAlias,
                'name' => $newAlias,
                'start_time' => \OmegaUp\Time::get(),
            ])
        );

        $this->assertSame($clonedCourse['alias'], $newAlias);
    }

    public function testUseExpiredToken() {
        [
            'token' => $token,
            'alias' => $originalAlias,
        ] = $this->createCourseWithCloneToken((8 * 24 * 60 * 60));

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        $newAlias = \OmegaUp\Test\Utils::createRandomString();

        try {
            \OmegaUp\Controllers\Course::apiClone(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'token' => $token,
                    'course_alias' => $originalAlias,
                    'alias' => $newAlias,
                    'name' => $newAlias,
                    'start_time' => \OmegaUp\Time::get(),
                ])
            );
            $this->fail('It should fail');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame($e->getMessage(), 'tokenDecodeExpired');
        }
    }

    public function testUseTokenFromDifferentCourse() {
        ['alias' => $primaryAlias] = $this->createCourseWithCloneToken();
        ['token' => $secondaryToken] = $this->createCourseWithCloneToken();

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        $newAlias = \OmegaUp\Test\Utils::createRandomString();

        try {
            \OmegaUp\Controllers\Course::apiClone(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'token' => $secondaryToken,
                    'course_alias' => $primaryAlias,
                    'alias' => $newAlias,
                    'name' => $newAlias,
                    'start_time' => \OmegaUp\Time::get(),
                ])
            );
            $this->fail('It should fail');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame($e->getMessage(), 'tokenDecodeInvalid');
        }
    }

    public function testUseInvalidToken() {
        ['alias' => $originalAlias] = $this->createCourseWithCloneToken();

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);
        $token = 'v2.local.kq6WbVsQfV6C6IEL3ilJE2owJ5XS8iTj7yZqhdwZkp0QEgIrqM-Fzoz1VrbH7fWtss0b5o0p3xMs0fzADT-Iz4JKjb7juKgZSqB9YxJNY9mHtfE72YmqBnikVv6zzuRuGVbD5zXkgjQ3Wb4GWlPHrDLdW73tZ_dbqNYhZkZk1MPIGl8gqqaGoR2_F4i4Lg6zZUiNRNCfXUMSKqW68jENikHMa0RRJARKQGy5gn3p2qCWQA-Wkb_1IOgcN2aeJtOn';
        $newAlias = \OmegaUp\Test\Utils::createRandomString();
        try {
            \OmegaUp\Controllers\Course::apiClone(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'token' => $token,
                    'course_alias' => $originalAlias,
                    'alias' => $newAlias,
                    'name' => $newAlias,
                    'start_time' => \OmegaUp\Time::get(),
                ])
            );
            $this->fail('It should fail');
        } catch (\OmegaUp\Exceptions\ApiException $e) {
            $this->assertSame($e->getMessage(), 'tokenDecodeCorrupted');
        }
    }

    public function testCreateCourseCloneWithProblemOrder() {
        $problemsAssignment = 5;
        $assignmentProblemsMap = [];

        $courseData = \OmegaUp\Test\Factories\Course::createCourse();
        $assignmentAlias = \OmegaUp\Test\Utils::createRandomString();

        // Create the problems and then reorder them
        $adminLogin = self::login($courseData['admin']);
        foreach (range(0, $problemsAssignment - 1) as $index) {
            $problemData = \OmegaUp\Test\Factories\Problem::createProblem(
                new \OmegaUp\Test\Factories\ProblemParams([
                    'title' => "problem_{$index}",
                ])
            );
            $assignmentProblemsMap[]['alias'] = $problemData['problem']->alias;
        }

        $orderedProblems = [
            $assignmentProblemsMap[2],
            $assignmentProblemsMap[4],
            $assignmentProblemsMap[3],
            $assignmentProblemsMap[0],
            $assignmentProblemsMap[1],
        ];

        // Create the assignment with problems
        \OmegaUp\Controllers\Course::apiCreateAssignment(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'alias' => $assignmentAlias,
            'assignment_type' => 'homework',
            'course_alias' => $courseData['course_alias'],
            'description' => \OmegaUp\Test\Utils::createRandomString(),
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'start_time' => \OmegaUp\Time::get(),
            'finish_time' => \OmegaUp\Time::get() + (2 * 60),
            'problems' => json_encode($orderedProblems),
        ]));

        [
            'assignments' => $assignments,
        ] = \OmegaUp\Controllers\Course::apiDetails(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'alias' => $courseData['course_alias']
            ])
        );
        [$assignment] = $assignments;

        [
            'problems' => $problems,
        ] = \OmegaUp\Controllers\Course::apiAssignmentDetails(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'assignment' => $assignment['alias'],
                'course' => $courseData['course_alias']
            ])
        );

        foreach ($problems as $index => $problem) {
            $this->assertSame(
                $problem['alias'],
                $orderedProblems[$index]['alias']
            );
        }

        $newCourseAlias = \OmegaUp\Test\Utils::createRandomString();

        $clonedCourseData = \OmegaUp\Controllers\Course::apiClone(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
                'name' => \OmegaUp\Test\Utils::createRandomString(),
                'alias' => $newCourseAlias,
                'start_time' => \OmegaUp\Time::get(),
            ])
        );

        $this->assertSame($newCourseAlias, $clonedCourseData['alias']);

        [
            'assignments' => $assignments,
        ] = \OmegaUp\Controllers\Course::apiDetails(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'alias' => $newCourseAlias
            ])
        );
        [$assignment] = $assignments;

        [
            'problems' => $problems,
        ] = \OmegaUp\Controllers\Course::apiAssignmentDetails(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'assignment' => $assignment['alias'],
                'course' => $newCourseAlias
            ])
        );

        $this->assertSame($problemsAssignment, count($problems));

        foreach ($problems as $index => $problem) {
            $this->assertSame(
                $problem['alias'],
                $orderedProblems[$index]['alias'],
                'Problems order should be the same as the original course'
            );
        }
    }

    /*
     * Under13 users can't clone course.
     */
    public function testUserUnder13CannotCloneCourse() {
        $courseAlias = \OmegaUp\Test\Utils::createRandomString();
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();
        $defaultDate = strtotime('2022-01-01T00:00:00Z');
        \OmegaUp\Time::setTimeForTesting($defaultDate);
        // Create a 10 years-old user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams([
                'birthDate' => strtotime('2012-01-01T00:00:00Z'),
            ]),
        );

        // Log in the user and set the auth token in the new request
        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\Course::apiClone(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'course_alias' => $courseData['course_alias'],
                'name' => \OmegaUp\Test\Utils::createRandomString(),
                'alias' => $courseAlias,
                'start_time' => \OmegaUp\Time::get()
            ]));
            $this->fail(
                'Creating contests should not have been allowed for U13'
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('U13CannotPerform', $e->getMessage());
        }
    }
}
