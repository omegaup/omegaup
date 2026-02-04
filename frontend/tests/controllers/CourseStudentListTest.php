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
     * List can only be retrieved by an admin
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
            $this->assertSame('userNotAllowed', $e->getMessage());
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
            $this->assertSame('courseNotFound', $e->getMessage());
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

        $this->assertSame(3, $results['totalRows']);
        $this->assertSame(
            $results['totalRows'],
            count(
                $results['studentsProgress']
            )
        );

        $this->assertSame(
            $participants[0]->name,
            $results['studentsProgress'][0]['name']
        );
        $this->assertArrayHasKey(
            $assignment,
            $results['studentsProgress'][0]['assignments']
        );
        $this->assertSame(
            66,
            intval(
                $results['studentsProgress'][0]['assignments'][$assignment]['progress']
            )
        );
        $this->assertSame(
            100.0,
            $results['studentsProgress'][0]['assignments'][$assignment]['problems'][$problemsData[0]['problem']->alias]['score']
        );
        $this->assertSame(
            100.0,
            $results['studentsProgress'][0]['assignments'][$assignment]['problems'][$problemsData[1]['problem']->alias]['score']
        );
        $this->assertSame(
            0.0,
            $results['studentsProgress'][0]['assignments'][$assignment]['problems'][$problemsData[2]['problem']->alias]['score']
        );

        $this->assertSame(
            $participants[1]->name,
            $results['studentsProgress'][1]['name']
        );
        $this->assertArrayHasKey(
            $assignment,
            $results['studentsProgress'][1]['assignments']
        );
        $this->assertSame(
            33,
            intval(
                $results['studentsProgress'][1]['assignments'][$assignment]['progress']
            )
        );
        $this->assertSame(
            0.0,
            $results['studentsProgress'][1]['assignments'][$assignment]['problems'][$problemsData[0]['problem']->alias]['score']
        );
        $this->assertSame(
            100.0,
            $results['studentsProgress'][1]['assignments'][$assignment]['problems'][$problemsData[1]['problem']->alias]['score']
        );
        $this->assertArrayNotHasKey(
            $problemsData[2]['problem']->alias,
            $results['studentsProgress'][1]['assignments'][$assignment]['problems']
        );

        $this->assertSame(
            $participants[2]->name,
            $results['studentsProgress'][2]['name']
        );
        $this->assertArrayHasKey(
            $assignment,
            $results['studentsProgress'][2]['assignments']
        );
        $this->assertSame(
            33,
            intval(
                $results['studentsProgress'][2]['assignments'][$assignment]['progress']
            )
        );
        $this->assertArrayNotHasKey(
            $problemsData[0]['problem']->alias,
            $results['studentsProgress'][2]['assignments'][$assignment]['problems']
        );
        $this->assertArrayNotHasKey(
            $problemsData[1]['problem']->alias,
            $results['studentsProgress'][2]['assignments'][$assignment]['problems']
        );
        $this->assertSame(
            100.0,
            $results['studentsProgress'][2]['assignments'][$assignment]['problems'][$problemsData[2]['problem']->alias]['score']
        );
    }

    public function testApiStudentsProgress() {
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

        $login = self::login($courseData['admin']);

        $results = \OmegaUp\Controllers\Course::apiStudentsProgress(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'course' => $courseData['course']->alias,
                'page' => 1,
                'length' => 100,
            ])
        );

        $this->assertSame(
            3,
            count(
                $results['progress']
            )
        );

        $this->assertSame(
            $participants[0]->name,
            $results['progress'][0]['name']
        );
        $this->assertArrayHasKey(
            $assignment,
            $results['progress'][0]['assignments']
        );
        $this->assertSame(
            66,
            intval(
                $results['progress'][0]['assignments'][$assignment]['progress']
            )
        );
        $this->assertSame(
            100.0,
            $results['progress'][0]['assignments'][$assignment]['problems'][$problemsData[0]['problem']->alias]['score']
        );
        $this->assertSame(
            100.0,
            $results['progress'][0]['assignments'][$assignment]['problems'][$problemsData[1]['problem']->alias]['score']
        );
        $this->assertSame(
            0.0,
            $results['progress'][0]['assignments'][$assignment]['problems'][$problemsData[2]['problem']->alias]['score']
        );

        $this->assertSame(
            $participants[1]->name,
            $results['progress'][1]['name']
        );
        $this->assertArrayHasKey(
            $assignment,
            $results['progress'][1]['assignments']
        );
        $this->assertSame(
            33,
            intval(
                $results['progress'][1]['assignments'][$assignment]['progress']
            )
        );
        $this->assertSame(
            0.0,
            $results['progress'][1]['assignments'][$assignment]['problems'][$problemsData[0]['problem']->alias]['score']
        );
        $this->assertSame(
            100.0,
            $results['progress'][1]['assignments'][$assignment]['problems'][$problemsData[1]['problem']->alias]['score']
        );
        $this->assertArrayNotHasKey(
            $problemsData[2]['problem']->alias,
            $results['progress'][1]['assignments'][$assignment]['problems']
        );

        $this->assertSame(
            $participants[2]->name,
            $results['progress'][2]['name']
        );
        $this->assertArrayHasKey(
            $assignment,
            $results['progress'][2]['assignments']
        );
        $this->assertSame(
            33,
            intval(
                $results['progress'][2]['assignments'][$assignment]['progress']
            )
        );
        $this->assertArrayNotHasKey(
            $problemsData[0]['problem']->alias,
            $results['progress'][2]['assignments'][$assignment]['problems']
        );
        $this->assertArrayNotHasKey(
            $problemsData[1]['problem']->alias,
            $results['progress'][2]['assignments'][$assignment]['problems']
        );
        $this->assertSame(
            100.0,
            $results['progress'][2]['assignments'][$assignment]['problems'][$problemsData[2]['problem']->alias]['score']
        );
    }

    public function testGetStudentProgressForCourseWithExtraProblems() {
        // One course, with three assignments
        // A1 has 2 problems that the student will solve => score 100%
        // A2 has 2 problems, the student will solve just one => score 50%
        // A3 is a lesson, it should not be counted
        // Global score will be 75% (A1: 100% + A2: 50%)
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithAssignments(
            3
        );
        $assignmentAliases = $courseData['assignment_aliases'];

        $course = \OmegaUp\DAO\Courses::getByAlias($courseData['course_alias']);
        if (is_null($course)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }

        // A3 should be a lesson
        $lessonAssignment = \OmegaUp\DAO\Assignments::getByAliasAndCourse(
            $assignmentAliases[2],
            $course->course_id
        );
        $lessonAssignment->assignment_type = 'lesson';
        \OmegaUp\DAO\Assignments::update($lessonAssignment);

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

        $results = \OmegaUp\DAO\Courses::getStudentsProgressPerAssignment(
            $course->course_id,
            $course->group_id,
            1,
            100
        );

        // First test assignmentsProblems info
        $this->assertCount(2, $results['assignmentsProblems']);
        $this->assertSame(1, $results['assignmentsProblems'][0]['order']);
        $this->assertSame(
            $assignmentAliases[0],
            $results['assignmentsProblems'][0]['alias']
        );
        $this->assertSame(200.0, $results['assignmentsProblems'][0]['points']);
        $this->assertCount(2, $results['assignmentsProblems'][0]['problems']);

        $this->assertSame(2, $results['assignmentsProblems'][1]['order']);
        $this->assertSame(
            $assignmentAliases[1],
            $results['assignmentsProblems'][1]['alias']
        );
        $this->assertSame(200.0, $results['assignmentsProblems'][1]['points']);
        $this->assertCount(2, $results['assignmentsProblems'][0]['problems']);

        // Then test studentsProgress info
        $this->assertCount(1, $results['studentsProgress']);

        $this->assertSame(
            $participant->username,
            $results['studentsProgress'][0]['username']
        );
        $this->assertSame(
            300.0,
            $results['studentsProgress'][0]['courseScore']
        ); // 3 problems solved
        $this->assertSame(
            75.0,
            $results['studentsProgress'][0]['courseProgress']
        );

        $this->assertSame(
            200.0,
            $results['studentsProgress'][0]['assignments'][$assignmentAliases[0]]['score']
        );
        $this->assertSame(
            100.0,
            $results['studentsProgress'][0]['assignments'][$assignmentAliases[0]]['progress']
        );

        $this->assertSame(
            100.0,
            $results['studentsProgress'][0]['assignments'][$assignmentAliases[1]]['score']
        );
        $this->assertSame(
            50.0,
            $results['studentsProgress'][0]['assignments'][$assignmentAliases[1]]['progress']
        );

        // Now add two extra problems and make the user solve them
        // Only the global score should be less than the 100%.
        for ($i = 0; $i < 2; $i++) {
            $problemsData[] = \OmegaUp\Test\Factories\Problem::createProblem();
        }
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $course->alias,
            $assignmentAliases[0],
            [ $problemsData[4], $problemsData[5] ],
            extraProblems: true
        );

        $runData = \OmegaUp\Test\Factories\Run::createAssignmentRun(
            $course->alias,
            $assignmentAliases[0],
            $problemsData[4],
            $participant
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $runData = \OmegaUp\Test\Factories\Run::createAssignmentRun(
            $course->alias,
            $assignmentAliases[0],
            $problemsData[5],
            $participant
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $resultsWithExtraProblem = \OmegaUp\DAO\Courses::getStudentsProgressPerAssignment(
            $course->course_id,
            $course->group_id,
            1,
            100
        );

        // First test assignmentsProblems info
        $this->assertSame(
            count($results['assignmentsProblems']),
            count($resultsWithExtraProblem['assignmentsProblems'])
        );
        $this->assertSame(
            $results['assignmentsProblems'][0]['order'],
            $resultsWithExtraProblem['assignmentsProblems'][0]['order']
        );
        $this->assertSame(
            $results['assignmentsProblems'][0]['alias'],
            $resultsWithExtraProblem['assignmentsProblems'][0]['alias']
        );
        $this->assertSame(
            $results['assignmentsProblems'][0]['points'],
            $resultsWithExtraProblem['assignmentsProblems'][0]['points']
        );
        $this->assertSame(
            200.0, // 2 extra problems
            $resultsWithExtraProblem['assignmentsProblems'][0]['points']
        );
        $this->assertCount(
            count($results['assignmentsProblems'][0]['problems']) + 2,
            $resultsWithExtraProblem['assignmentsProblems'][0]['problems']
        );

        $this->assertSame(
            $results['assignmentsProblems'][1]['order'],
            $resultsWithExtraProblem['assignmentsProblems'][1]['order']
        );
        $this->assertSame(
            $results['assignmentsProblems'][1]['alias'],
            $resultsWithExtraProblem['assignmentsProblems'][1]['alias']
        );
        $this->assertSame(
            $results['assignmentsProblems'][1]['points'],
            $resultsWithExtraProblem['assignmentsProblems'][1]['points']
        );
        $this->assertSame(
            count($results['assignmentsProblems'][1]['problems']),
            count(
                $resultsWithExtraProblem['assignmentsProblems'][1]['problems']
            )
        );

        // Then test studentsProgress info
        $this->assertSame(
            count($results['studentsProgress']),
            count($resultsWithExtraProblem['studentsProgress'])
        );
        $this->assertSame(
            $results['studentsProgress'][0]['username'],
            $resultsWithExtraProblem['studentsProgress'][0]['username']
        );
        $this->assertSame(
            $results['studentsProgress'][0]['courseScore'] + 200,
            $resultsWithExtraProblem['studentsProgress'][0]['courseScore']
        );
        $this->assertSame(
            100, // 100% (3 problems + 1 extra problem / 4 problems)
            $resultsWithExtraProblem['studentsProgress'][0]['courseProgress']
        );

        $this->assertSame(
            $results['studentsProgress'][0]['assignments'][$assignmentAliases[0]]['score'] + 200, // 2 extra problems solved
            $resultsWithExtraProblem['studentsProgress'][0]['assignments'][$assignmentAliases[0]]['score']
        );
        $this->assertSame(
            $results['studentsProgress'][0]['assignments'][$assignmentAliases[0]]['progress'] + 100,
            $resultsWithExtraProblem['studentsProgress'][0]['assignments'][$assignmentAliases[0]]['progress']
        );

        $this->assertSame(
            $results['studentsProgress'][0]['assignments'][$assignmentAliases[1]]['score'],
            $resultsWithExtraProblem['studentsProgress'][0]['assignments'][$assignmentAliases[1]]['score']
        );
        $this->assertSame(
            $results['studentsProgress'][0]['assignments'][$assignmentAliases[1]]['progress'],
            $resultsWithExtraProblem['studentsProgress'][0]['assignments'][$assignmentAliases[1]]['progress']
        );
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
        ] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams([
                'username' => 'userA',
                'name' => 'userA',
            ])
        );

        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $participant
        );

        // Add extra student to course who will have
        // 0 course progress and score.
        [
            'user' => $extraUser,
            'identity' => $extraParticipant
        ] = \OmegaUp\Test\Factories\User::createUser(
            new \OmegaUp\Test\Factories\UserParams([
                'username' => 'userB',
                'name' => 'userB',
            ])
        );

        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $extraParticipant
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

        $this->assertSame(2, $results['totalRows']);
        $this->assertSame(
            $results['totalRows'],
            count(
                $results['studentsProgress']
            )
        );

        $this->assertSame(
            $participant->name,
            $results['studentsProgress'][0]['name']
        );
        $this->assertArrayHasKey(
            $assignment,
            $results['studentsProgress'][0]['assignments']
        );
        $this->assertSame(
            100.0,
            $results['studentsProgress'][0]['assignments'][$assignment]['problems'][$problemData['problem']->alias]['score']
        );

        $this->assertSame(
            $extraParticipant->name,
            $results['studentsProgress'][1]['name']
        );
        $this->assertSame(
            0.0,
            $results['studentsProgress'][1]['courseScore']
        );
        $this->assertSame(
            0.0,
            $results['studentsProgress'][1]['courseProgress']
        );
    }
}
