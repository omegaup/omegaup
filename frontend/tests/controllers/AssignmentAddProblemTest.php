<?php

class AssignmentAddProblemTest extends OmegaupTestCase {
    public function testAddProblemToAssignment() {
        $user = UserFactory::createUser();
        $login = self::login($user);

        $courseData = CoursesFactory::createCourseWithOneAssignment($user, $login);
        $assignmentAlias = $courseData['assignment_alias'];

        $probData = ProblemsFactory::createProblem(null, null, 1, $user, null, $login);

        $r = new Request([
            'auth_token' => $login->auth_token,
            'course_alias' => $courseData['course_alias'],
            'assignment_alias' => $assignmentAlias,
            'problem_alias' => $probData['problem']->alias,
        ]);

        $response = CourseController::apiAddProblem($r);
        $this->assertEquals('ok', $response['status']);
    }
}
