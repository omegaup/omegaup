<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

class CourseAssignmentsTest extends \OmegaUp\Test\ControllerTestCase {
    public function testAssignmentsWithOriginalOrder() {
        // Create a course with 5 assignments
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();

        $adminLogin = self::login($courseData['admin']);
        foreach (range(1, 5) as $index) {
            \OmegaUp\Controllers\Course::apiCreateAssignment(
                new \OmegaUp\Request([
                    'auth_token' => $adminLogin->auth_token,
                    'name' => "AssignmentNo {$index}",
                    'alias' => \OmegaUp\Test\Utils::createRandomString(),
                    'description' => \OmegaUp\Test\Utils::createRandomString(),
                    'start_time' => (\OmegaUp\Time::get() + 60),
                    'finish_time' => (\OmegaUp\Time::get() + 120),
                    'course_alias' => $courseData['course_alias'],
                    'assignment_type' => 'homework'
                ])
            );
        }

        [
            'assignments' => $assignments
        ] = \OmegaUp\Controllers\Course::apiListAssignments(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias']
            ])
        );

        foreach ($assignments as $index => $assignment) {
            $this->assertEquals($assignment['order'], $index + 1);
        }
    }

    public function testOrderAssignments() {
        // Create a course with 5 assignments
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithAssignments(
            5
        );

        // Login admin and getting assignments list
        $adminLogin = self::login($courseData['admin']);
        $assignments = \OmegaUp\Controllers\Course::apiListAssignments(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias']
            ])
        );

        $aliases = [];
        foreach ($assignments['assignments'] as $assignment) {
            $aliases[] = $assignment['alias'];
        }

        \OmegaUp\Controllers\Course::apiUpdateAssignmentsOrder(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
                'assignments' => json_encode($aliases),
            ])
        );

        // Getting one more time assignments list with original order
        $assignments = \OmegaUp\Controllers\Course::apiListAssignments(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias']
            ])
        );

        // ordering assignments
        $assignments['assignments'][0]['order'] = 1;
        $assignments['assignments'][1]['order'] = 2;
        $assignments['assignments'][2]['order'] = 3;
        $assignments['assignments'][3]['order'] = 4;
        $assignments['assignments'][4]['order'] = 5;

        // Asserting assignments order is the same that the original
        $i = 1;
        foreach ($assignments['assignments'] as $index => $assignment) {
            $originalOrder[$index] = [
                'alias' => $assignments['assignments'][$index]['alias'],
                'order' => $assignments['assignments'][$index]['order']
            ];
            $this->assertEquals(
                $assignments['assignments'][$index]['order'],
                $i++
            );
        }

        // Reordering assignments
        $aliases = [
            $assignments['assignments'][2]['alias'],
            $assignments['assignments'][3]['alias'],
            $assignments['assignments'][1]['alias'],
            $assignments['assignments'][4]['alias'],
            $assignments['assignments'][0]['alias'],
        ];

        \OmegaUp\Controllers\Course::apiUpdateAssignmentsOrder(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
                'assignments' => json_encode($aliases),
            ])
        );
        $assignments = \OmegaUp\Controllers\Course::apiListAssignments(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias']
            ])
        );

        // Asserting that the new ordering is not equal that original
        foreach ($assignments['assignments'] as $index => $assignment) {
            $this->assertNotEquals(
                $assignment['alias'],
                $originalOrder[$index]['alias']
            );
        }
    }

    public function testGetAssignmentDetails() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();

        // Create a problem, a student and a run
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        $adminLogin = self::login($courseData['admin']);

        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $adminLogin,
            $courseData['course_alias'],
            $courseData['assignment_alias'],
            [ $problemData ]
        );

        $student = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $student['identity']
        );

        \OmegaUp\Test\Factories\Run::gradeRun(
            \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
                $problemData,
                $courseData,
                $student['identity']
            )
        );

        // Need to re-login because of the student login for submitting the run
        $adminLogin = self::login($courseData['admin']);

        $adminPayload = \OmegaUp\Controllers\Course::getCourseDetailsForTypeScript(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'assignment_alias' => $courseData['assignment']->alias,
        ]))['templateProperties']['payload'];

        $this->assertEquals(
            $courseData['course']->name,
            $adminPayload['courseDetails']['name']
        );
        $this->assertEmpty($adminPayload['courseDetails']['clarifications']);

        $this->assertEquals(
            $courseData['assignment']->alias,
            $adminPayload['currentAssignment']['alias']
        );
        $this->assertCount(1, $adminPayload['currentAssignment']['problems']);
        $this->assertCount(1, $adminPayload['currentAssignment']['runs']);

        $studentLogin = self::login($student['identity']);
        $studentPayload = \OmegaUp\Controllers\Course::getCourseDetailsForTypeScript(new \OmegaUp\Request([
            'auth_token' => $studentLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'assignment_alias' => $courseData['assignment']->alias,
        ]))['templateProperties']['payload'];

        // The student should not see the runs
        $this->assertEmpty($studentPayload['currentAssignment']['runs']);
    }

    public function testAssignmentForbiddenAccess() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            startTimeDelay: 60
        );

        $student = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $student['identity']
        );

        $adminLogin = self::login($courseData['admin']);
        $adminPayload = \OmegaUp\Controllers\Course::getCourseDetailsForTypeScript(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'assignment_alias' => $courseData['assignment']->alias,
        ]))['templateProperties']['payload'];

        // Admin should not have problems even when the assignment has not started yet
        $this->assertEquals(
            $courseData['course']->name,
            $adminPayload['courseDetails']['name']
        );
        $this->assertEquals(
            $courseData['assignment']->alias,
            $adminPayload['currentAssignment']['alias']
        );

        // Student should throw an exception as the assignment has not started yet
        $studentLogin = self::login($student['identity']);
        try {
            \OmegaUp\Controllers\Course::getCourseDetailsForTypeScript(new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment']->alias,
            ]));
            $this->fail('Should have thrown a ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals($e->getMessage(), 'assignmentNotStarted');
        }

        try {
            \OmegaUp\Controllers\Course::getAssignmentDetails(
                $student['identity'],
                null,
                $courseData['course'],
                \OmegaUp\DAO\Groups::getByPK(
                    intval($courseData['course']->group_id)
                ),
                $courseData['assignment_alias'],
            );
            $this->fail('Should have thrown a ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals($e->getMessage(), 'assignmentNotStarted');
        }

        try {
            \OmegaUp\Controllers\Course::getArenaCourseDetailsForTypeScript(
                new \OmegaUp\Request([
                    'auth_token' => $studentLogin->auth_token,
                    'course_alias' => $courseData['course_alias'],
                    'assignment_alias' => $courseData['assignment']->alias,
                ])
            );
            $this->fail('Should have thrown a ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('assignmentNotStarted', $e->getMessage());
        }

        // User not registered in course should throw an exception
        $extraStudent = \OmegaUp\Test\Factories\User::createUser();
        $extraStudentLogin = self::login($extraStudent['identity']);

        try {
            \OmegaUp\Controllers\Course::getCourseDetailsForTypeScript(new \OmegaUp\Request([
                'auth_token' => $extraStudentLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment']->alias,
            ]));
            $this->fail('Should have thrown a ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals($e->getMessage(), 'userNotAllowed');
        }

        try {
            \OmegaUp\Controllers\Course::getAssignmentDetails(
                $extraStudent['identity'],
                null,
                $courseData['course'],
                \OmegaUp\DAO\Groups::getByPK(
                    intval($courseData['course']->group_id)
                ),
                $courseData['assignment_alias'],
            );
            $this->fail('Should have thrown a ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals($e->getMessage(), 'userNotAllowed');
        }

        try {
            \OmegaUp\Controllers\Course::getArenaCourseDetailsForTypeScript(
                new \OmegaUp\Request([
                    'auth_token' => $extraStudentLogin->auth_token,
                    'course_alias' => $courseData['course_alias'],
                    'assignment_alias' => $courseData['assignment']->alias,
                ])
            );
            $this->fail('Should have thrown a ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals($e->getMessage(), 'userNotAllowed');
        }
    }

    public function testGetArenaCourseDetails() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();

        // Create and add two problems
        $problemsData = [
            \OmegaUp\Test\Factories\Problem::createProblem(),
            \OmegaUp\Test\Factories\Problem::createProblem(),
        ];

        $adminLogin = self::login($courseData['admin']);

        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $adminLogin,
            $courseData['course_alias'],
            $courseData['assignment_alias'],
            [$problemsData[0], $problemsData[1]]
        );

        $payload = \OmegaUp\Controllers\Course::getArenaCourseDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment']->alias,
            ])
        )['templateProperties']['payload'];
        $this->assertEquals(
            $courseData['course']->name,
            $payload['course']['name']
        );
        $this->assertEquals(
            $courseData['assignment']->alias,
            $payload['assignment']['alias']
        );
        $this->assertCount(2, $payload['problems']);
        $this->assertEquals(
            $problemsData[0]['problem']->alias,
            $payload['problems'][0]['alias']
        );
        $this->assertEquals(
            $problemsData[1]['problem']->alias,
            $payload['problems'][1]['alias']
        );
        $this->assertNull($payload['currentProblem']);
        $this->assertEmpty($payload['clarifications']);

        $students = [];
        for ($i = 0; $i < 3; $i++) {
            $students[] = \OmegaUp\Test\Factories\User::createUser();
            \OmegaUp\Test\Factories\Course::addStudentToCourse(
                $courseData,
                $students[$i]['identity']
            );
            \OmegaUp\Test\Factories\Run::gradeRun(
                \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
                    $problemsData[0],
                    $courseData,
                    $students[$i]['identity']
                )
            );
        }

        $adminLogin = self::login($courseData['admin']);
        $payload = \OmegaUp\Controllers\Course::getArenaCourseDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment']->alias,
                'problem_alias' => $problemsData[0]['problem']->alias,
            ])
        )['templateProperties']['payload'];
        $this->assertEquals(
            $courseData['course']->name,
            $payload['course']['name']
        );
        $this->assertEquals(
            $courseData['assignment']->alias,
            $payload['assignment']['alias']
        );
        $this->assertCount(2, $payload['problems']);
        $this->assertEquals(
            $problemsData[0]['problem']->alias,
            $payload['problems'][0]['alias']
        );
        $this->assertEquals(
            $problemsData[1]['problem']->alias,
            $payload['problems'][1]['alias']
        );
        $this->assertEquals(
            $problemsData[0]['problem']->alias,
            $payload['currentProblem']['alias']
        );
        $this->assertCount(3, $payload['runs']);
        $this->assertEmpty($payload['clarifications']);

        $studentLogin = self::login($students[0]['identity']);
        $payload = \OmegaUp\Controllers\Course::getArenaCourseDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment']->alias,
                'problem_alias' => $problemsData[0]['problem']->alias,
            ])
        )['templateProperties']['payload'];
        $this->assertEquals(
            $courseData['course']->name,
            $payload['course']['name']
        );
        $this->assertEquals(
            $courseData['assignment']->alias,
            $payload['assignment']['alias']
        );
        $this->assertCount(2, $payload['problems']);
        $this->assertEquals(
            $problemsData[0]['problem']->alias,
            $payload['problems'][0]['alias']
        );
        $this->assertEquals(
            $problemsData[1]['problem']->alias,
            $payload['problems'][1]['alias']
        );
        $this->assertEquals(
            $problemsData[0]['problem']->alias,
            $payload['currentProblem']['alias']
        );
        $this->assertCount(1, $payload['runs']);
        $this->assertEmpty($payload['clarifications']);
    }

    public function testAssignmentOpened() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();

        $problemsData = [
            \OmegaUp\Test\Factories\Problem::createProblem(),
            \OmegaUp\Test\Factories\Problem::createProblem(),
        ];

        $adminLogin = self::login($courseData['admin']);

        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $adminLogin,
            $courseData['course_alias'],
            $courseData['assignment_alias'],
            [$problemsData[0], $problemsData[1]]
        );

        // The assignment must not be opened yet
        $assignments = \OmegaUp\Controllers\Course::getCourseDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
            ])
        )['templateProperties']['payload']['details']['assignments'];
        $this->assertCount(1, $assignments);
        $this->assertFalse($assignments[0]['opened']);

        $student = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $student['identity']
        );

        // Now, as a student open the assignment through getArenaCourseDetailsForTypeScript
        $studentLogin = self::login($student['identity']);
        \OmegaUp\Controllers\Course::getArenaCourseDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment']->alias,
                'problem_alias' => $problemsData[0]['problem']->alias,
            ])
        );

        // Now check the assignment must be opened
        $assignments = \OmegaUp\Controllers\Course::getCourseDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
            ])
        )['templateProperties']['payload']['course']['assignments'];
        $this->assertCount(1, $assignments);
        $this->assertTrue($assignments[0]['opened']);
    }
}
