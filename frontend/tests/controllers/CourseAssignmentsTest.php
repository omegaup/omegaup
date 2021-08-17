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

    public function testAllAdminsCanSeeAdminMode() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();

        // Login admin and getting assignments list
        $adminLogin = self::login($courseData['admin']);
        [
            'assignments' => $assignments,
        ] = \OmegaUp\Controllers\Course::apiListAssignments(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias']
            ])
        );

        $details = \OmegaUp\Controllers\Course::getCourseAdminDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $assignments[0]['alias'],
            ])
        );

        $this->assertArrayHasKey('smartyProperties', $details);
        $this->assertArrayHasKey('payload', $details['smartyProperties']);
        $this->assertArrayHasKey(
            'details',
            $details['smartyProperties']['payload']
        );

        // A new student is added to course
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $identity
        );

        $userLogin = self::login($identity);

        // Student tries to access into the course in admin mode
        try {
            \OmegaUp\Controllers\Course::getCourseAdminDetailsForTypeScript(
                new \OmegaUp\Request([
                    'auth_token' => $userLogin->auth_token,
                    'course_alias' => $courseData['course_alias'],
                    'assignment_alias' => $assignments[0]['alias'],
                ])
            );
            $this->fail('User should not have access to admin mode');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }

        $adminLogin = self::login($courseData['admin']);

        // Making admin to the user previously created
        \OmegaUp\Controllers\Course::apiAddAdmin(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'usernameOrEmail' => $identity->username,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        $addedAdminLogin = self::login($identity);

        // Now, user is able to access into a course in admin mode
        $details = \OmegaUp\Controllers\Course::getCourseAdminDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $addedAdminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $assignments[0]['alias'],
            ])
        );

        $this->assertArrayHasKey('smartyProperties', $details);
        $this->assertArrayHasKey('payload', $details['smartyProperties']);
        $this->assertArrayHasKey(
            'details',
            $details['smartyProperties']['payload']
        );
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

        $adminPayload = \OmegaUp\Controllers\Course::getAssignmentDetailsForTypeScript(
            $courseData['admin'],
            $courseData['course'],
            \OmegaUp\DAO\Groups::getByPK(
                $courseData['course']->group_id
            ),
            $courseData['assignment']->alias
        )['smartyProperties']['payload'];

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

        $studentPayload = \OmegaUp\Controllers\Course::getAssignmentDetailsForTypeScript(
            $student['identity'],
            $courseData['course'],
            \OmegaUp\DAO\Groups::getByPK(
                $courseData['course']->group_id
            ),
            $courseData['assignment']->alias
        )['smartyProperties']['payload'];

        // The student should not see the runs
        $this->assertEmpty($studentPayload['currentAssignment']['runs']);
    }
}
