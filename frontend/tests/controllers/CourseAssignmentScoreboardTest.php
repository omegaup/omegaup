<?php

class CourseAssignmentScoreboardTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Get score of a given assignment happy path
     */
    public function testGetAssignmentScoreboard() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();
        $problemsInAssignment = 3;
        $studentsInCourse = 5;

        // Prepare assignment. Create problems
        $adminLogin = self::login($courseData['admin']);
        $problemAssignmentsMap = [];
        for ($i = 0; $i < $problemsInAssignment; $i++) {
            $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

            \OmegaUp\Controllers\Course::apiAddProblem(new \OmegaUp\Request([
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
            $students[] = \OmegaUp\Test\Factories\Course::addStudentToCourse(
                $courseData
            );
        }

        // Generate runs
        $expectedScores = \OmegaUp\Test\Factories\Course::submitRunsToAssignmentsInCourse(
            $courseData,
            $students,
            [$courseData['assignment_alias']],
            $problemAssignmentsMap
        );

        // Call API
        $adminLogin = self::login($courseData['admin']);
        $response = \OmegaUp\Controllers\Course::apiAssignmentScoreboard(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course' => $courseData['course_alias'],
            'assignment' => $courseData['assignment_alias']
        ]));

        $userScore = [];
        foreach ($expectedScores as $index => $score) {
            $key = array_keys($score);
            $userScore[$index] = $score[$key[0]];
        }

        // Validation. Now, courses should be sorted by ranking.
        array_multisort(
            array_values($userScore),
            SORT_DESC,
            array_keys($expectedScores),
            SORT_ASC,
            $expectedScores
        );
        $expectedPlace = 0;
        $lastScore = 0;
        $i = 0;
        foreach ($expectedScores as $username => $score) {
            if ($lastScore !== $score) {
                $expectedPlace = $i + 1;
                $lastScore = $score;
            }

            $this->assertEquals(
                $username,
                $response['ranking'][$i]['username'],
                'Scoreboard is not properly sorted by username.'
            );
            $this->assertEquals(
                $expectedPlace,
                $response['ranking'][$i]['place'],
                'Course scoreboard place information is wrong.'
            );
            $i++;
        }

        // User should get the same scoreboard information using the function
        // getCourseScoreboardDetailsForTypeScript
        $scoreboard = \OmegaUp\Controllers\Course::getCourseScoreboardDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
                'assignment_alias' => $courseData['assignment_alias']
            ])
        )['smartyProperties']['payload']['scoreboard'];

        $expectedPlace = 0;
        $lastScore = 0;
        $i = 0;
        foreach ($expectedScores as $username => $score) {
            if ($lastScore !== $score) {
                $expectedPlace = $i + 1;
                $lastScore = $score;
            }

            $this->assertEquals(
                $username,
                $scoreboard['ranking'][$i]['username'],
                'Scoreboard is not properly sorted by username.'
            );
            $this->assertEquals(
                $expectedPlace,
                $scoreboard['ranking'][$i]['place'],
                'Course scoreboard place information is wrong.'
            );
            $i++;
        }
    }

    /**
     * Get scoreboard events of a given assignment happy path
     */
    public function testGetAssignmentScoreboardEvents() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();
        $problemsInAssignment = 3;
        $studentsInCourse = 5;

        // Prepare assignment. Create problems
        $adminLogin = self::login($courseData['admin']);
        $problemAssignmentsMap = [];
        for ($i = 0; $i < $problemsInAssignment; $i++) {
            $problemData = \OmegaUp\Test\Factories\Problem::createProblem(
                new \OmegaUp\Test\Factories\ProblemParams(),
                $adminLogin
            );

            \OmegaUp\Controllers\Course::apiAddProblem(new \OmegaUp\Request([
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
            $students[] = \OmegaUp\Test\Factories\Course::addStudentToCourse(
                $courseData,
                null,
                $adminLogin
            );
        }

        // The admin is also going to send runs that should not be present
        // in the scoreboard events
        $students[] = $courseData['admin'];

        // Generate runs
        $expectedScores = \OmegaUp\Test\Factories\Course::submitRunsToAssignmentsInCourse(
            $courseData,
            $students,
            [$courseData['assignment_alias']],
            $problemAssignmentsMap
        );

        // Call API
        $response = \OmegaUp\Controllers\Problemset::apiScoreboardEvents(new \OmegaUp\Request([
            'auth_token' => self::login($courseData['admin'])->auth_token,
            'problemset_id' => $courseData['problemset_id'],
        ]));

        $results = [];
        foreach ($response['events'] as $runData) {
            $results[$runData['username']][$courseData['assignment_alias']][$runData['problem']['alias']] = $runData['problem']['points'];
        }

        // Now remove again the admin from students before making assertions
        array_pop($students);

        // Admin should not be in the results
        $this->assertCount($studentsInCourse, $results);

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
