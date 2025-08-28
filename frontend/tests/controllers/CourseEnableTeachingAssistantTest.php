<?php

class CourseEnableTeachingAssistantTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Test that newly created courses have AI Teaching Assistant disabled by default
     */
    public function testNewCourseHasTeachingAssistantDisabledByDefault() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        $courseAlias = \OmegaUp\Test\Utils::createRandomString();
        $courseName = \OmegaUp\Test\Utils::createRandomString();

        $response = \OmegaUp\Controllers\Course::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => $courseName,
            'alias' => $courseAlias,
            'description' => \OmegaUp\Test\Utils::createRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120),
        ]));

        $this->assertSame('ok', $response['status']);

        $course = \OmegaUp\DAO\Courses::getByAlias($courseAlias);
        $this->assertNotNull($course);
        $this->assertFalse($course->teaching_assistant_enabled);

        $courseDetails = \OmegaUp\Controllers\Course::apiDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'alias' => $courseAlias,
        ]));

        $this->assertFalse($courseDetails['teaching_assistant_enabled']);
    }

    /**
     * Test that course admin can toggle AI Teaching Assistant
     */
    public function testAdminCanToggleTeachingAssistant() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();
        $adminLogin = self::login($courseData['admin']);

        $course = \OmegaUp\DAO\Courses::getByAlias($courseData['course_alias']);
        $this->assertFalse($course->teaching_assistant_enabled);

        // Toggle AI TA to enabled
        $response = \OmegaUp\Controllers\Course::apiToggleTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        $this->assertSame('ok', $response['status']);
        $this->assertTrue($response['teaching_assistant_enabled']);

        $course = \OmegaUp\DAO\Courses::getByAlias($courseData['course_alias']);
        $this->assertTrue($course->teaching_assistant_enabled);

        $courseDetails = \OmegaUp\Controllers\Course::apiDetails(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'alias' => $courseData['course_alias'],
        ]));
        $this->assertTrue($courseDetails['teaching_assistant_enabled']);

        $response = \OmegaUp\Controllers\Course::apiToggleTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        $this->assertSame('ok', $response['status']);
        $this->assertFalse($response['teaching_assistant_enabled']);

        $course = \OmegaUp\DAO\Courses::getByAlias($courseData['course_alias']);
        $this->assertFalse($course->teaching_assistant_enabled);

        $courseDetails = \OmegaUp\Controllers\Course::apiDetails(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'alias' => $courseData['course_alias'],
        ]));
        $this->assertFalse($courseDetails['teaching_assistant_enabled']);
    }

    /**
     * Test that only course admins can toggle AI Teaching Assistant
     */
    public function testOnlyAdminCanToggleTeachingAssistant() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();

        ['identity' => $regularUser] = \OmegaUp\Test\Factories\User::createUser();
        $regularLogin = self::login($regularUser);

        try {
            \OmegaUp\Controllers\Course::apiToggleTeachingAssistant(
                new \OmegaUp\Request([
                    'auth_token' => $regularLogin->auth_token,
                    'course_alias' => $courseData['course_alias'],
                ])
            );
            $this->fail('Regular user should not be able to toggle AI TA');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }

        $course = \OmegaUp\DAO\Courses::getByAlias($courseData['course_alias']);
        $this->assertFalse($course->teaching_assistant_enabled);
    }

    /**
     * Test that teaching assistants cannot toggle AI Teaching Assistant
     */
    public function testTeachingAssistantCannotToggleTeachingAssistant() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();
        $adminLogin = self::login($courseData['admin']);

        ['identity' => $teachingAssistant] = \OmegaUp\Test\Factories\User::createUser();

        // Add user as teaching assistant
        \OmegaUp\Controllers\Course::apiAddTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'usernameOrEmail' => $teachingAssistant->username,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        $taLogin = self::login($teachingAssistant);

        // Teaching assistant should not be able to toggle AI TA
        try {
            \OmegaUp\Controllers\Course::apiToggleTeachingAssistant(
                new \OmegaUp\Request([
                    'auth_token' => $taLogin->auth_token,
                    'course_alias' => $courseData['course_alias'],
                ])
            );
            $this->fail(
                'Teaching assistant should not be able to toggle AI TA'
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }

        $course = \OmegaUp\DAO\Courses::getByAlias($courseData['course_alias']);
        $this->assertFalse($course->teaching_assistant_enabled);
    }

    /**
     * Test toggle with non-existent course
     */
    public function testToggleTeachingAssistantWithNonExistentCourse() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\Course::apiToggleTeachingAssistant(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'course_alias' => 'non-existent-course',
                ])
            );
            $this->fail('Should have thrown exception for non-existent course');
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            $this->assertSame('courseNotFound', $e->getMessage());
        }
    }

    /**
     * Test toggle with invalid course alias
     */
    public function testToggleTeachingAssistantWithInvalidAlias() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\Course::apiToggleTeachingAssistant(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'course_alias' => 'invalid alias with spaces',
                ])
            );
            $this->fail('Should have thrown exception for invalid alias');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame('parameterInvalid', $e->getMessage());
        }
    }

    /**
     * Test that the AI Teaching Assistant field is included in course listing APIs
     */
    public function testTeachingAssistantFieldInCourseListing() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();
        $adminLogin = self::login($courseData['admin']);

        // Enable AI TA
        \OmegaUp\Controllers\Course::apiToggleTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        $courseDetails = \OmegaUp\Controllers\Course::apiDetails(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'alias' => $courseData['course_alias'],
        ]));

        $this->assertArrayHasKey('teaching_assistant_enabled', $courseDetails);
        $this->assertTrue($courseDetails['teaching_assistant_enabled']);
    }

    /**
     * Test multiple consecutive toggles
     */
    public function testMultipleConsecutiveToggles() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();
        $adminLogin = self::login($courseData['admin']);

        $course = \OmegaUp\DAO\Courses::getByAlias($courseData['course_alias']);
        $this->assertFalse($course->teaching_assistant_enabled);

        // Toggle 1: Enable
        $response = \OmegaUp\Controllers\Course::apiToggleTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
            ])
        );
        $this->assertTrue($response['teaching_assistant_enabled']);

        // Toggle 2: Disable
        $response = \OmegaUp\Controllers\Course::apiToggleTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
            ])
        );
        $this->assertFalse($response['teaching_assistant_enabled']);

        // Toggle 3: Enable again
        $response = \OmegaUp\Controllers\Course::apiToggleTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
            ])
        );
        $this->assertTrue($response['teaching_assistant_enabled']);

        $course = \OmegaUp\DAO\Courses::getByAlias($courseData['course_alias']);
        $this->assertTrue($course->teaching_assistant_enabled);
    }

    /**
     * Test that the field persists correctly after course updates
     */
    public function testTeachingAssistantFieldPersistsAfterCourseUpdate() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();
        $adminLogin = self::login($courseData['admin']);

        \OmegaUp\Controllers\Course::apiToggleTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        \OmegaUp\Controllers\Course::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'alias' => $courseData['course_alias'],
            'description' => 'Updated course description',
        ]));

        $course = \OmegaUp\DAO\Courses::getByAlias($courseData['course_alias']);
        $this->assertTrue($course->teaching_assistant_enabled);

        $courseDetails = \OmegaUp\Controllers\Course::apiDetails(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'alias' => $courseData['course_alias'],
        ]));
        $this->assertTrue($courseDetails['teaching_assistant_enabled']);
    }
}
