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

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $identity
        );

        $userLogin = self::login($identity);

        // Call the details API for the assignment that's already started.
        \OmegaUp\Controllers\Course::apiAssignmentDetails(
            new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
                'course' => $courseData['course_alias'],
                'assignment' => $courseData['assignment_aliases'][0],
            ])
        );

        // Call API
        $adminLogin = self::login($courseData['admin']);
        $response = \OmegaUp\Controllers\Course::apiActivityReport(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        // Check that we have entries in the log.
        $this->assertEquals(1, count($response['events']));
        $this->assertEquals(
            $identity->username,
            $response['events'][0]['username']
        );
        $this->assertEquals(0, $response['events'][0]['ip']);
        $this->assertEquals('open', $response['events'][0]['event']['name']);
    }

    /**
     * Users are added as admins and they have access to edit the course
     */
    public function testUserAdminCourse() {
        // Create a course with 5 assignments
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();

        // Create normal user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // User login
        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\Course::getCourseDetailsForSmarty(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'course_alias' => $courseData['course_alias'],
                ])
            );
            $this->fail(
                'Should have failed because identity has not been invited to course'
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }

        // Admin login
        $adminLogin = self::login($courseData['admin']);

        // First, admin adds user to course as a student
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $identity
        );

        $response = \OmegaUp\Controllers\Course::getCourseDetailsForSmarty(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        $this->assertEquals($response['template'], 'arena.course.intro.tpl');

        // Admin login
        $adminLogin = self::login($courseData['admin']);

        // Making admin to the user previously created
        \OmegaUp\Controllers\Course::apiAddAdmin(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'usernameOrEmail' => $identity->username,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        $response = \OmegaUp\Controllers\Course::getCourseDetailsForSmarty(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        $this->assertEquals($response['template'], 'course.details.tpl');
    }
}
