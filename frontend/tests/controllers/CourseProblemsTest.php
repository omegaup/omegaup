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
            $problemData[$i] = ProblemsFactory::createProblem(new ProblemParams([
                'visibility' => 1,
                'author' => $user,
            ]), $login);
        }
        CoursesFactory::addProblemsToAssignment($login, $courseAlias, $assignmentAlias, $problemData);

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
        for ($i=0; $i < $numberOfProblems; $i++) {
            $this->assertNotEquals($problems['problems'][$i]['alias'], $originalOrder[$i]['alias']);
        }
    }

    public function testCourseProblemUsers() {
        $admin = UserFactory::createUser();
        $student = UserFactory::createUser();

        // Create a course with an assignment
        $adminLogin = self::login($admin);
        $courseData = CoursesFactory::createCourseWithOneAssignment($admin, $adminLogin);
        CoursesFactory::addStudentToCourse($courseData, $student, $adminLogin);
        $course = $courseData['course'];
        $assignment = $courseData['assignment'];

        $problemData = [];
        for ($i = 0; $i < 3; $i++) {
            $problemData[] = ProblemsFactory::createProblem(new ProblemParams([
                'visibility' => 1,
                'author' => $admin,
            ]), $adminLogin);
        }
        CoursesFactory::addProblemsToAssignment($adminLogin, $course->alias, $assignment->alias, $problemData);

        // Send runs to problem 1 (PA) and 2 (AC).
        $login = self::login($student);
        {
            $response = \OmegaUp\Controllers\Run::apiCreate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problemset_id' => $assignment->problemset_id,
                'problem_alias' => $problemData[0]['problem']->alias,
                'language' => 'c',
                'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
            ]));
            RunsFactory::gradeRun(null /*runData*/, 0.5, 'PA', null, $response['guid']);
        }
        {
            $response = \OmegaUp\Controllers\Run::apiCreate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problemset_id' => $assignment->problemset_id,
                'problem_alias' => $problemData[1]['problem']->alias,
                'language' => 'c',
                'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
            ]));
            RunsFactory::gradeRun(null /*runData*/, 1.0, 'AC', null, $response['guid']);
        }

        // Ensure that the student has attempted problems 1 and 2.
        $response = \OmegaUp\Controllers\Course::apiGetProblemUsers(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $course->alias,
            'problem_alias' => $problemData[0]['problem']->alias,
        ]));
        $this->assertEquals([$student->username], $response['identities']);
        $response = \OmegaUp\Controllers\Course::apiGetProblemUsers(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $course->alias,
            'problem_alias' => $problemData[1]['problem']->alias,
        ]));
        $this->assertEquals([$student->username], $response['identities']);
        $response = \OmegaUp\Controllers\Course::apiGetProblemUsers(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $course->alias,
            'problem_alias' => $problemData[2]['problem']->alias,
        ]));
        $this->assertEquals([], $response['identities']);
    }
}
