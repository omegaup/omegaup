<?php

class CoursesFactory {
    public static function createCourse(
        \OmegaUp\DAO\VO\Identities $admin = null,
        ScopedLoginToken $adminLogin = null,
        $public = false,
        $requestsUserInformation = 'no',
        $showScoreboard = 'false'
    ) {
        if (is_null($admin)) {
            $admin = UserFactory::createUser();
            $adminLogin = OmegaupTestCase::login($admin);
        }
        if ($public != false) {
            $curatorGroup = \OmegaUp\DAO\Groups::findByAlias(
                \OmegaUp\Authorization::COURSE_CURATOR_GROUP_ALIAS
            );

            \OmegaUp\DAO\GroupsIdentities::create(new \OmegaUp\DAO\VO\GroupsIdentities([
                'group_id' => $curatorGroup->group_id,
                'identity_id' => $admin->identity_id,
            ]));
        }

        $courseAlias = Utils::CreateRandomString();

        $r = new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => $courseAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => (\OmegaUp\Time::get()),
            'finish_time' => (\OmegaUp\Time::get() + 120),
            'public' => $public,
            'requests_user_information' => $requestsUserInformation,
            'show_scoreboard' => $showScoreboard,
        ]);

        $response = \OmegaUp\Controllers\Course::apiCreate($r);

        return [
            'request' => $r,
            'admin' => $admin,
            'course_alias' => $courseAlias,
        ];
    }

    public static function createCourseWithOneAssignment(
        \OmegaUp\DAO\VO\Identities $admin = null,
        ScopedLoginToken $adminLogin = null,
        $public = false,
        $requestsUserInformation = 'no',
        $showScoreboard = 'false',
        $startTimeDelay = 0
    ) {
        if (is_null($admin)) {
            $admin = UserFactory::createUser();
            $adminLogin = OmegaupTestCase::login($admin);
        }

        // Create the course
        $courseFactoryResult = self::createCourse($admin, $adminLogin, $public, $requestsUserInformation, $showScoreboard);
        $courseAlias = $courseFactoryResult['course_alias'];

        // Create the assignment
        $assignmentAlias = Utils::CreateRandomString();
        $course = \OmegaUp\DAO\Courses::getByAlias($courseAlias);

        $r = new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => $assignmentAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => \OmegaUp\Time::get() + $startTimeDelay,
            'finish_time' => \OmegaUp\Time::get() + 120,
            'course_alias' => $courseAlias,
            'assignment_type' => 'homework',
            'course' => $course,
        ]);
        $assignmentResult = \OmegaUp\Controllers\Course::apiCreateAssignment($r);
        $assignment = \OmegaUp\DAO\Assignments::getByAliasAndCourse($assignmentAlias, $course->course_id);
        return [
            'course' => $course,
            'course_alias' => $courseAlias,
            'assignment_alias' => $assignmentAlias,
            'problemset_id' => $assignment->problemset_id,
            'assignment' => $assignment,
            'request' => $r,
            'admin' => $admin
        ];
    }

    public static function createCourseWithAssignments($nAssignments) {
        return self::createCourseWithNAssignmentsPerType([
            'homework' => $nAssignments
        ]);
    }

    public static function createCourseWithNAssignmentsPerType($assignmentsPerType) {
        $courseFactoryResult = self::createCourse();
        $courseAlias = $courseFactoryResult['course_alias'];
        $admin = $courseFactoryResult['admin'];
        $adminLogin = OmegaupTestCase::login($admin);
        $assignmentAlias = [];

        foreach ($assignmentsPerType as $assignmentType => $count) {
            for ($i = 0; $i < $count; $i++) {
                $r = new \OmegaUp\Request([
                    'auth_token' => $adminLogin->auth_token,
                    'name' => Utils::CreateRandomString(),
                    'alias' => Utils::CreateRandomString(),
                    'description' => Utils::CreateRandomString(),
                    'start_time' => (\OmegaUp\Time::get()),
                    'finish_time' => (\OmegaUp\Time::get() + 120),
                    'course_alias' => $courseAlias,
                    'assignment_type' => $assignmentType
                ]);

                $assignmentAlias[] = $r['alias'];
                \OmegaUp\Controllers\Course::apiCreateAssignment($r);
            }
        }

        return [
            'admin' => $admin,
            'course_alias' => $courseAlias,
            'assignment_aliases' => $assignmentAlias
        ];
    }

    /**
     * Add a Student to a course
     * @param Array $courseData [from self::createCourse]
     * @param \OmegaUp\DAO\VO\Users $student
     */
    public static function addStudentToCourse($courseData, $student = null, ?ScopedLoginToken $login = null) {
        if (is_null($student)) {
            $student = UserFactory::createUser();
        }

        $course = \OmegaUp\DAO\Courses::getByAlias($courseData['course_alias']);

        if (is_null($course) || is_null($course->group_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }
        $group = \OmegaUp\DAO\Groups::getByPK($course->group_id);
        if (is_null($login)) {
            $login = OmegaupTestCase::login($courseData['admin']);
        }
        \OmegaUp\Controllers\Group::apiAddUser(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $student->username,
            'group_alias' => $group->alias
        ]));

        return $student;
    }

    public static function addProblemsToAssignment(ScopedLoginToken $login, $courseAlias, $assignmentAlias, $problems) {
        $responses = [];
        foreach ($problems as $problem) {
            // Add a problem to the assignment
            $responses[] = \OmegaUp\Controllers\Course::apiAddProblem(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'course_alias' => $courseAlias,
                'assignment_alias' => $assignmentAlias,
                'problem_alias' => $problem['problem']->alias,
            ]));
        }

        return $responses;
    }

    /**
     * @param array{course_alias: string} $courseData
     * @param \OmegaUp\DAO\VO\Identities[] $students
     * @param string[] $assignmentAliases
     * @param array $problemAssignmentsMap
     * @return array
     * @psalm-return array<string|null, array<string, int>>
     */
    public static function submitRunsToAssignmentsInCourse(
        array $courseData,
        array $students,
        array $assignmentAliases,
        array $problemAssignmentsMap
    ) {
        $course = \OmegaUp\DAO\Courses::getByAlias($courseData['course_alias']);
        $expectedScores = [];
        if (is_null($course) || is_null($course->course_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
        }
        for ($s = 0; $s < count($students); $s++) {
            if (is_null($students[$s]->username)) {
                throw new \OmegaUp\Exceptions\NotFoundException('courseNotFound');
            }
            $studentUsername = $students[$s]->username;
            $expectedScores[$studentUsername] = [];
            $studentLogin = OmegaupTestCase::login($students[$s]);

            // Loop through all problems inside assignments created
            $p = 0;
            foreach ($assignmentAliases as $assignmentAlias) {
                $assignment = \OmegaUp\DAO\Assignments::getByAliasAndCourse(
                    $assignmentAlias,
                    $course->course_id
                );

                $expectedScores[$studentUsername][$assignmentAlias] = 0;

                foreach ($problemAssignmentsMap[$assignmentAlias] as $problemData) {
                    $p++;
                    if ($s % 2 == $p % 2) {
                        // PA run
                        $runResponsePA = \OmegaUp\Controllers\Run::apiCreate(new \OmegaUp\Request([
                            'auth_token' => $studentLogin->auth_token,
                            'problemset_id' => $assignment->problemset_id,
                            'problem_alias' => $problemData['request']['problem_alias'],
                            'language' => 'c',
                            'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
                        ]));
                        RunsFactory::gradeRun(null /*runData*/, 0.5, 'PA', null, $runResponsePA['guid']);
                        $expectedScores[$studentUsername][$assignmentAlias] += 50;

                        if (($s + $p) % 3 == 0) {
                            // 100 pts run
                            $runResponseAC = \OmegaUp\Controllers\Run::apiCreate(new \OmegaUp\Request([
                                'auth_token' => $studentLogin->auth_token,
                                'problemset_id' => $assignment->problemset_id,
                                'problem_alias' => $problemData['request']['problem_alias'],
                                'language' => 'c',
                                'source' => "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }",
                            ]));
                            RunsFactory::gradeRun(null /*runData*/, 1, 'AC', null, $runResponseAC['guid']);
                            $expectedScores[$studentUsername][$assignmentAlias] += 50;
                        }
                    }
                }
            }
        }

        return $expectedScores;
    }

    public static function openCourse($courseAssignmentData, $user) {
        // Log in as course adminy
        $login = OmegaupTestCase::login($user);

        // Call api
        \OmegaUp\Controllers\Course::apiIntroDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'course_alias' => $courseAssignmentData['request']['course_alias'],
        ]));
    }

    public static function openAssignmentCourse($courseAssignmentData, $user) {
        // Log in as course adminy
        $login = OmegaupTestCase::login($user);

        // Call api
        \OmegaUp\Controllers\Course::apiIntroDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'course_alias' => $courseAssignmentData['request']['course_alias'],
            'assignment_alias' => $courseAssignmentData['request']['assignment_alias'],
        ]));
    }

    public static function openProblemInCourseAssignment($courseAssignmentData, $problemData, $user) {
        // Log in the user
        $login = OmegaupTestCase::login($user);

        // Call api
        \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
            'course_alias' => $courseAssignmentData['request']['course_alias'],
            'assignment_alias' => $courseAssignmentData['request']['assignment_alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
            'auth_token' => $login->auth_token,
        ]));
    }
}
