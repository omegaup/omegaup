<?php

/**
 *
 * @author @joemmanuel
 */

class CourseStudentListTest extends OmegaupTestCase {
    /**
     * Basic apiStudentList test
     */
    public function testCourseStudentList() {
        // Create a course
        $courseData = CoursesFactory::createCourse();

        // Add some students to course
        $students = [];
        for ($i = 0; $i < 3; $i++) {
            $students[$i] = CoursesFactory::addStudentToCourse($courseData);
        }

        // Call apiStudentList by an admin
        $adminLogin = self::login($courseData['admin']);
        $response = CourseController::apiListStudents(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias']
        ]));

        $this->assertEquals('ok', $response['status']);
        foreach ($students as $s) {
            $this->assertArrayContainsWithPredicate($response['students'], function ($value) use ($s) {
                return $value['username'] == $s->username;
            });
        }
    }

    /**
     * List can only be retreived by an admin
     * @expectedException ForbiddenAccessException
     */
    public function testCourseStudentListNonAdmin() {
        $courseData = CoursesFactory::createCourse();

        // Call apiStudentList by another random user
        $userLogin = self::login(UserFactory::createUser());
        $response = CourseController::apiListStudents(new Request([
            'auth_token' => $userLogin->auth_token,
            'course_alias' => $courseData['course_alias']
        ]));
    }

    /**
     * Course does not exists test
     * @expectedException NotFoundException
     */
    public function testCourseStudentListInvalidCourse() {
        // Call apiStudentList by another random user
        $userLogin = self::login(UserFactory::createUser());
        $response = CourseController::apiListStudents(new Request([
            'auth_token' => $userLogin->auth_token,
            'course_alias' => 'foo'
        ]));
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
        $courseData = CoursesFactory::createCourseWithNAssignmentsPerType(['homework' => 5, 'test' => 5]);

        // Add problems to assignments
        $adminLogin = self::login($courseData['admin']);
        for ($i = 0; $i < $homeworkCount + $testCount; $i++) {
            $assignmentAlias = $courseData['assignment_aliases'][$i];
            $problemAssignmentsMap[$assignmentAlias] = [];

            for ($j = 0; $j < $problemsPerAssignment; $j++) {
                $problemData = ProblemsFactory::createProblem();
                CourseController::apiAddProblem(new Request([
                    'auth_token' => $adminLogin->auth_token,
                    'course_alias' => $courseData['course_alias'],
                    'assignment_alias' => $assignmentAlias,
                    'problem_alias' => $problemData['request']['alias'],
                ]));
                $problemAssignmentsMap[$assignmentAlias][] = $problemData;
            }
        }

        // Create & add students to course
        $students = [];
        for ($i = 0; $i < $studentCount; $i++) {
            $students[] = CoursesFactory::addStudentToCourse($courseData);
        }

        // Submit runs - Simulate each student submitting runs to some problems and some others not.
        // Also, sometimes only PAs are sent, other times ACs.
        $course = CoursesDAO::getByAlias($courseData['course_alias']);
        $expectedScores = [];
        for ($s = 0; $s < $studentCount; $s++) {
            $studentUsername = $students[$s]->username;
            $expectedScores[$studentUsername] = [];
            $studentLogin = self::login($students[$s]);

            // Loop through all problems inside assignments created
            $p = 0;
            foreach ($courseData['assignment_aliases'] as $assignmentAlias) {
                $assignment = AssignmentsDAO::search(new Assignments([
                    'course_id' => $course->course_id,
                    'alias' => $assignmentAlias,
                ]))[0];

                $expectedScores[$studentUsername][$assignmentAlias] = 0;

                foreach ($problemAssignmentsMap[$assignmentAlias] as $problemData) {
                    $p++;
                    if ($s % 2 == $p % 2) {
                        // PA run
                        $runResponsePA = RunController::apiCreate(new Request([
                            'auth_token' => $studentLogin->auth_token,
                            'problemset_id' => $assignment->problemset_id,
                            'problem_alias' => $problemData['request']['alias'],
                            'language' => 'c',
                            'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
                        ]));
                        RunsFactory::gradeRun(null /*runData*/, 0.5, 'PA', 200, $runResponsePA['guid']);
                        $expectedScores[$studentUsername][$assignmentAlias] += 0.5;

                        if (($s + $p) % 3 == 0) {
                            // 100 pts run
                            $runResponseAC = RunController::apiCreate(new Request([
                                'auth_token' => $studentLogin->auth_token,
                                'problemset_id' => $assignment->problemset_id,
                                'problem_alias' => $problemData['request']['alias'],
                                'language' => 'c',
                                'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
                            ]));
                            RunsFactory::gradeRun(null /*runData*/, 1, 'AC', 200, $runResponseAC['guid']);
                            $expectedScores[$studentUsername][$assignmentAlias] += 0.5;
                        }
                    }
                }
            }
        }

        // Adding a new student with no runs. Should show in progress
        $studentWithNoRuns = CoursesFactory::addStudentToCourse($courseData);

        // Call API
        $adminLogin = self::login($courseData['admin']);
        $response = CourseController::apiListStudents(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias']
        ]));

        // Verify response maps to expected scores
        $this->assertEquals('ok', $response['status']);
        foreach ($expectedScores as $username => $scores) {
            $student = $this->findByPredicate($response['students'], function ($value) use ($username) {
                return $value['username'] == $username;
            });
            if ($student == null) {
                $this->fail("Failed asserting that the response has student {$username}");
            }

            foreach ($scores as $assignmentAlias => $assignmentScore) {
                $this->assertArrayHasKey($assignmentAlias, $student['progress'], "Alias $assignmentAlias not found in response");
                $this->assertEquals($assignmentScore, $student['progress'][$assignmentAlias], "Score for $username $assignmentAlias did not match expected.");
            }
        }

        // Verify the student with no runs is on the list but with 0 reported assignments
        $student = $this->findByPredicate($response['students'], function ($value) use ($studentWithNoRuns) {
            return $value['username'] == $studentWithNoRuns->username;
        });
        if ($student == null) {
            $this->fail("Failed asserting that the response has student {$studentWithNoRuns->username}");
        }
        $this->assertEquals(0, count($student['progress']));
    }
}
