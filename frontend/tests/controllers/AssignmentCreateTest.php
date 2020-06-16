<?php

/**
 * @author alanboy
 */
class AssignmentCreateTest extends \OmegaUp\Test\ControllerTestCase {
    public function testAssignmentCreateWithInvalidDates() {
        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($admin);

        // Create the course
        $courseData = \OmegaUp\Test\Factories\Course::createCourse(
            $admin,
            $login
        );

        $courseAlias = $courseData['course_alias'];
        $courseStartTime = intval($courseData['request']['start_time']);

        // Create the assignment
        $assignmentAlias = \OmegaUp\Test\Utils::createRandomString();
        $course = \OmegaUp\DAO\Courses::getByAlias($courseAlias);
        if (is_null($course) || is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'alias' => $assignmentAlias,
            'description' => \OmegaUp\Test\Utils::createRandomString(),
            'start_time' => $courseStartTime - 10,
            'finish_time' => $courseStartTime + 120,
            'course_alias' => $courseAlias,
            'assignment_type' => 'homework',
            'course' => $course,
        ]);

        try {
            \OmegaUp\Controllers\Course::apiCreateAssignment($r);
            $this->fail(
                'Updating assignment should have failed due assignment start date incorrect'
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals(
                'courseAssignmentStartDateBeforeCourseStartDate',
                $e->getMessage()
            );
        }

        $r['start_time'] = $courseStartTime + 10;
        $r['finish_time'] = $courseStartTime - 10;

        try {
            \OmegaUp\Controllers\Course::apiCreateAssignment($r);
            $this->fail(
                'Updating assignment should have failed due assignment end date incorrect'
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals(
                'courseAssignmentEndDateBeforeCourseStartDate',
                $e->getMessage()
            );
        }

        $r['finish_time'] = null;
        $r['unlimited_duration'] = true;

        try {
            \OmegaUp\Controllers\Course::apiCreateAssignment($r);
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
}
