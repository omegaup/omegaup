<?php

/**
 * Simple test for introToAlgorithmsCourseGraduate user Badge
 *
 * @author RodCross
 */
// phpcs:ignore Squiz.Classes.ValidClassName.NotCamelCaps
class Badge_introToAlgorithmsCourseGraduateTest extends \OmegaUp\Test\BadgesTestCase {
    protected $courseData;
    protected $problems;
    protected $students;

    public function setUp(): void {
        parent::setUp();
        $courseAlias = 'introduccion_a_algoritmos';

        // Create four problems
        $this->problems = [];
        for ($i = 0; $i < 4; $i++) {
            $this->problems[] = \OmegaUp\Test\Factories\Problem::createProblem();
        }

        // Create course
        $this->courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            null,
            null,
            \OmegaUp\Controllers\Course::ADMISSION_MODE_PRIVATE,
            'no',
            'false',
            0,
            120,
            120,
            $courseAlias
        );
        $assignmentAlias = $this->courseData['assignment_alias'];

        // Login
        $login = self::login($this->courseData['admin']);

        // Add the problems to the assignment
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseAlias,
            $assignmentAlias,
            $this->problems
        );

        // Create students
        $this->students = [];
        $this->students[0] = \OmegaUp\Test\Factories\User::createUser();
        $this->students[1] = \OmegaUp\Test\Factories\User::createUser();

        // Add students to course
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $this->courseData,
            $this->students[0]['identity']
        );
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $this->courseData,
            $this->students[1]['identity']
        );
    }

    public function testIntroToAlgorithmsCourseEarnBadge() {
        // The student solves 3 out of 4 problems
        for ($i = 0; $i < 3; $i++) {
            $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
                $this->problems[$i],
                $this->courseData,
                $this->students[0]['identity']
            );
            \OmegaUp\Test\Factories\Run::gradeRun($runData);
        }

        $queryPath = static::OMEGAUP_BADGES_ROOT . '/introToAlgorithmsCourseGraduate/' . static::QUERY_FILE;
        $results = self::getSortedResults(file_get_contents($queryPath));
        $expected = [$this->students[0]['user']->user_id];
        $this->assertEquals($expected, $results);
    }

    public function testIntroToAlgorithmsCourseDoNotEarnBadge() {
        // The student solves the same problem with multiple submissions
        for ($i = 0; $i < 10; $i++) {
            $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
                $this->problems[0],
                $this->courseData,
                $this->students[1]['identity']
            );
            \OmegaUp\Test\Factories\Run::gradeRun($runData);
        }

        $queryPath = static::OMEGAUP_BADGES_ROOT . '/introToAlgorithmsCourseGraduate/' . static::QUERY_FILE;
        $results = self::getSortedResults(file_get_contents($queryPath));
        $expected = [];
        $this->assertEquals($expected, $results);
    }
}
