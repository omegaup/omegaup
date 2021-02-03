<?php

/**
 * Simple test for legacy user Badge
 *
 * @author RuizYugen
 */
// phpcs:ignore Squiz.Classes.ValidClassName.NotCamelCaps
class Badge_cppCourseGraduateTest extends \OmegaUp\Test\BadgesTestCase {
    protected $courseData;
    protected $students;
    protected $submissionSource;
    protected $problems;

    public function setUp(): void {
        parent::setUp();
        $courseAlias = 'introduccion_a_cpp';

        //create course
        $this->courseData = \OmegaUp\Test\Factories\Course::createCourseWithAssignments(
            /*$nAssignments=*/            2,
            $courseAlias
        );
        $studentsInCourse = 2;

        // Prepare assignment. Create four problems: The first three accept
        // submissions and the last one does not.
        $adminLogin = self::login($this->courseData['admin']);
        $this->problems = [];
        for ($i = 0; $i < 3; $i++) {
            $this->problems[] = \OmegaUp\Test\Factories\Problem::createProblem();
        }
        $this->problems[] = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'languages' => '',
            ])
        );

        foreach (array_slice($this->problems, 0, 2) as $problemData) {
            \OmegaUp\Controllers\Course::apiAddProblem(new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $this->courseData['course_alias'],
                'assignment_alias' => $this->courseData['assignment_aliases'][0],
                'problem_alias' => $problemData['request']['problem_alias'],
            ]));
        }
        foreach (array_slice($this->problems, 2, 2) as $problemData) {
            \OmegaUp\Controllers\Course::apiAddProblem(new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $this->courseData['course_alias'],
                'assignment_alias' => $this->courseData['assignment_aliases'][1],
                'problem_alias' => $problemData['request']['problem_alias'],
            ]));
        }

        // Add students to course
        $this->students = [];
        for ($i = 0; $i < $studentsInCourse; $i++) {
            $this->students[] = \OmegaUp\Test\Factories\Course::addStudentToCourse(
                $this->courseData
            );
        }

        $this->submissionSource = "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }";
    }

    public function testCourseCppUserEarnBadge() {
        // This user will receive the badge because they will resolve 4 differents problems
        // of the course
        $studentLogin = \OmegaUp\Test\ControllerTestCase::login(
            $this->students[0]
        );

        // Add one run to the first problem in the first assignment.
        $runResponse = \OmegaUp\Controllers\Run::apiCreate(new \OmegaUp\Request([
            'auth_token' => $studentLogin->auth_token,
            'problemset_id' => $this->courseData['assignment_problemset_ids'][0],
            'problem_alias' => $this->problems[0]['problem']->alias,
        'language' => 'c11-gcc',
        'source' => $this->submissionSource,
        ]));
        \OmegaUp\Test\Factories\Run::gradeRun(
            /*$runData=*/            null,
            1,
            'AC',
            null,
            $runResponse['guid']
        );
        $runResponse = \OmegaUp\Controllers\Run::apiCreate(new \OmegaUp\Request([
            'auth_token' => $studentLogin->auth_token,
            'problemset_id' => $this->courseData['assignment_problemset_ids'][0],
            'problem_alias' => $this->problems[1]['problem']->alias,
            'language' => 'c11-gcc',
        'source' => $this->submissionSource,
        ]));
        \OmegaUp\Test\Factories\Run::gradeRun(
            /*$runData=*/            null,
            1,
            'AC',
            null,
            $runResponse['guid']
        );

        // Add one run to the third problem in the second assignment
        $runResponse = \OmegaUp\Controllers\Run::apiCreate(new \OmegaUp\Request([
            'auth_token' => $studentLogin->auth_token,
            'problemset_id' => $this->courseData['assignment_problemset_ids'][1],
            'problem_alias' => $this->problems[2]['problem']->alias,
            'language' => 'c11-gcc',
            'source' => $this->submissionSource,
        ]));
        \OmegaUp\Test\Factories\Run::gradeRun(
            /*$runData=*/            null,
            1,
            'AC',
            null,
            $runResponse['guid']
        );

        $queryPath = static::OMEGAUP_BADGES_ROOT . '/cppCourseGraduate/' . static::QUERY_FILE;
        $results = self::getSortedResults(file_get_contents($queryPath));
        $expected = [$this->students[0]->user_id];
        $this->assertEquals($expected, $results);
    }

    public function testCourseCppUserDoNotEarnBadge() {
        // This user will not receive the badge because they will only solve a problem
        // with multiple submissions.
        $studentLogin = \OmegaUp\Test\ControllerTestCase::login(
            $this->students[1]
        );

        for ($i = 0; $i < 10; $i++) {
            // Add one run to the first problem in the first assignment.
            $runResponse = \OmegaUp\Controllers\Run::apiCreate(new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'problemset_id' => $this->courseData['assignment_problemset_ids'][0],
                'problem_alias' => $this->problems[0]['problem']->alias,
                'language' => 'c11-gcc',
                'source' => $this->submissionSource,
            ]));
            \OmegaUp\Test\Factories\Run::gradeRun(
                /*$runData=*/                null,
                1,
                'AC',
                null,
                $runResponse['guid']
            );
        }

        $queryPath = static::OMEGAUP_BADGES_ROOT . '/cppCourseGraduate/' . static::QUERY_FILE;
        $results = self::getSortedResults(file_get_contents($queryPath));
        $expected = [];
        $this->assertEquals($expected, $results);
    }
}
