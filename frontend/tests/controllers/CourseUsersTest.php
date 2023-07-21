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
        $this->assertSame(1, count($response['events']));
        $this->assertSame(
            $identity->username,
            $response['events'][0]['username']
        );
        $this->assertSame(0, $response['events'][0]['ip']);
        $this->assertSame('open', $response['events'][0]['event']['name']);
    }

    public function testSearchUsersCourse() {
        // Create a course with 5 assignments
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();

        // Prepare assignment. Create problem
        $adminLogin = self::login($courseData['admin']);
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        \OmegaUp\Controllers\Course::apiAddProblem(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'assignment_alias' => $courseData['assignment_alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));

        // Create 10 users
        $numberOfStudents = 10;
        $identities = [];

        [
            'identity' => $identities[0],
        ] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams([
                'username' => 'test_course_user_0',
            ])
        );
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $identities[0]
        );

        [
            'identity' => $identities[1],
        ] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams([
                'username' => 'test_course_user_1',
            ])
        );
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $identities[1]
        );

        foreach (range(2, $numberOfStudents - 1) as $studentIndex) {
            [
                'identity' => $identities[$studentIndex],
            ] = \OmegaUp\Test\Factories\User::createUser();
            \OmegaUp\Test\Factories\Course::addStudentToCourse(
                $courseData,
                $identities[$studentIndex]
            );
        }

        // Call the details API for the assignment that's already started and
        // open a problem.
        foreach (range(0, $numberOfStudents - 1) as $studentIndex) {
            $userLogin = self::login($identities[$studentIndex]);
            \OmegaUp\Controllers\Course::apiAssignmentDetails(
                new \OmegaUp\Request([
                    'auth_token' => $userLogin->auth_token,
                    'course' => $courseData['course_alias'],
                    'assignment' => $courseData['assignment_alias'],
                ])
            );

            \OmegaUp\Controllers\Problem::apiDetails(
                new \OmegaUp\Request([
                    'auth_token' => $userLogin->auth_token,
                    'problemset_id' => $courseData['problemset_id'],
                    'prevent_problemset_open' => false,
                    'problem_alias' => $problemData['request']['problem_alias'],
                ])
            );
        }

        // Call search API as admin
        $adminLogin = self::login($courseData['admin']);
        $response = \OmegaUp\Controllers\Course::apiSearchUsers(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment_alias'],
                'query' => 'test_course_user_'
            ])
        )['results'];

        // Only two users match with the query
        $this->assertCount(2, $response);
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
            $this->assertSame('userNotAllowed', $e->getMessage());
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

        $this->assertSame($response['entrypoint'], 'course_intro');
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

        $this->assertSame($response['entrypoint'], 'course_details');
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
        $this->assertSame(
            \OmegaUp\DAO\Notifications::COURSE_ADMINISTRATOR_ADDED,
            $notificationContents['type']
        );
        $this->assertSame(
            $courseData['course_name'],
            $notificationContents['body']['localizationParams']['courseName']
        );
    }
}
