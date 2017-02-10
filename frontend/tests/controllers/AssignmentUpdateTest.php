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

        $r = new Request([
            'auth_token' => $login->auth_token,
            'alias' => $assignmentAlias,
            'start_time' => strtotime('2017-01-02 12:34:56'),
            'finish_time' => strtotime('2017-03-04 12:34:56'),
            'name' => 'some new name',
            'description' => 'some meaningful description'
        ]);

        $response = CourseController::apiUpdateAssignment($r);

        // Read the assignment again
        $r = new Request([
                    'auth_token' => $login->auth_token,
                    'alias' => $assignmentAlias
                ]);

        $response = CourseController::apiAssignmentDetails($r);

        $this->assertEquals(strtotime('2017-01-02 12:34:56'), $response['start_time']);
        $this->assertEquals(strtotime('2017-03-04 12:34:56'), $response['finish_time']);

        $this->assertEquals('some new name', $response['name']);
        $this->assertEquals('some meaningful description', $response['description']);
    }
}
