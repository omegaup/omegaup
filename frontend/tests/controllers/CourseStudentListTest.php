<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

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
            $this->assertArrayContainsWithPredicate(
                $response['students'],
                fn ($value) => $value['username'] == $s->username
            );
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

    public function testGetStudentsProgressForCourse() {
        $problemsData = [];
        for ($i = 0; $i < 4; $i++) {
            $problemsData[] = \OmegaUp\Test\Factories\Problem::createProblem();
        }

        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();
        $courseAlias = $courseData['course_alias'];
        $assignment = $courseData['assignment_alias'];

        $login = self::login($courseData['admin']);

        // assignment is going to have the first 3 problems
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseAlias,
            $assignment,
            [$problemsData[0], $problemsData[1], $problemsData[2]]
        );

        $users = [];
        $participants = [];
        for ($i = 0; $i < 3; $i++) {
            [
                'user' => $users[],
                'identity' => $participants[]
            ] = \OmegaUp\Test\Factories\User::createUser();

            \OmegaUp\Test\Factories\Course::addStudentToCourse(
                $courseData,
                $participants[$i]
            );
        }

        // Sort participants for tests asserts
        usort(
            $participants,
            fn ($a, $b) => strcasecmp(
                !empty($a->name) ? $a->name : $a->username,
                !empty($b->name) ? $b->name : $b->username
            )
        );

        // First student will solve problem0 and problem1, and fail on problem2
        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemsData[0],
            $courseData,
            $participants[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemsData[1],
            $courseData,
            $participants[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemsData[2],
            $courseData,
            $participants[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData, 0, 'WA');

        // Second student will solve problem1, fail on problem0 and won't try problem2
        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemsData[1],
            $courseData,
            $participants[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemsData[0],
            $courseData,
            $participants[1]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData, 0, 'WA');

        // Third student will solve problem2 and won't try problem0 andproblem1
        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemsData[2],
            $courseData,
            $participants[2]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $results = \OmegaUp\DAO\Courses::getStudentsProgressPerAssignment(
            $courseData['course']->course_id,
            $courseData['course']->group_id,
            1,
            100
        );

        $this->assertEquals(3, $results['totalRows']);

        $this->assertEquals(
            $participants[0]->name,
            $results['allProgress'][0]['name']
        );
        $this->assertArrayHasKey(
            $assignment,
            $results['allProgress'][0]['progress']
        );
        $this->assertEquals(
            100,
            $results['allProgress'][0]['progress'][$assignment][$problemsData[0]['problem']->alias]
        );
        $this->assertEquals(
            100,
            $results['allProgress'][0]['progress'][$assignment][$problemsData[1]['problem']->alias]
        );
        $this->assertEquals(
            0,
            $results['allProgress'][0]['progress'][$assignment][$problemsData[2]['problem']->alias]
        );

        $this->assertEquals(
            $participants[1]->name,
            $results['allProgress'][1]['name']
        );
        $this->assertArrayHasKey(
            $assignment,
            $results['allProgress'][1]['progress']
        );
        $this->assertEquals(
            0,
            $results['allProgress'][1]['progress'][$assignment][$problemsData[0]['problem']->alias]
        );
        $this->assertEquals(
            100,
            $results['allProgress'][1]['progress'][$assignment][$problemsData[1]['problem']->alias]
        );
        $this->assertEquals(
            0,
            $results['allProgress'][1]['progress'][$assignment][$problemsData[2]['problem']->alias]
        );

        $this->assertEquals(
            $participants[2]->name,
            $results['allProgress'][2]['name']
        );
        $this->assertArrayHasKey(
            $assignment,
            $results['allProgress'][2]['progress']
        );
        $this->assertEquals(
            0,
            $results['allProgress'][2]['progress'][$assignment][$problemsData[0]['problem']->alias]
        );
        $this->assertEquals(
            0,
            $results['allProgress'][2]['progress'][$assignment][$problemsData[1]['problem']->alias]
        );
        $this->assertEquals(
            100,
            $results['allProgress'][2]['progress'][$assignment][$problemsData[2]['problem']->alias]
        );
    }

    public function testGetStudentProgressForCourseWithExtraProblems() {
        // One course, with two assignments
        // A1 has 2 problems that the student will solve => score 100%
        // A2 has 2 problems, the student will solve just one => score 50%
        // Global score will be 75% (A1: 100% + A2: 50%)
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithAssignments(
            2
        );
        $assignmentAliases = $courseData['assignment_aliases'];

        $course = \OmegaUp\DAO\Courses::getByAlias($courseData['course_alias']);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        [
            'user' => $user,
            'identity' => $participant
        ] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $participant
        );

        $login = self::login($courseData['admin']);

        $problemsData = [];
        for ($i = 0; $i < 4; $i++) {
            $problemsData[] = \OmegaUp\Test\Factories\Problem::createProblem();
        }
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $course->alias,
            $assignmentAliases[0],
            [
                $problemsData[0],
                $problemsData[1],
            ]
        );

        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $course->alias,
            $assignmentAliases[1],
            [
                $problemsData[2],
                $problemsData[3],
            ]
        );

        // Student will solve problems 0, 1 (from assignment 0)...
        for ($i = 0; $i < 2; $i++) {
            $runData = \OmegaUp\Test\Factories\Run::createAssignmentRun(
                $course->alias,
                $assignmentAliases[0],
                $problemsData[$i],
                $participant
            );
            \OmegaUp\Test\Factories\Run::gradeRun($runData);
        }
        // ... and also problem 2 (from assignment 1)
        $runData = \OmegaUp\Test\Factories\Run::createAssignmentRun(
            $course->alias,
            $assignmentAliases[1],
            $problemsData[2],
            $participant
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $results = \OmegaUp\DAO\Courses::getStudentsProgressPerAssignmentv2(
            $course->course_id
        );

        $this->assertCount(2, $results);
        $this->assertEquals(1, $results[0]['order']);
        $this->assertEquals($assignmentAliases[0], $results[0]['alias']);
        $this->assertEquals(200, $results[0]['points']);
        $this->assertEquals(
            $results[0]['points'],
            $results[0]['pointsWithExtraProblems']
        );
        $this->assertCount(2, $results[0]['problems']);

        $this->assertEquals(2, $results[1]['order']);
        $this->assertEquals($assignmentAliases[1], $results[1]['alias']);
        $this->assertEquals(200, $results[1]['points']);
        $this->assertEquals(
            $results[1]['points'],
            $results[1]['pointsWithExtraProblems']
        );
        $this->assertCount(2, $results[0]['problems']);

        // One student
        // print_r($results);

        return;
    }

    public function testGetStudentsProgressForCourseWithZeroes() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $problemData['points'] = 0;

        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();
        $courseAlias = $courseData['course_alias'];
        $assignment = $courseData['assignment_alias'];

        $login = self::login($courseData['admin']);

        // assignment is going to have the first 3 problems
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseAlias,
            $assignment,
            [$problemData]
        );

        [
            'user' => $user,
            'identity' => $participant
        ] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $participant
        );

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemData,
            $courseData,
            $participant
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $results = \OmegaUp\DAO\Courses::getStudentsProgressPerAssignment(
            $courseData['course']->course_id,
            $courseData['course']->group_id,
            1,
            100
        );

        $this->assertEquals(1, $results['totalRows']);
        $this->assertEquals(
            $participant->name,
            $results['allProgress'][0]['name']
        );
        $this->assertArrayHasKey(
            $assignment,
            $results['allProgress'][0]['progress']
        );
        $this->assertEquals(
            0,
            $results['allProgress'][0]['progress'][$assignment][$problemData['problem']->alias]
        );
    }
}
