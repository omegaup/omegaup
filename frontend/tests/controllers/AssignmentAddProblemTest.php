<?php

class AssignmentAddProblemTest extends OmegaupTestCase {
    public function testAddProbemToAssignment() {
        $user = UserFactory::createUser();

        $courseData = CoursesFactory::createCourseWithOneAssignment($user);
        $assignmentAlias = $courseData['assignment_alias'];

        $probData = ProblemsFactory::createProblem(null, null, 1, $user);

        $r = new Request(array(
            'assignment_alias' => $assignmentAlias,
            'problem_alias' => $probData['problem']->alias,
            'auth_token' => self::login($user)
        ));

        $response = CourseController::apiAddProblem($r);
        $this->assertEquals('ok', $response['status']);
    }
}
