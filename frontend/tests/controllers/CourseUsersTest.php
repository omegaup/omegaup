<?php

/**
 * Description of CourseUsersTest
 *
 * @author juan.pablo
 */

class CourseUsersTest extends \OmegaUp\Test\ControllerTestCase {
    public function testCourseActivityReport() {
        // Create a course with 5 assignments
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithAssignments(
            5
        );

        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $identity
        );

        $userLogin = self::login($identity);

        // Call the details API for the assignment that's already started.
        \OmegaUp\Controllers\Course::apiAssignmentDetails(new \OmegaUp\Request([
            'auth_token' => $userLogin->auth_token,
            'course' => $courseData['course_alias'],
            'assignment' => $courseData['assignment_aliases'][0],
        ]));

        // Call API
        $adminLogin = self::login($courseData['admin']);
        $response = \OmegaUp\Controllers\Course::apiActivityReport(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
        ]));

        // Check that we have entries in the log.
        $this->assertEquals(1, count($response['events']));
        $this->assertEquals(
            $identity->username,
            $response['events'][0]['username']
        );
        $this->assertEquals(0, $response['events'][0]['ip']);
        $this->assertEquals('open', $response['events'][0]['event']['name']);
    }
}
