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
        CourseController::apiCreateAssignment(new Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => Utils::CreateRandomString(),
            'description' => Utils::CreateRandomString(),
            'start_time' => (Utils::GetPhpUnixTimestamp() - 60),
            'finish_time' => (Utils::GetPhpUnixTimestamp() + 120),
            'course_alias' => $courseData['course_alias'],
            'assignment_type' => 'homework',
        ]));

        // Call the details API
        $response = CourseController::apiDetails(new Request([
            'auth_token' => $adminLogin->auth_token,
            'alias' => $courseData['course_alias']
        ]));

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals($courseData['course_alias'], $response['alias']);
        Validators::isNumber($response['start_time'], 'start_time', true);
        Validators::isNumber($response['finish_time'], 'finish_time', true);

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

            Validators::isNumber($assignment['start_time'], 'start_time', true);
            Validators::isNumber($assignment['finish_time'], 'finish_time', true);
        }
    }

    public function testGetCourseDetailsAsStudentValid() {
        $courseData = CoursesFactory::createCourseWithOneAssignment();

        // Add assignment that's already underway.
        $adminLogin = self::login($courseData['admin']);
        $assignmentAlias = Utils::CreateRandomString();
        CourseController::apiCreateAssignment(new Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => $assignmentAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => (Utils::GetPhpUnixTimestamp() - 60),
            'finish_time' => (Utils::GetPhpUnixTimestamp() + 120),
            'course_alias' => $courseData['course_alias'],
            'assignment_type' => 'homework',
        ]));

        $user = CoursesFactory::addStudentToCourse($courseData);
        $userLogin = self::login($user);

        // Call the details API
        $response = CourseController::apiDetails(new Request([
            'auth_token' => $userLogin->auth_token,
            'alias' => $courseData['course_alias']
        ]));

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals($courseData['course_alias'], $response['alias']);
        Validators::isNumber($response['start_time'], 'start_time', true);
        Validators::isNumber($response['finish_time'], 'finish_time', true);

        // Only the course that has started should be visible.
        $this->assertEquals(false, $response['is_admin']);
        $this->assertEquals(1, count($response['assignments']));
        $this->assertEquals(
            $assignmentAlias,
            $response['assignments'][0]['alias']
        );
    }

    /**
     * Get details with user not registered to the Course. Should fail.
     * @expectedException ForbiddenAccessException
     */
    public function testGetCourseDetailsNormalUser() {
        $courseData = CoursesFactory::createCourseWithOneAssignment();
        $user = UserFactory::createUser();
        $userLogin = self::login($user);

        $response = CourseController::apiDetails(new Request(array(
            'auth_token' => $userLogin->auth_token,
            'alias' => $courseData['course_alias']
        )));
    }

    public function testGetCourseDetailsCourseMember() {
        $courseData = CoursesFactory::createCourseWithOneAssignment();
        $user = CoursesFactory::addStudentToCourse($courseData);
        $userLogin = self::login($user);

        $response = CourseController::apiDetails(new Request([
            'auth_token' => $userLogin->auth_token,
            'alias' => $courseData['course_alias']
        ]));

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(false, $response['is_admin']);
    }

    public function testGetAssignmentAsStudent() {
        $courseData = CoursesFactory::createCourseWithOneAssignment();

        // Add assignment that's already underway.
        $adminLogin = self::login($courseData['admin']);
        $assignmentAlias = Utils::CreateRandomString();
        CourseController::apiCreateAssignment(new Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => $assignmentAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => (Utils::GetPhpUnixTimestamp() - 60),
            'finish_time' => (Utils::GetPhpUnixTimestamp() + 120),
            'course_alias' => $courseData['course_alias'],
            'assignment_type' => 'homework',
        ]));

        $user = CoursesFactory::addStudentToCourse($courseData);
        $userLogin = self::login($user);

        // Call the details API for the assignment that's already started.
        $response = CourseController::apiAssignmentDetails(new Request([
            'auth_token' => $userLogin->auth_token,
            'course' => $courseData['course_alias'],
            'assignment' => $assignmentAlias,
        ]));
        $this->assertEquals('ok', $response['status']);

        // Call the detail API for the assignment that has not started.
        try {
            $response = CourseController::apiAssignmentDetails(new Request([
            'auth_token' => $userLogin->auth_token,
            'course' => $courseData['course_alias'],
            'assignment' => $courseData['assignment_alias'],
            ]));
            $this->fail('Exception was expected.');
        } catch (ForbiddenAccessException $e) {
            // OK!
        }
    }
}
