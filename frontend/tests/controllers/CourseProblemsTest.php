<?php

class CourseProblemsTest extends OmegaupTestCase {
    public function testOrderProblems() {
        // Create a test course
        $user = UserFactory::createUser();

        $courseAlias = Utils::CreateRandomString();

        $login = self::login($user);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => $courseAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => (Utils::GetPhpUnixTimestamp() + 60),
            'finish_time' => (Utils::GetPhpUnixTimestamp() + 120)
        ]);

        // Call api
        $course = CourseController::apiCreate($r);

        // Create a test course
        $login = self::login($user);
        $assignment_alias = Utils::CreateRandomString();
        $course = CourseController::apiCreateAssignment(new Request([
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => $assignment_alias,
            'description' => Utils::CreateRandomString(),
            'start_time' => (Utils::GetPhpUnixTimestamp() + 60),
            'finish_time' => (Utils::GetPhpUnixTimestamp() + 120),
            'course_alias' => $courseAlias,
            'assignment_type' => 'homework'
        ]));

        $assignments = AssignmentsDAO::search([
            'alias' => $assignment_alias,
        ]);
        $assignment = $assignments[0];

        // Add 3 problems to the assignment.
        $numberOfProblems = 3;
        for ($i=0; $i < $numberOfProblems; $i++) {
            $problemData[$i] = ProblemsFactory::createProblem(null, null, 1, $user, null, $login);

            CourseController::apiAddProblem(new Request([
                'auth_token' => $login->auth_token,
                'course_alias' => $courseAlias,
                'assignment_alias' => $assignment->alias,
                'problem_alias' => $problemData[$i]['problem']->alias,
            ]));
        }

        $problems = CourseController::apiAssignmentDetails(new Request([
            'auth_token' => $login->auth_token,
            'assignment' => $assignment->alias,
            'course' => $courseAlias
        ]));

        $problems['problems'][0]['order'] = 1;
        $problems['problems'][1]['order'] = 2;
        $problems['problems'][2]['order'] = 3;

        CourseController::apiUpdateProblemsOrder(new Request([
            'auth_token' => $login->auth_token,
            'course_alias' => $courseAlias,
            'assignment_alias' => $assignment->alias,
            'problem_alias' => $problems['problems'],
        ]));

        $problems = CourseController::apiAssignmentDetails(new Request([
            'auth_token' => $login->auth_token,
            'assignment' => $assignment->alias,
            'course' => $courseAlias
        ]));

        // Before disordering problems
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
            'assignment_alias' => $assignment->alias,
            'problem_alias' => $problems['problems'],
        ]));

        $problems = CourseController::apiAssignmentDetails(new Request([
            'auth_token' => $login->auth_token,
            'assignment' => $assignment->alias,
            'course' => $courseAlias
        ]));

        // After disordering problems
        for ($i=0; $i < $numberOfProblems; $i++) {
            $this->assertNotEquals($problems['problems'][$i]['alias'], $originalOrder[$i]['alias']);
        }
    }
}
