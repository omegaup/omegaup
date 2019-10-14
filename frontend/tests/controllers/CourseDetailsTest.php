<?php

/**
 *
 * @author alan
 */

class CourseDetailsTest extends OmegaupTestCase {
    public function testGetCourseDetailsValid() {
        $courseData = CoursesFactory::createCourseWithOneAssignment();

        // Add assignment that's already underway.
        $adminLogin = self::login($courseData['admin']);
        \OmegaUp\Controllers\Course::apiCreateAssignment(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => Utils::CreateRandomString(),
            'description' => Utils::CreateRandomString(),
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

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals($courseData['course_alias'], $response['alias']);
        \OmegaUp\Validators::validateNumber(
            $response['start_time'],
            'start_time'
        );
        \OmegaUp\Validators::validateNumber(
            $response['finish_time'],
            'finish_time'
        );

        // Both assignments added should be visible since the caller is an
        // admin.
        $this->assertEquals(true, $response['is_admin']);
        $this->assertEquals(2, count($response['assignments']));

        foreach ($response['assignments'] as $assignment) {
            $this->assertNotNull($assignment['name']);
            $this->assertNotNull($assignment['description']);
            $this->assertNotNull($assignment['alias']);
            $this->assertNotNull($assignment['assignment_type']);
            $this->assertNotNull($assignment['start_time']);
            $this->assertNotNull($assignment['finish_time']);

            \OmegaUp\Validators::validateNumber(
                $assignment['start_time'],
                'start_time'
            );
            \OmegaUp\Validators::validateNumber(
                $assignment['finish_time'],
                'finish_time'
            );
        }
    }

    public function testGetCourseDetailsAsStudentValid() {
        $courseData = CoursesFactory::createCourseWithOneAssignment();

        // Add assignment that's already underway.
        $adminLogin = self::login($courseData['admin']);
        $assignmentAlias = Utils::CreateRandomString();
        \OmegaUp\Controllers\Course::apiCreateAssignment(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => $assignmentAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120),
            'course_alias' => $courseData['course_alias'],
            'assignment_type' => 'homework',
        ]));

        $user = CoursesFactory::addStudentToCourse($courseData);
        $userLogin = self::login($user);

        // Call the details API
        $response = \OmegaUp\Controllers\Course::apiDetails(new \OmegaUp\Request([
            'auth_token' => $userLogin->auth_token,
            'alias' => $courseData['course_alias']
        ]));

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals($courseData['course_alias'], $response['alias']);
        \OmegaUp\Validators::validateNumber(
            $response['start_time'],
            'start_time'
        );
        \OmegaUp\Validators::validateNumber(
            $response['finish_time'],
            'finish_time'
        );

        // Only the course that has started should be visible.
        $this->assertEquals(false, $response['is_admin']);
        $this->assertEquals(1, count($response['assignments']));
        $this->assertEquals(
            $courseData['assignment_alias'],
            $response['assignments'][0]['alias']
        );
    }

    /**
     * Get details with user not registered to the Course. Should fail.
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testGetCourseDetailsNoCourseMember() {
        $courseData = CoursesFactory::createCourseWithOneAssignment();
        $user = UserFactory::createUser();
        $userLogin = self::login($user);

        $response = \OmegaUp\Controllers\Course::apiDetails(new \OmegaUp\Request([
            'auth_token' => $userLogin->auth_token,
            'alias' => $courseData['course_alias']
        ]));
    }

    /**
     * Get details with user not registered to the Course. Should fail even if course is Public.
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testGetCourseDetailsNoCourseMemberPublic() {
        $courseData = CoursesFactory::createCourse(null, null, true);
        $user = UserFactory::createUser();

        $userLogin = self::login($user);
        $response = \OmegaUp\Controllers\Course::apiDetails(new \OmegaUp\Request([
            'auth_token' => $userLogin->auth_token,
            'alias' => $courseData['course_alias']
        ]));
    }

    public function testGetCourseIntroDetailsNoCourseMemberPublic() {
        $courseData = CoursesFactory::createCourse(null, null, true);
        $user = UserFactory::createUser();

        $userLogin = self::login($user);
        $response = \OmegaUp\Controllers\Course::apiIntroDetails(new \OmegaUp\Request([
            'auth_token' => $userLogin->auth_token,
            'course_alias' => $courseData['course_alias']
        ]));

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals($courseData['request']['name'], $response['name']);
        $this->assertArrayNotHasKey('assignments', $response);
    }

    public function testGetCourseDetailsCourseMember() {
        $courseData = CoursesFactory::createCourseWithOneAssignment();
        $user = CoursesFactory::addStudentToCourse($courseData);
        $userLogin = self::login($user);

        $response = \OmegaUp\Controllers\Course::apiDetails(new \OmegaUp\Request([
            'auth_token' => $userLogin->auth_token,
            'alias' => $courseData['course_alias']
        ]));

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(false, $response['is_admin']);
    }

    public function testGetAssignmentAsStudent() {
        $courseData = CoursesFactory::createCourseWithOneAssignment();

        // Add assignment that hasn't started yet.
        $adminLogin = self::login($courseData['admin']);
        $assignmentAlias = Utils::CreateRandomString();
        \OmegaUp\Controllers\Course::apiCreateAssignment(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => $assignmentAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120),
            'course_alias' => $courseData['course_alias'],
            'assignment_type' => 'homework',
        ]));

        $user = UserFactory::createUser();
        $userLogin = self::login($user);

        // Try to get details before being added to the course;
        try {
            $response = \OmegaUp\Controllers\Course::apiAssignmentDetails(new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
                'course' => $courseData['course_alias'],
                'assignment' => $courseData['assignment_alias'],
            ]));
            $this->fail('Exception was expected.');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            // OK!
        }

        CoursesFactory::addStudentToCourse($courseData, $user);

        // Call the details API for the assignment that's already started.
        $response = \OmegaUp\Controllers\Course::apiAssignmentDetails(new \OmegaUp\Request([
            'auth_token' => $userLogin->auth_token,
            'course' => $courseData['course_alias'],
            'assignment' => $courseData['assignment_alias'],
        ]));
        $this->assertEquals('ok', $response['status']);

        // Call the detail API for the assignment that has not started.
        try {
            $response = \OmegaUp\Controllers\Course::apiAssignmentDetails(new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
                'course' => $courseData['course_alias'],
                'assignment' => $assignmentAlias,
            ]));
            $this->fail('Exception was expected.');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            // OK!
        }
    }
}
