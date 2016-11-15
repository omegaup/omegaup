<?php

/**
 *
 * @author alan
 */

class CourseDetailsTest extends OmegaupTestCase {
    public function testGetCourseDetailsValid() {
        // Create 1 course with 1 assignment
        $courseData = CoursesFactory::createCourseWithOneAssignment();

        // Call the details API
        $adminLogin = self::login($courseData['admin']);
        $response = CourseController::apiDetails(new Request(array(
            'auth_token' => $adminLogin->auth_token,
            'alias' => $courseData['course_alias']
        )));

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals($courseData['course_alias'], $response['alias']);
        Validators::isNumber($response['start_time'], 'start_time', true);
        Validators::isNumber($response['finish_time'], 'finish_time', true);

        // 1 assignment
        $this->assertEquals(1, count($response['assignments']));
        $this->assertEquals(true, $response['is_admin']);

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

    /**
     * Get details with user not registerd to the Course. Should fail.
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

        $response = CourseController::apiDetails(new Request(array(
            'auth_token' => $userLogin->auth_token,
            'alias' => $courseData['course_alias']
        )));

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(false, $response['is_admin']);
    }
}
