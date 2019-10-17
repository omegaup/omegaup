<?php

/**
 * @author alanboy
 */
class AssignmentUpdateTest extends OmegaupTestCase {
    public function testAssignmentUpdate() {
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        $login = self::login($identity);

        $courseData = CoursesFactory::createCourseWithOneAssignment(
            $identity,
            $login
        );
        $assignmentAlias = $courseData['assignment_alias'];
        $courseAlias = $courseData['course_alias'];

        $updatedStartTime = $courseData['request']['start_time'] + 10;
        $updatedFinishTime = $courseData['request']['start_time'] + 20;

        \OmegaUp\Controllers\Course::apiUpdateAssignment(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'assignment' => $assignmentAlias,
            'course' => $courseAlias,
            'start_time' => $updatedStartTime,
            'finish_time' => $updatedFinishTime,
            'name' => 'some new name',
            'description' => 'some meaningful description'
        ]));

        // Read the assignment again
        $response = \OmegaUp\Controllers\Course::apiAssignmentDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'assignment' => $assignmentAlias,
            'course' => $courseAlias
        ]));

        $this->assertEquals($updatedStartTime, $response['start_time']);
        $this->assertEquals($updatedFinishTime, $response['finish_time']);

        $this->assertEquals('some new name', $response['name']);
        $this->assertEquals(
            'some meaningful description',
            $response['description']
        );
    }

    /**
     * When updating an assignment you need to supply both assignment
     * alias and course alias
     */
    public function testMissingDataOnAssignmentUpdate() {
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        $login = self::login($identity);

        $courseData = CoursesFactory::createCourseWithOneAssignment(
            $identity,
            $login
        );

        try {
            \OmegaUp\Controllers\Course::apiUpdateAssignment(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'assignment' => $courseData['assignment_alias'],
                'course' => $courseData['course_alias'],
                'name' => 'some new name'
            ]));
            $this->fail(
                'Updating assignment should have failed due to missing parameter'
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals('parameterEmpty', $e->getMessage());
        }
    }

    /**
     * Can't update the start time to be after the finish time.
     */
    public function testAssignmentUpdateWithInvertedTimes() {
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();
        $login = self::login($identity);

        $courseData = CoursesFactory::createCourseWithOneAssignment(
            $identity,
            $login
        );

        try {
            \OmegaUp\Controllers\Course::apiUpdateAssignment(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'assignment' => $courseData['assignment_alias'],
                'course' => $courseData['course_alias'],
                'start_time' => $courseData['request']['start_time'] + 10,
                'finish_time' => $courseData['request']['start_time'] + 9,
            ]));

            $this->fail(
                'Assignment should not have been updated because finish time is earlier than start time'
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals('courseInvalidStartTime', $e->getMessage());
        }
    }

    /**
     * Students should not be able to update the assignment.
     */
    public function testAssignmentUpdateByStudent() {
        ['user' => $admin, 'identity' => $adminIdentity] = UserFactory::createUser();
        $adminLogin = OmegaupTestCase::login($adminIdentity);
        $courseData = CoursesFactory::createCourseWithOneAssignment(
            $adminIdentity,
            $adminLogin
        );

        ['user' => $student, 'identity' => $identity] = UserFactory::createUser();
        $response = \OmegaUp\Controllers\Course::apiAddStudent(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $identity->username,
            'course_alias' => $courseData['course_alias'],
        ]));

        $login = OmegaupTestCase::login($identity);
        try {
            \OmegaUp\Controllers\Course::apiUpdateAssignment(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'assignment' => $courseData['assignment_alias'],
                'course' => $courseData['course_alias'],
                'start_time' => $courseData['request']['start_time'],
                'finish_time' => $courseData['request']['finish_time'],
                'description' => 'pwnd',
            ]));
            $this->fail('Expected ForbiddenAccessException');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    public function testAssignmentsOutOfDate() {
        // Create 1 course with 1 assignment
        $courseData = CoursesFactory::createCourseWithOneAssignment();

        $adminLogin = self::login($courseData['admin']);
        $response = \OmegaUp\Controllers\Course::apiListAssignments(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias']
        ]));

        // Updating start_time of assignment out of the date
        try {
            \OmegaUp\Controllers\Course::apiUpdateAssignment(new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course' => $courseData['course_alias'],
                'name' => $response['assignments'][0]['name'],
                'assignment' => $response['assignments'][0]['alias'],
                'description' => $response['assignments'][0]['description'],
                'start_time' => $response['assignments'][0]['start_time'] + 240,
                'finish_time' => $response['assignments'][0]['finish_time'] + 240,
                'assignment_type' => $response['assignments'][0]['assignment_type'],
            ]));
            $this->fail(
                'Assignment should not have been updated because the date falls outside of valid range'
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals('parameterNumberTooLarge', $e->getMessage());
        }
    }
}
