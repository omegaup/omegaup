<?php

/**
 * Description of CourseUsersTest
 *
 * @author juan.pablo
 */

class CourseUsersTest extends OmegaupTestCase {
    public function testCourseActivityReport() {
        // Create a course with 5 assignments
        $courseData = CoursesFactory::createCourseWithAssignments(5);

        $user = UserFactory::createUser();

        CoursesFactory::addStudentToCourse($courseData, $user);

        $userLogin = self::login($user);

        // Call the details API for the assignment that's already started.
        CourseController::apiAssignmentDetails(new Request([
            'auth_token' => $userLogin->auth_token,
            'course' => $courseData['course_alias'],
            'assignment' => $courseData['assignment_aliases'][0],
        ]));

        // Call API
        $adminLogin = self::login($courseData['admin']);
        $response = CourseController::apiActivityReport(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
        ]));

        // Check that we have entries in the log.
        $this->assertEquals(1, count($response['events']));
        $this->assertEquals($user->username, $response['events'][0]['username']);
        $this->assertEquals(0, $response['events'][0]['ip']);
        $this->assertEquals('open', $response['events'][0]['event']['name']);
    }
}
