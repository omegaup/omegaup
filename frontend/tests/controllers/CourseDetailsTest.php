<?php

class CourseDetailsTest extends \OmegaUp\Test\ControllerTestCase {
    public function testGetCourseDetailsValid() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();

        // Add assignment that's already underway.
        $adminLogin = self::login($courseData['admin']);
        \OmegaUp\Controllers\Course::apiCreateAssignment(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'alias' => \OmegaUp\Test\Utils::createRandomString(),
            'description' => \OmegaUp\Test\Utils::createRandomString(),
            'start_time' => (\OmegaUp\Time::get()),
            'finish_time' => (\OmegaUp\Time::get() + 120),
            'course_alias' => $courseData['course_alias'],
            'assignment_type' => 'homework',
        ]));

        // Call the details API
        $response = \OmegaUp\Controllers\Course::apiDetails(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'alias' => $courseData['course_alias']
        ]));

        $this->assertSame($courseData['course_alias'], $response['alias']);
        \OmegaUp\Validators::validateNumber(
            $response['start_time']->time,
            'start_time'
        );
        \OmegaUp\Validators::validateNumber(
            $response['finish_time']->time,
            'finish_time'
        );

        // Both assignments added should be visible since the caller is an
        // admin.
        $this->assertSame(true, $response['is_admin']);
        $this->assertSame(2, count($response['assignments']));

        foreach ($response['assignments'] as $assignment) {
            $this->assertNotNull($assignment['name']);
            $this->assertNotNull($assignment['description']);
            $this->assertNotNull($assignment['alias']);
            $this->assertNotNull($assignment['assignment_type']);
            $this->assertNotNull($assignment['start_time']);
            $this->assertNotNull($assignment['finish_time']);

            \OmegaUp\Validators::validateNumber(
                $assignment['start_time']->time,
                'start_time'
            );
            \OmegaUp\Validators::validateNumber(
                $assignment['finish_time']->time,
                'finish_time'
            );
        }
    }

    public function testGetCourseDetailsAsStudentValid() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();

        // Add assignment that's already underway.
        $adminLogin = self::login($courseData['admin']);
        $assignmentAlias = \OmegaUp\Test\Utils::createRandomString();
        \OmegaUp\Controllers\Course::apiCreateAssignment(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'alias' => $assignmentAlias,
            'description' => \OmegaUp\Test\Utils::createRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120),
            'course_alias' => $courseData['course_alias'],
            'assignment_type' => 'homework',
        ]));

        $user = \OmegaUp\Test\Factories\Course::addStudentToCourse($courseData);
        $userLogin = self::login($user);

        // Call the details API
        $response = \OmegaUp\Controllers\Course::apiDetails(new \OmegaUp\Request([
            'auth_token' => $userLogin->auth_token,
            'alias' => $courseData['course_alias']
        ]));

        $this->assertSame($courseData['course_alias'], $response['alias']);
        \OmegaUp\Validators::validateNumber(
            $response['start_time']->time,
            'start_time'
        );
        \OmegaUp\Validators::validateNumber(
            $response['finish_time']->time,
            'finish_time'
        );

        // Only the course that has started should be visible.
        $this->assertSame(false, $response['is_admin']);
        $this->assertSame(1, count($response['assignments']));
        $this->assertSame(
            $courseData['assignment_alias'],
            $response['assignments'][0]['alias']
        );
    }

    public function testGetCourseDetailsForTeachingAssistant() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();

        $adminLogin = self::login($courseData['admin']);

        ['identity' => $identityTA] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Controllers\Course::apiAddTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'usernameOrEmail' => $identityTA->username,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        $taLogin = self::login($identityTA);

        // Call the details API
        $response = \OmegaUp\Controllers\Course::getCourseEditDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $taLogin->auth_token,
                'course' => $courseData['course_alias']
            ])
        )['templateProperties']['payload'];

        $this->assertTrue($response['course']['is_teaching_assistant']);
    }

    public function testGetCourseDetailsForTeachingAssistantAddedByGroup() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();

        $adminLogin = self::login($courseData['admin']);

        ['identity' => $identityTA] = \OmegaUp\Test\Factories\User::createUser();

        $groupDataTA = \OmegaUp\Test\Factories\Groups::createGroup();

        // add user to the groups
        \OmegaUp\Test\Factories\Groups::addUserToGroup(
            $groupDataTA,
            $identityTA
        );

        \OmegaUp\Controllers\Course::apiAddGroupTeachingAssistant(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'group' => $groupDataTA['request']['alias'],
            'course_alias' => $courseData['course_alias'],
        ]));

        $taLogin = self::login($identityTA);

        // Call the details API
        $response = \OmegaUp\Controllers\Course::getCourseEditDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $taLogin->auth_token,
                'course' => $courseData['course_alias']
            ])
        )['templateProperties']['payload'];

        $this->assertTrue($response['course']['is_teaching_assistant']);
    }

    /**
     * Get details with user not registered to the Course. Should fail.
     */
    public function testGetCourseDetailsNoCourseMember() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $userLogin = self::login($identity);

        try {
            \OmegaUp\Controllers\Course::apiDetails(new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
                'alias' => $courseData['course_alias']
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Get details with user not registered to the Course. Should fail even if course is Public.
     */
    public function testGetCourseDetailsNoCourseMemberPublic() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourse(
            null,
            null,
            \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC
        );
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $userLogin = self::login($identity);
        try {
            \OmegaUp\Controllers\Course::apiDetails(new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
                'alias' => $courseData['course_alias']
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    public function testGetCourseIntroDetailsNoCourseMemberPublic() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourse(
            admin: null,
            adminLogin: null,
            admissionMode: \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC
        );
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $userLogin = self::login($identity);
        $response = \OmegaUp\Controllers\Course::apiIntroDetails(new \OmegaUp\Request([
            'auth_token' => $userLogin->auth_token,
            'course_alias' => $courseData['course_alias']
        ]));

        $this->assertSame(
            $courseData['request']['name'],
            $response['course']['name']
        );
        $this->assertArrayNotHasKey('assignments', $response);
    }

    public function testGetCourseDetailsCourseMember() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();
        $user = \OmegaUp\Test\Factories\Course::addStudentToCourse($courseData);
        $userLogin = self::login($user);

        $response = \OmegaUp\Controllers\Course::apiDetails(new \OmegaUp\Request([
            'auth_token' => $userLogin->auth_token,
            'alias' => $courseData['course_alias']
        ]));

        $this->assertSame(false, $response['is_admin']);
    }

    public function testGetAssignmentAsStudent() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();

        // Add assignment that hasn't started yet.
        $adminLogin = self::login($courseData['admin']);
        $assignmentAlias = \OmegaUp\Test\Utils::createRandomString();
        \OmegaUp\Controllers\Course::apiCreateAssignment(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'alias' => $assignmentAlias,
            'description' => \OmegaUp\Test\Utils::createRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120),
            'course_alias' => $courseData['course_alias'],
            'assignment_type' => 'homework',
        ]));

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $userLogin = self::login($identity);

        // Try to get details before being added to the course;
        try {
            \OmegaUp\Controllers\Course::apiAssignmentDetails(new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
                'course' => $courseData['course_alias'],
                'assignment' => $courseData['assignment_alias'],
            ]));
            $this->fail('Exception was expected.');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }

        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $identity
        );

        // Call the details API for the assignment that's already started.
        \OmegaUp\Controllers\Course::apiAssignmentDetails(new \OmegaUp\Request([
            'auth_token' => $userLogin->auth_token,
            'course' => $courseData['course_alias'],
            'assignment' => $courseData['assignment_alias'],
        ]));

        // Call the detail API for the assignment that has not started.
        try {
            \OmegaUp\Controllers\Course::apiAssignmentDetails(new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
                'course' => $courseData['course_alias'],
                'assignment' => $assignmentAlias,
            ]));
            $this->fail('Exception was expected.');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Tests the API to archive or desarchive a course
     */
    public function testArchiveCourse() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();
        $adminLogin = self::login($courseData['admin']);

        $course = \OmegaUp\DAO\Courses::getByPK(
            $courseData['course']->course_id
        );
        $this->assertFalse($course->archived);

        \OmegaUp\Controllers\Course::apiArchive(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $course->alias,
            'archive' => true
        ]));
        $course = \OmegaUp\DAO\Courses::getByPK(
            $courseData['course']->course_id
        );
        $this->assertTrue($course->archived);

        \OmegaUp\Controllers\Course::apiArchive(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $course->alias,
            'archive' => false
        ]));
        $course = \OmegaUp\DAO\Courses::getByPK(
            $courseData['course']->course_id
        );
        $this->assertFalse($course->archived);
    }
}
