<?php

/**
 * Description of CourseUsersTest
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

    public function testNormalUserCannotAccessToDetailsCourse() {
        // Create a course
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();

        // Create normal user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // User login
        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\Course::getCourseDetailsForTypeScript(
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
    }

    public function testUserCanAccessToCourseIntroWhenIsInvitedAsStudent() {
        // Create a course
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();

        // Create normal user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // First, admin adds user to course as a student
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $identity
        );

        // User login
        $login = self::login($identity);

        $response = \OmegaUp\Controllers\Course::getCourseDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        $this->assertEquals($response['entrypoint'], 'course_intro');
    }

    public function testUserCanAccessToCourseDetailsWhenIsInvitedAsAdmin() {
        // Create a course
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();

        // Create normal user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

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

        // User login
        $login = self::login($identity);

        $response = \OmegaUp\Controllers\Course::getCourseDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        $this->assertEquals($response['entrypoint'], 'course_details');
    }

    public function testGetNotificationForAddAdministrator() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $adminLogin = \OmegaUp\Test\ControllerTestCase::login(
            $courseData['admin']
        );
        \OmegaUp\Controllers\Course::apiAddAdmin(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $identity->username,
            'course_alias' => $courseData['course_alias'],
        ]));

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Notification::apiMyList(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        );
        $notificationContents = $response['notifications'][0]['contents'];

        $this->assertCount(1, $response['notifications']);
        $this->assertEquals(
            \OmegaUp\DAO\Notifications::COURSE_ADMINISTRATOR_ADDED,
            $notificationContents['type']
        );
        $this->assertEquals(
            $courseData['course_name'],
            $notificationContents['body']['localizationParams']['courseName']
        );
    }
}
