<?php

/**
 * Tests for course clarifications
 */
class CourseClarificationsTest extends \OmegaUp\Test\ControllerTestCase {
    public function setUp(): void {
        parent::setUp();

        // Create four problems
        $this->problems = [];
        for ($i = 0; $i < 4; $i++) {
            $this->problems[] = \OmegaUp\Test\Factories\Problem::createProblem();
        }

        // Create a course with two assignments
        $course = \OmegaUp\Test\Factories\Course::createCourseWithAssignments(
            2
        );
        $this->course = \OmegaUp\DAO\Courses::getByAlias(
            $course['course_alias']
        );
        if (is_null($this->course)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'courseNotFound'
            );
        }
        $this->assignmentsAliases = $course['assignment_aliases'];
        $this->courseAdmin = $course['admin'];

        $login = self::login($this->courseAdmin);
        // Add 1 problem to first assignment
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $this->course->alias,
            $this->assignmentsAliases[0],
            [ $this->problems[0] ]
        );

        // Add 3 remaining problems to second assignment
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $this->course->alias,
            $this->assignmentsAliases[1],
            [
                $this->problems[1],
                $this->problems[2],
                $this->problems[3]
            ]
        );

        // Add two students to course
        $this->students = [];
        for ($i = 0; $i < 2; $i++) {
            $this->students[] = \OmegaUp\Test\Factories\User::createUser();
            \OmegaUp\Test\Factories\Course::addStudentToCourse(
                $course,
                $this->students[$i]['identity']
            );
        }
    }

    public function testCreateCourseClarification() {
        // Take one course student and create a clarification for
        // the problem of the first assignment
        $login = self::login($this->students[0]['identity']);

        $message = 'Test message';
        $clarification = \OmegaUp\Controllers\Clarification::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'course_alias' => $this->course->alias,
                'assignment_alias' => $this->assignmentsAliases[0],
                'problem_alias' => $this->problems[0]['problem']->alias,
                'message' => $message,
            ])
        );

        $this->assertEquals($message, $clarification['message']);

        // Verify notification for admin
        $adminUser = \OmegaUp\DAO\Users::getByPK(
            intval($this->courseAdmin->user_id)
        );
        if (is_null($adminUser)) {
            return;
        }
        $notifications = \OmegaUp\DAO\Notifications::getUnreadNotifications(
            $adminUser
        );
        $this->assertCount(1, $notifications);

        $contents = json_decode($notifications[0]['contents'], true);
        $this->assertEquals(
            \OmegaUp\DAO\Notifications::COURSE_CLARIFICATION_REQUEST,
            $contents['type']
        );
        $this->assertEquals(
            $this->course->name,
            $contents['body']['localizationParams']['courseName']
        );
        $this->assertEquals(
            $this->problems[0]['problem']->alias,
            $contents['body']['localizationParams']['problemAlias']
        );
    }

    public function testListClarificationsForCourse() {
        // First student will submit clarification for problem0, problem1
        \OmegaUp\Controllers\Clarification::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => self::login(
                    $this->students[0]['identity']
                )->auth_token,
                'course_alias' => $this->course->alias,
                'assignment_alias' => $this->assignmentsAliases[0],
                'problem_alias' => $this->problems[0]['problem']->alias,
                'message' => 'Test message',
            ])
        );

        \OmegaUp\Controllers\Clarification::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => self::login(
                    $this->students[0]['identity']
                )->auth_token,
                'course_alias' => $this->course->alias,
                'assignment_alias' => $this->assignmentsAliases[1],
                'problem_alias' => $this->problems[1]['problem']->alias,
                'message' => 'Test message',
            ])
        );

        // Second student will submit clarification for problem1, problem2 and problem3
        for ($i = 1; $i < 4; $i++) {
            \OmegaUp\Controllers\Clarification::apiCreate(
                new \OmegaUp\Request([
                    'auth_token' => self::login(
                        $this->students[1]['identity']
                    )->auth_token,
                    'course_alias' => $this->course->alias,
                    'assignment_alias' => $this->assignmentsAliases[1],
                    'problem_alias' => $this->problems[$i]['problem']->alias,
                    'message' => 'Test message',
                ])
            );
        }

        // Now list clarifications for each user:

        // Student0 should get 2 clarifications
        $response = \OmegaUp\Controllers\Course::apiClarifications(
            new \OmegaUp\Request([
                'auth_token' => self::login(
                    $this->students[0]['identity']
                )->auth_token,
                'course_alias' => $this->course->alias,
            ])
        );
        $this->assertCount(2, $response['clarifications']);

        // Student1 should get 3 clarifications
        $response = \OmegaUp\Controllers\Course::apiClarifications(
            new \OmegaUp\Request([
                'auth_token' => self::login(
                    $this->students[1]['identity']
                )->auth_token,
                'course_alias' => $this->course->alias,
            ])
        );
        $this->assertCount(3, $response['clarifications']);

        // Admin should get 2 + 3 clarifications
        $response = \OmegaUp\Controllers\Course::apiClarifications(
            new \OmegaUp\Request([
                'auth_token' => self::login($this->courseAdmin)->auth_token,
                'course_alias' => $this->course->alias,
            ])
        );
        $this->assertCount(5, $response['clarifications']);

        // Random user should receive an exception
        $randomUser = \OmegaUp\Test\Factories\User::createUser();
        try {
            \OmegaUp\Controllers\Course::apiClarifications(
                new \OmegaUp\Request([
                    'auth_token' => self::login(
                        $randomUser['identity']
                    )->auth_token,
                    'course_alias' => $this->course->alias,
                ])
            );
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    public function testListClarificationsForProblemInCourse() {
        // First student will submit clarification for problem0, problem1
        \OmegaUp\Controllers\Clarification::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => self::login(
                    $this->students[0]['identity']
                )->auth_token,
                'course_alias' => $this->course->alias,
                'assignment_alias' => $this->assignmentsAliases[0],
                'problem_alias' => $this->problems[0]['problem']->alias,
                'message' => 'Test message',
            ])
        );

        \OmegaUp\Controllers\Clarification::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => self::login(
                    $this->students[0]['identity']
                )->auth_token,
                'course_alias' => $this->course->alias,
                'assignment_alias' => $this->assignmentsAliases[1],
                'problem_alias' => $this->problems[1]['problem']->alias,
                'message' => 'Test message',
            ])
        );

        // Second student will submit clarification for problem1
        \OmegaUp\Controllers\Clarification::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => self::login(
                    $this->students[1]['identity']
                )->auth_token,
                'course_alias' => $this->course->alias,
                'assignment_alias' => $this->assignmentsAliases[1],
                'problem_alias' => $this->problems[1]['problem']->alias,
                'message' => 'Test message',
            ])
        );

        // Now list clarifications for problem0 (1) and problem1 (2)
        $response = \OmegaUp\Controllers\Course::apiProblemClarifications(
            new \OmegaUp\Request([
                'auth_token' => self::login(
                    $this->courseAdmin
                )->auth_token,
                'course_alias' => $this->course->alias,
                'assignment_alias' => $this->assignmentsAliases[0],
                'problem_alias' => $this->problems[0]['problem']->alias
            ])
        );
        $this->assertCount(1, $response['clarifications']);

        $response = \OmegaUp\Controllers\Course::apiProblemClarifications(
            new \OmegaUp\Request([
                'auth_token' => self::login(
                    $this->courseAdmin
                )->auth_token,
                'course_alias' => $this->course->alias,
                'assignment_alias' => $this->assignmentsAliases[1],
                'problem_alias' => $this->problems[1]['problem']->alias
            ])
        );
        $this->assertCount(2, $response['clarifications']);

        // Random user should receive an exception
        $randomUser = \OmegaUp\Test\Factories\User::createUser();
        try {
            \OmegaUp\Controllers\Course::apiClarifications(
                new \OmegaUp\Request([
                    'auth_token' => self::login(
                        $randomUser['identity']
                    )->auth_token,
                    'course_alias' => $this->course->alias,
                ])
            );
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }
}
