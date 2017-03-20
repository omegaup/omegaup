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

        $updatedStartTime = strtotime('2017-01-02 12:34:56');
        $updatedFinishTime = strtotime('2017-03-04 12:34:56');

        $response = CourseController::apiUpdateAssignment(new Request([
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
            'name' => 'some new name'
        ]));
    }

    /**
     * Can't update the start time to be after the finish time.
     * @expectedException InvalidParameterException
     */
    public function testAssignmentUpdateWithInvertedTimes() {
        $user = UserFactory::createUser();
        $login = self::login($user);

        $courseData = CoursesFactory::createCourseWithOneAssignment($user, $login);
        $response = CourseController::apiUpdateAssignment(new Request([
            'auth_token' => $login->auth_token,
            'assignment' => $courseData['assignment_alias'],
            'course' => $courseData['course_alias'],
            'start_time' => strtotime('2017-03-04 12:34:56'),
            'finish_time' => strtotime('2017-01-02 12:34:56'),
        ]));
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
                'description' => 'pwnd',
            ]));
            $this->fail('Expected ForbiddenAccessException');
        } catch (ForbiddenAccessException $e) {
            // OK.
        }
    }
}
