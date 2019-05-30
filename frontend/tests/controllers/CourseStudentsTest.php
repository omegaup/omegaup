<?php

/**
 * Tests that students' progress can be tracked.
 */
class CourseStudentsTest extends OmegaupTestCase {
    /**
     * Basic apiStudentProgress test.
     */
    public function testAddStudentToCourse() {
        $courseData = CoursesFactory::createCourseWithOneAssignment();
        $studentsInCourse = 5;

        // Prepare assignment. Create problems
        $adminLogin = self::login($courseData['admin']);
        $problemData = ProblemsFactory::createProblem();

        CourseController::apiAddProblem(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'assignment_alias' => $courseData['assignment_alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
        ]));

        $problem = $problemData['problem'];

        // Add students to course
        $students = [];
        for ($i = 0; $i < $studentsInCourse; $i++) {
            $students[] = CoursesFactory::addStudentToCourse($courseData);
        }

        // Add one run to one of the problems.
        $submissionSource = "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }";
        {
            $studentLogin = OmegaupTestCase::login($students[0]);
            $runResponsePA = RunController::apiCreate(new Request([
                'auth_token' => $studentLogin->auth_token,
                'problemset_id' => $courseData['assignment']->problemset_id,
                'problem_alias' => $problem->alias,
                'language' => 'c',
                'source' => $submissionSource,
            ]));
            RunsFactory::gradeRun(null /*runData*/, 0.5, 'PA', null, $runResponsePA['guid']);
        }

        // Call API
        $adminLogin = self::login($courseData['admin']);
        $response = CourseController::apiStudentProgress(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'assignment_alias' => $courseData['assignment_alias'],
            'usernameOrEmail' => $students[0]->username,
        ]));
        $this->assertCount(1, $response['problems']);
        $this->assertCount(1, $response['problems'][0]['runs']);
        $this->assertEquals($response['problems'][0]['runs'][0]['source'], $submissionSource);
        $this->assertEquals($response['problems'][0]['runs'][0]['score'], 0.5);
    }
}
