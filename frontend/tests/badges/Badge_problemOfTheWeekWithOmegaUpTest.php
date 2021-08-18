<?php

/**
 * Simple test for ProblemOfTheWeekWithOmegaUp Badge
 */
// phpcs:ignore Squiz.Classes.ValidClassName.NotCamelCaps
class Badge_problemOfTheWeekWithOmegaUpTest extends \OmegaUp\Test\BadgesTestCase {
    public function test1SolvedProblem(): void {
        // Create problems
        $problems = [];
        for ($i = 0; $i < 1; $i++) {
            $problems[] = \OmegaUp\Test\Factories\Problem::createProblem();
        }

        // Create course
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            /*$admin=*/            null,
            /*$adminLogin=*/ null,
            /*$admissionMode=*/ \OmegaUp\Controllers\Course::ADMISSION_MODE_PRIVATE,
            /*$requestsUserInformation=*/ 'no',
            /*$showScoreboard=*/ 'false',
            /*$startTimeDelay=*/ 0,
            /*$courseDuration=*/ 120,
            /*$assignmentDuration=*/ 120,
            /*$courseAlias=*/ 'ResolviendoProblemas2021'
        );
        $assignmentAlias = $courseData['assignment_alias'];

        // Login
        $login = self::login($courseData['admin']);

        // Add the problems to the assignment
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            'ResolviendoProblemas2021',
            $assignmentAlias,
            $problems
        );

        // Create students
        $students = [];
        $students[0] = \OmegaUp\Test\Factories\User::createUser();
        $students[1] = \OmegaUp\Test\Factories\User::createUser();

        // Add students to course
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $students[0]['identity']
        );
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $students[1]['identity']
        );

        // One student solves 1 problem
        for ($i = 0; $i < 1; $i++) {
            $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
                $problems[$i],
                $courseData,
                $students[0]['identity'],
                'c11-gcc'
            );
            \OmegaUp\Test\Factories\Run::gradeRun($runData);
        }

        $queryPath = self::OMEGAUP_BADGES_ROOT . '/problemOfTheWeekWithOmegaUp/' . self::QUERY_FILE;
        $results = self::getSortedResults(file_get_contents($queryPath));
        $expected = [$students[0]['user']->user_id];
        $this->assertEquals($expected, $results);
    }
}
