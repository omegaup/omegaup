<?php

/**
 *
 * @author @joemmanuel
 */

class CourseStudentListTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Basic apiStudentList test
     */
    public function testCourseStudentList() {
        // Create a course
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();

        // Add some students to course
        $students = [];
        for ($i = 0; $i < 3; $i++) {
            $students[$i] = \OmegaUp\Test\Factories\Course::addStudentToCourse(
                $courseData
            );
        }

        // Call apiStudentList by an admin
        $adminLogin = self::login($courseData['admin']);
        $response = \OmegaUp\Controllers\Course::apiListStudents(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias']
        ]));

        foreach ($students as $s) {
            $this->assertArrayContainsWithPredicate($response['students'], function ($value) use ($s) {
                return $value['username'] == $s->username;
            });
        }
    }

    /**
     * List can only be retreived by an admin
     */
    public function testCourseStudentListNonAdmin() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();

        // Call apiStudentList by another random user
        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        $userLogin = self::login($identity);
        try {
            \OmegaUp\Controllers\Course::apiListStudents(new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Course does not exists test
     */
    public function testCourseStudentListInvalidCourse() {
        // Call apiStudentList by another random user
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $userLogin = self::login($identity);
        try {
            \OmegaUp\Controllers\Course::apiListStudents(new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
                'course_alias' => 'foo',
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\NotfoundException $e) {
            $this->assertEquals('courseNotFound', $e->getMessage());
        }
    }

    /**
     *  Tests progress in apiDetails is correctly calculated for multiple
     *  assignments and multiple students
     */
    public function testCourseStudentListWithProgressMultipleAssignments() {
        $homeworkCount = 5;
        $testCount = 5;
        $problemsPerAssignment = 3;
        $studentCount = 5;
        $problemAssignmentsMap = [];

        // Create course with assignments
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithNAssignmentsPerType(
            ['homework' => 5, 'test' => 5]
        );

        // Add problems to assignments
        $adminLogin = self::login($courseData['admin']);
        for ($i = 0; $i < $homeworkCount + $testCount; $i++) {
            $assignmentAlias = $courseData['assignment_aliases'][$i];
            $problemAssignmentsMap[$assignmentAlias] = [];

            for ($j = 0; $j < $problemsPerAssignment; $j++) {
                $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
                \OmegaUp\Controllers\Course::apiAddProblem(new \OmegaUp\Request([
                    'auth_token' => $adminLogin->auth_token,
                    'course_alias' => $courseData['course_alias'],
                    'assignment_alias' => $assignmentAlias,
                    'problem_alias' => $problemData['request']['problem_alias'],
                ]));
                $problemAssignmentsMap[$assignmentAlias][] = $problemData;
            }
        }

        // Create & add students to course
        $students = [];
        for ($i = 0; $i < $studentCount; $i++) {
            $students[] = \OmegaUp\Test\Factories\Course::addStudentToCourse(
                $courseData
            );
        }

        // Submit runs - Simulate each student submitting runs to some problems and some others not.
        // Also, sometimes only PAs are sent, other times ACs.
        $expectedScores = \OmegaUp\Test\Factories\Course::submitRunsToAssignmentsInCourse(
            $courseData,
            $students,
            $courseData['assignment_aliases'],
            $problemAssignmentsMap
        );

        // Adding a new student with no runs. Should show in progress
        $studentWithNoRuns = \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData
        );

        // Call API
        $adminLogin = self::login($courseData['admin']);
        $response = \OmegaUp\Controllers\Course::apiListStudents(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias']
        ]));

        // Verify response maps to expected scores
        foreach ($expectedScores as $username => $scores) {
            $student = $this->findByPredicate($response['students'], function ($value) use ($username) {
                return $value['username'] == $username;
            });
            if (is_null($student)) {
                $this->fail(
                    "Failed asserting that the response has student {$username}"
                );
            }

            foreach ($scores as $assignmentAlias => $assignmentScore) {
                $this->assertArrayHasKey(
                    $assignmentAlias,
                    $student['progress'],
                    "Alias $assignmentAlias not found in response"
                );
                $this->assertEquals(
                    $assignmentScore,
                    $student['progress'][$assignmentAlias],
                    "Score for $username $assignmentAlias did not match expected."
                );
            }
        }

        // Verify the student with no runs is on the list but with 0 reported assignments
        $student = $this->findByPredicate($response['students'], function ($value) use ($studentWithNoRuns) {
            return $value['username'] == $studentWithNoRuns->username;
        });
        if (is_null($student)) {
            $this->fail(
                "Failed asserting that the response has student {$studentWithNoRuns->username}"
            );
        }
        $this->assertEquals(0, count($student['progress']));
    }
}
