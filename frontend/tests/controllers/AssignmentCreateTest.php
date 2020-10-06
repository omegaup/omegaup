<?php

/**
 * @author alanboy
 */
class AssignmentCreateTest extends \OmegaUp\Test\ControllerTestCase {
    private static $login = null;
    private static $courseAlias = null;
    private static $courseStartTime = null;
    private static $assignmentAlias = null;
    private static $course = null;

    public function setUp(): void {
        parent::setUp();

        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createUser();
        self::$login = self::login($admin);

        // Create the course
        $courseData = \OmegaUp\Test\Factories\Course::createCourse(
            $admin,
            self::$login
        );

        self::$courseAlias = $courseData['course_alias'];
        self::$courseStartTime = intval($courseData['request']['start_time']);

        // Create the assignment
        self::$assignmentAlias = \OmegaUp\Test\Utils::createRandomString();
        self::$course = \OmegaUp\DAO\Courses::getByAlias(self::$courseAlias);
        if (is_null(self::$course) || is_null(self::$course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }
    }

    public function testAssignmentStartTimeBeforeCourseStartTime() {
        try {
            \OmegaUp\Controllers\Course::apiCreateAssignment(
                new \OmegaUp\Request([
                    'auth_token' => self::$login->auth_token,
                    'name' => \OmegaUp\Test\Utils::createRandomString(),
                    'alias' => self::$assignmentAlias,
                    'description' => \OmegaUp\Test\Utils::createRandomString(),
                    'start_time' => self::$courseStartTime - 10,
                    'finish_time' => self::$courseStartTime + 120,
                    'course_alias' => self::$courseAlias,
                    'assignment_type' => 'homework',
                    'course' => self::$course,
                ])
            );
            $this->fail(
                'Updating assignment should have failed due assignment start date incorrect'
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals(
                'courseAssignmentStartDateBeforeCourseStartDate',
                $e->getMessage()
            );
        }
    }

    public function testAssignmentFinishTimeBeforeCourseStartTime() {
        try {
            \OmegaUp\Controllers\Course::apiCreateAssignment(
                new \OmegaUp\Request([
                    'auth_token' => self::$login->auth_token,
                    'name' => \OmegaUp\Test\Utils::createRandomString(),
                    'alias' => self::$assignmentAlias,
                    'description' => \OmegaUp\Test\Utils::createRandomString(),
                    'start_time' => self::$courseStartTime + 10,
                    'finish_time' => self::$courseStartTime - 10,
                    'course_alias' => self::$courseAlias,
                    'assignment_type' => 'homework',
                    'course' => self::$course,
                ])
            );
            $this->fail(
                'Updating assignment should have failed due assignment end date incorrect'
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals(
                'courseAssignmentEndDateBeforeCourseStartDate',
                $e->getMessage()
            );
        }
    }

    public function testInvalidFinishTimeWhenUnlimitedDurationCourse() {
        try {
            \OmegaUp\Controllers\Course::apiCreateAssignment(
                new \OmegaUp\Request([
                    'auth_token' => self::$login->auth_token,
                    'name' => \OmegaUp\Test\Utils::createRandomString(),
                    'alias' => self::$assignmentAlias,
                    'description' => \OmegaUp\Test\Utils::createRandomString(),
                    'start_time' => self::$courseStartTime + 10,
                    'finish_time' => null,
                    'unlimited_duration' => true,
                    'course_alias' => self::$courseAlias,
                    'assignment_type' => 'homework',
                    'course' => self::$course,
                ])
            );
            $this->fail(
                'Updating assignment should have failed due assignment unlimted duration not allowed'
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals(
                'courseDoesNotHaveUnlimitedDuration',
                $e->getMessage()
            );
        }
    }

    public function testCreateAssignmentWithInvalidAlias() {
        try {
            \OmegaUp\Controllers\Course::apiCreateAssignment(
                new \OmegaUp\Request([
                    'auth_token' => self::$login->auth_token,
                    'name' => \OmegaUp\Test\Utils::createRandomString(),
                    'alias' => 'invalid alias',
                    'description' => \OmegaUp\Test\Utils::createRandomString(),
                    'start_time' => self::$courseStartTime,
                    'finish_time' => self::$courseStartTime + 120,
                    'course_alias' => self::$courseAlias,
                    'assignment_type' => 'homework',
                    'course' => self::$course,
                ])
            );
            $this->fail('Should have thrown an exception');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals('parameterInvalid', $e->getMessage());
        }
    }
}
