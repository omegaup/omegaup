<?php

class CourseProblemsTest extends OmegaupTestCase {
    public function testOrderProblems() {
        // Create a test course
        $user = UserFactory::createUser();

        $login = self::login($user);

        // Create a course with an assignment
        $courseData = CoursesFactory::createCourseWithOneAssignment($user, $login);
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add 3 problems to the assignment.
        $numberOfProblems = 3;
        for ($i=0; $i < $numberOfProblems; $i++) {
            $problemData[$i] = ProblemsFactory::createProblem(null, null, 1, $user, null, $login);
        }
        CoursesFactory::addProblemsToAssignment($login, $courseAlias, $assignmentAlias, $problemData);

        $problems = CourseController::apiAssignmentDetails(new Request([
            'auth_token' => $login->auth_token,
            'assignment' => $assignmentAlias,
            'course' => $courseAlias
        ]));

        $problems['problems'][0]['order'] = 1;
        $problems['problems'][1]['order'] = 2;
        $problems['problems'][2]['order'] = 3;

        CourseController::apiUpdateProblemsOrder(new Request([
            'auth_token' => $login->auth_token,
            'course_alias' => $courseAlias,
            'assignment_alias' => $assignmentAlias,
            'problems' => $problems['problems'],
        ]));

        $problems = CourseController::apiAssignmentDetails(new Request([
            'auth_token' => $login->auth_token,
            'assignment' => $assignmentAlias,
            'course' => $courseAlias
        ]));

        // Before reordering problems
        for ($i=0; $i < $numberOfProblems; $i++) {
            $originalOrder[$i] = [
                'alias' => $problems['problems'][$i]['alias'],
                'order' => $problems['problems'][$i]['order']
            ];
            $this->assertEquals($problems['problems'][$i]['order'], ($i+1));
        }

        $problems['problems'][0]['order'] = 2;
        $problems['problems'][1]['order'] = 3;
        $problems['problems'][2]['order'] = 1;

        CourseController::apiUpdateProblemsOrder(new Request([
            'auth_token' => $login->auth_token,
            'course_alias' => $courseAlias,
            'assignment_alias' => $assignmentAlias,
            'problems' => $problems['problems'],
        ]));

        $problems = CourseController::apiAssignmentDetails(new Request([
            'auth_token' => $login->auth_token,
            'assignment' => $assignmentAlias,
            'course' => $courseAlias
        ]));

        // After disordering problems
        for ($i=0; $i < $numberOfProblems; $i++) {
            $this->assertNotEquals($problems['problems'][$i]['alias'], $originalOrder[$i]['alias']);
        }
    }
}
