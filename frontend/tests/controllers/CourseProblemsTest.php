<?php

class CourseProblemsTest extends OmegaupTestCase {
    public function testOrderProblems() {
        // Create a test course
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        $login = self::login($identity);

        // Create a course with an assignment
        $courseData = CoursesFactory::createCourseWithOneAssignment(
            $identity,
            $login
        );
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add 3 problems to the assignment.
        $numberOfProblems = 3;
        for ($i = 0; $i < $numberOfProblems; $i++) {
            $problemData[$i] = ProblemsFactory::createProblem(new ProblemParams([
                'visibility' => 1,
                'author' => $identity,
            ]), $login);
        }
        CoursesFactory::addProblemsToAssignment(
            $login,
            $courseAlias,
            $assignmentAlias,
            $problemData
        );

        $problems = \OmegaUp\Controllers\Course::apiAssignmentDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'assignment' => $assignmentAlias,
            'course' => $courseAlias
        ]));

        $problems['problems'][0]['order'] = 1;
        $problems['problems'][1]['order'] = 2;
        $problems['problems'][2]['order'] = 3;

        \OmegaUp\Controllers\Course::apiUpdateProblemsOrder(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'course_alias' => $courseAlias,
            'assignment_alias' => $assignmentAlias,
            'problems' => $problems['problems'],
        ]));

        $problems = \OmegaUp\Controllers\Course::apiAssignmentDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'assignment' => $assignmentAlias,
            'course' => $courseAlias
        ]));

        // Before reordering problems
        for ($i = 0; $i < $numberOfProblems; $i++) {
            $originalOrder[$i] = [
                'alias' => $problems['problems'][$i]['alias'],
                'order' => $problems['problems'][$i]['order']
            ];
            $this->assertEquals($problems['problems'][$i]['order'], ($i + 1));
        }

        $problems['problems'][0]['order'] = 2;
        $problems['problems'][1]['order'] = 3;
        $problems['problems'][2]['order'] = 1;

        \OmegaUp\Controllers\Course::apiUpdateProblemsOrder(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'course_alias' => $courseAlias,
            'assignment_alias' => $assignmentAlias,
            'problems' => $problems['problems'],
        ]));

        $problems = \OmegaUp\Controllers\Course::apiAssignmentDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'assignment' => $assignmentAlias,
            'course' => $courseAlias
        ]));

        // After disordering problems
        for ($i = 0; $i < $numberOfProblems; $i++) {
            $this->assertNotEquals(
                $problems['problems'][$i]['alias'],
                $originalOrder[$i]['alias']
            );
        }
    }

    public function testCourseProblemUsers() {
        ['user' => $admin, 'identity' => $identity] = UserFactory::createUser();
        ['user' => $student, 'identity' => $identityStudent] = UserFactory::createUser();

        // Create a course with an assignment
        $adminLogin = self::login($identity);
        $courseData = CoursesFactory::createCourseWithOneAssignment(
            $identity,
            $adminLogin
        );
        CoursesFactory::addStudentToCourse(
            $courseData,
            $identityStudent,
            $adminLogin
        );
        $course = $courseData['course'];
        $assignment = $courseData['assignment'];

        $problemData = [];
        for ($i = 0; $i < 3; $i++) {
            $problemData[] = ProblemsFactory::createProblem(new ProblemParams([
                'visibility' => 1,
                'author' => $identity,
            ]), $adminLogin);
        }
        CoursesFactory::addProblemsToAssignment(
            $adminLogin,
            $course->alias,
            $assignment->alias,
            $problemData
        );

        // Send runs to problem 1 (PA) and 2 (AC).
        $login = self::login($identityStudent);
        {
            $response = \OmegaUp\Controllers\Run::apiCreate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problemset_id' => $assignment->problemset_id,
                'problem_alias' => $problemData[0]['problem']->alias,
                'language' => 'c',
                'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
            ]));
            RunsFactory::gradeRun(
                null /*runData*/,
                0.5,
                'PA',
                null,
                $response['guid']
            );
        }
        {
            $response = \OmegaUp\Controllers\Run::apiCreate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problemset_id' => $assignment->problemset_id,
                'problem_alias' => $problemData[1]['problem']->alias,
                'language' => 'c',
                'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
            ]));
            RunsFactory::gradeRun(
                null /*runData*/,
                1.0,
                'AC',
                null,
                $response['guid']
            );
        }

        // Ensure that the student has attempted problems 1 and 2.
        $response = \OmegaUp\Controllers\Course::apiGetProblemUsers(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $course->alias,
            'problem_alias' => $problemData[0]['problem']->alias,
        ]));
        $this->assertEquals(
            [$identityStudent->username],
            $response['identities']
        );
        $response = \OmegaUp\Controllers\Course::apiGetProblemUsers(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $course->alias,
            'problem_alias' => $problemData[1]['problem']->alias,
        ]));
        $this->assertEquals(
            [$identityStudent->username],
            $response['identities']
        );
        $response = \OmegaUp\Controllers\Course::apiGetProblemUsers(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $course->alias,
            'problem_alias' => $problemData[2]['problem']->alias,
        ]));
        $this->assertEquals([], $response['identities']);
    }
}
