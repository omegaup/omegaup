<?php

/**
 *
 * @author @joemmanuel
 */

class CourseAssignmentScoreboardTest extends OmegaupTestCase {
    /**
     * Get score of a given assignment happy path
     */
    public function testGetAssignmentScoreboard() {
        $courseData = CoursesFactory::createCourseWithOneAssignment();
        $problemsInAssignment = 3;
        $studentsInCourse = 5;

        // Prepare assignment. Create problems
        $adminLogin = self::login($courseData['admin']);
        $problemAssignmentsMap = [];
        for ($i = 0; $i < $problemsInAssignment; $i++) {
            $problemData = ProblemsFactory::createProblem();

            CourseController::apiAddProblem(new Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment_alias'],
                'problem_alias' => $problemData['request']['alias'],
            ]));

            $problemAssignmentsMap[$courseData['assignment_alias']][] = $problemData;
        }

        // Create & add students to course
        $students = [];
        for ($i = 0; $i < $studentsInCourse; $i++) {
            $students[] = CoursesFactory::addStudentToCourse($courseData);
        }

        // Generate runs
        $expectedScores = CoursesFactory::submitRunsToAssignmentsInCourse($courseData, $students, [$courseData['assignment_alias']], $problemAssignmentsMap);

        // Call API
        $adminLogin = self::login($courseData['admin']);
        $response = CourseController::apiScoreboard(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'assignment_alias' => $courseData['assignment_alias']
        ]));

        // Validation
        array_multisort(array_values($expectedScores), SORT_DESC, array_keys($expectedScores), SORT_ASC, $expectedScores);
        $expectedPlace = 0;
        $lastScore = 0;
        $i = 0;
        foreach ($expectedScores as $username => $score) {
            if ($lastScore != $score) {
                $expectedPlace = $i + 1;
                $lastScore = $score;
            }

            $this->assertEquals($username, $response['ranking'][$i]['username'], 'Scoreboard is not properly sorted by contest score.');
            $this->assertEquals($expectedPlace, $response['ranking'][$i]['place']);
            $i++;
        }
    }
}
