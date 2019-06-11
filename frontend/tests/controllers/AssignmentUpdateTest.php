<?php

/**
 * @author alanboy
 */
class AssignmentUpdateTest extends OmegaupTestCase {
    public function testAssignmentUpdate() {
        $user = UserFactory::createUser();
        $login = self::login($user);

        $courseData = CoursesFactory::createCourseWithOneAssignment($user, $login);
        $assignmentAlias = $courseData['assignment_alias'];
        $courseAlias = $courseData['course_alias'];

        $updatedStartTime = $courseData['request']['start_time'] + 10;
        $updatedFinishTime = $courseData['request']['start_time'] + 20;

        CourseController::apiUpdateAssignment(new Request([
            'auth_token' => $login->auth_token,
            'assignment' => $assignmentAlias,
            'course' => $courseAlias,
            'start_time' => $updatedStartTime,
            'finish_time' => $updatedFinishTime,
            'name' => 'some new name',
            'description' => 'some meaningful description'
        ]));

        // Read the assignment again
        $response = CourseController::apiAssignmentDetails(new Request([
            'auth_token' => $login->auth_token,
            'assignment' => $assignmentAlias,
            'course' => $courseAlias
        ]));

        $this->assertEquals($updatedStartTime, $response['start_time']);
        $this->assertEquals($updatedFinishTime, $response['finish_time']);

        $this->assertEquals('some new name', $response['name']);
        $this->assertEquals('some meaningful description', $response['description']);
    }

    /**
     * When updating an assignment you need to supply both assignment
     * alias and course alias
     *
     * @expectedException InvalidParameterException
     */
    public function testMissingDataOnAssignmentUpdate() {
        $user = UserFactory::createUser();
        $login = self::login($user);

        $courseData = CoursesFactory::createCourseWithOneAssignment($user, $login);

        // Call the update API with no
        $response = CourseController::apiUpdateAssignment(new Request([
            'auth_token' => $login->auth_token,
            'assignment' => $courseData['assignment_alias'],
            'course' => $courseData['course_alias'],
            'name' => 'some new name'
        ]));
    }

    /**
     * Can't update the start time to be after the finish time.
     */
    public function testAssignmentUpdateWithInvertedTimes() {
        $user = UserFactory::createUser();
        $login = self::login($user);

        $courseData = CoursesFactory::createCourseWithOneAssignment($user, $login);

        try {
            CourseController::apiUpdateAssignment(new Request([
                'auth_token' => $login->auth_token,
                'assignment' => $courseData['assignment_alias'],
                'course' => $courseData['course_alias'],
                'start_time' => $courseData['request']['start_time'] + 10,
                'finish_time' => $courseData['request']['start_time'] + 9,
            ]));

            $this->fail('Assignment should not have been updated because finish time is earlier than start time');
        } catch (InvalidParameterException $e) {
            $this->assertEquals($e->getMessage(), 'courseInvalidStartTime');
        }
    }

    /**
     * Students should not be able to update the assignment.
     */
    public function testAssignmentUpdateByStudent() {
        $admin = UserFactory::createUser();
        $adminLogin = OmegaupTestCase::login($admin);
        $courseData = CoursesFactory::createCourseWithOneAssignment(
            $admin,
            $adminLogin
        );

        $student = UserFactory::createUser();
        $response = CourseController::apiAddStudent(new Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $student->username,
            'course_alias' => $courseData['course_alias'],
        ]));

        $login = OmegaupTestCase::login($student);
        try {
            CourseController::apiUpdateAssignment(new Request([
                'auth_token' => $login->auth_token,
                'assignment' => $courseData['assignment_alias'],
                'course' => $courseData['course_alias'],
                'start_time' => $courseData['request']['start_time'],
                'finish_time' => $courseData['request']['finish_time'],
                'description' => 'pwnd',
            ]));
            $this->fail('Expected ForbiddenAccessException');
        } catch (ForbiddenAccessException $e) {
            // OK.
        }
    }

    /**
     * @expectedException InvalidParameterException
     */
    public function testAssignmentsOutOfDate() {
        // Create 1 course with 1 assignment
        $courseData = CoursesFactory::createCourseWithOneAssignment();

        $adminLogin = self::login($courseData['admin']);
        $response = CourseController::apiListAssignments(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias']
        ]));

        // Updating start_time of assignment out of the date
        CourseController::apiUpdateAssignment(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course' => $courseData['course_alias'],
            'name' => $response['assignments'][0]['name'],
            'assignment' => $response['assignments'][0]['alias'],
            'description' => $response['assignments'][0]['description'],
            'start_time' => $response['assignments'][0]['start_time'] + 240,
            'finish_time' => $response['assignments'][0]['finish_time'] + 240,
            'assignment_type' => $response['assignments'][0]['assignment_type'],
        ]));

        $this->expectException(InvalidArgumentException::class);
    }
}
