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
                'problem_alias' => $problemData['request']['problem_alias'],
            ]));

            $problemAssignmentsMap[$courseData['assignment_alias']][] = $problemData;
        }

        // Add students to course
        $students = [];
        for ($i = 0; $i < $studentsInCourse; $i++) {
            $students[] = CoursesFactory::addStudentToCourse($courseData);
        }

        // Generate runs
        $expectedScores = CoursesFactory::submitRunsToAssignmentsInCourse(
            $courseData,
            $students,
            [$courseData['assignment_alias']],
            $problemAssignmentsMap
        );

        // Call API
        $adminLogin = self::login($courseData['admin']);
        $response = CourseController::apiAssignmentScoreboard(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course' => $courseData['course_alias'],
            'assignment' => $courseData['assignment_alias']
        ]));

        // Validation. Courses should be sorted by names instead of ranking.
        array_multisort(
            array_keys($expectedScores),
            SORT_ASC,
            array_values($expectedScores),
            SORT_DESC,
            $expectedScores
        );
        $expectedPlace = 0;
        $lastScore = 0;
        $i = 0;
        foreach ($expectedScores as $username => $score) {
            if ($lastScore != $score) {
                $expectedPlace = $i + 1;
                $lastScore = $score;
            }

            $this->assertEquals(
                $username,
                $response['ranking'][$i]['username'],
                'Scoreboard is not properly sorted by username.'
            );
            $this->assertFalse(
                array_key_exists('place', $response['ranking'][$i]),
                'Course scoreboard contains place information and should not.'
            );
            $i++;
        }
    }

    /**
     * Get scoreboard events of a given assignment happy path
     */
    public function testGetAssignmentScoreboardEvents() {
        $courseData = CoursesFactory::createCourseWithOneAssignment();
        $problemsInAssignment = 3;
        $studentsInCourse = 5;

        // Prepare assignment. Create problems
        $adminLogin = self::login($courseData['admin']);
        $problemAssignmentsMap = [];
        for ($i = 0; $i < $problemsInAssignment; $i++) {
            $problemData = ProblemsFactory::createProblem(
                new ProblemParams(),
                $adminLogin
            );

            CourseController::apiAddProblem(new Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment_alias'],
                'problem_alias' => $problemData['request']['problem_alias'],
            ]));

            $problemAssignmentsMap[$courseData['assignment_alias']][] = $problemData;
        }

        // Add students to course
        $students = [];
        for ($i = 0; $i < $studentsInCourse; $i++) {
            $students[] = CoursesFactory::addStudentToCourse(
                $courseData,
                null,
                $adminLogin
            );
        }

        // Generate runs
        $expectedScores = CoursesFactory::submitRunsToAssignmentsInCourse(
            $courseData,
            $students,
            [$courseData['assignment_alias']],
            $problemAssignmentsMap
        );

        // Call API
        $response = ProblemsetController::apiScoreboardEvents(new Request([
            'auth_token' => $adminLogin->auth_token,
            'problemset_id' => $courseData['problemset_id'],
        ]));

        $results = [];
        foreach ($response['events'] as $runData) {
            $results[$runData['username']][$courseData['assignment_alias']][$runData['problem']['alias']] = $runData['problem']['points'];
        }

        // From the map above, there are 9 meaningful combinations for events
        $this->assertNotEmpty($response['events']);

        // Score result and expected score must contain the same value
        foreach ($results as $username => $student) {
            $this->assertEquals(
                array_sum($student[$courseData['assignment_alias']]),
                $expectedScores[$username][$courseData['assignment_alias']],
                'Scoreboard is not properly matched with the expected scores.'
            );
        }
    }
}
