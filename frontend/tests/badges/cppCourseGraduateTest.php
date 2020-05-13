<?php

/**
 * Simple test for legacy user Badge
 *
 * @author RuizYugen
 */
class CppCourseGraduate extends \OmegaUp\Test\BadgesTestCase {
    public function testCourseCpp() {
        $courseAlias = 'introduccion_a_cpp';

        //create course
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithAssignments(
            /*$nAssignments=*/ 2,
            $courseAlias
        );
        $studentsInCourse = 2;

        // Prepare assignment. Create four problems: The first three accept
        // submissions and the last one does not.
        $adminLogin = self::login($courseData['admin']);
        $problems = [];
        for ($i = 0; $i < 3; $i++) {
            $problems[] = \OmegaUp\Test\Factories\Problem::createProblem();
        }
        $problems[] = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'languages' => '',
            ])
        );

        foreach (array_slice($problems, 0, 2) as $problemData) {
            \OmegaUp\Controllers\Course::apiAddProblem(new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment_aliases'][0],
                'problem_alias' => $problemData['request']['problem_alias'],
            ]));
        }
        foreach (array_slice($problems, 2, 2) as $problemData) {
            \OmegaUp\Controllers\Course::apiAddProblem(new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment_aliases'][1],
                'problem_alias' => $problemData['request']['problem_alias'],
            ]));
        }

        // Add students to course
        $students = [];
        for ($i = 0; $i < $studentsInCourse; $i++) {
            $students[] = \OmegaUp\Test\Factories\Course::addStudentToCourse(
                $courseData
            );
        }

        $submissionSource = "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }";
        {
            // This user will recive the badge because he will resolve 4 differents problems of the course
            $studentLogin = \OmegaUp\Test\ControllerTestCase::login(
                $students[0]
            );

            // Add one run to the first problem in the first assignment.
            $runResponse = \OmegaUp\Controllers\Run::apiCreate(new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'problemset_id' => $courseData['assignment_problemset_ids'][0],
                'problem_alias' => $problems[0]['problem']->alias,
                'language' => 'c11-gcc',
                'source' => $submissionSource,
            ]));
            \OmegaUp\Test\Factories\Run::gradeRun(
                /*$runData=*/ null,
                1,
                'AC',
                null,
                $runResponse['guid']
            );
            $runResponse = \OmegaUp\Controllers\Run::apiCreate(new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'problemset_id' => $courseData['assignment_problemset_ids'][0],
                'problem_alias' => $problems[1]['problem']->alias,
                'language' => 'c11-gcc',
                'source' => $submissionSource,
            ]));
            \OmegaUp\Test\Factories\Run::gradeRun(
                /*$runData=*/ null,
                1,
                'AC',
                null,
                $runResponse['guid']
            );

            // Add one run to the third problem in the second assignment
            $runResponse = \OmegaUp\Controllers\Run::apiCreate(new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'problemset_id' => $courseData['assignment_problemset_ids'][1],
                'problem_alias' => $problems[2]['problem']->alias,
                'language' => 'c11-gcc',
                'source' => $submissionSource,
            ]));
            \OmegaUp\Test\Factories\Run::gradeRun(
                /*$runData=*/ null,
                1,
                'AC',
                null,
                $runResponse['guid']
            );

            // This user will not recibe the badge because he will resolve a problem with multiple solutions
            $studentLogin = \OmegaUp\Test\ControllerTestCase::login(
                $students[1]
            );

        for ($i = 0; $i < 10; $i++) {
            // Add one run to the first problem in the first assignment.
            $runResponse = \OmegaUp\Controllers\Run::apiCreate(new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'problemset_id' => $courseData['assignment_problemset_ids'][0],
                'problem_alias' => $problems[0]['problem']->alias,
                'language' => 'c11-gcc',
                'source' => $submissionSource,
            ]));
            \OmegaUp\Test\Factories\Run::gradeRun(
                /*$runData=*/ null,
                1,
                'AC',
                null,
                $runResponse['guid']
            );
        }
        }
        $queryPath = static::OMEGAUP_BADGES_ROOT . '/cppCourseGraduate/' . static::QUERY_FILE;
        $results = self::getSortedResults(file_get_contents($queryPath));
        $expected = [$students[0]->user_id];
        $this->assertEquals($expected, $results);
    }
}
