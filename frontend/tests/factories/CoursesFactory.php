<?php

class CoursesFactory {
    public static function createCourse(
        Users $admin = null,
        ScopedLoginToken $adminLogin = null,
        $public = false,
        $requestsUserInformation = 'no',
        $showScoreboard = 'false'
    ) {
        if (is_null($admin)) {
            $admin = UserFactory::createUser();
            $adminLogin = OmegaupTestCase::login($admin);
        }
        $identity = IdentitiesDAO::getByPK($admin->main_identity_id);
        if ($public != false) {
            $curatorGroup = GroupsDAO::findByAlias(
                Authorization::COURSE_CURATOR_GROUP_ALIAS
            );

            GroupsIdentitiesDAO::save(new GroupsIdentities([
                'group_id' => $curatorGroup->group_id,
                'identity_id' => $identity->identity_id,
                'role_id' => Authorization::ADMIN_ROLE,
            ]));
        }

        $courseAlias = Utils::CreateRandomString();

        $r = new Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => $courseAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => (Utils::GetPhpUnixTimestamp()),
            'finish_time' => (Utils::GetPhpUnixTimestamp() + 120),
            'public' => $public,
            'requests_user_information' => $requestsUserInformation,
            'show_scoreboard' => $showScoreboard,
        ]);

        $response = CourseController::apiCreate($r);

        return [
            'request' => $r,
            'admin' => $admin,
            'course_alias' => $courseAlias,
        ];
    }

    public static function createCourseWithOneAssignment(
        Users $admin = null,
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
        $course = CoursesDAO::getByAlias($courseAlias);

        $r = new Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => $assignmentAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => Utils::GetPhpUnixTimestamp() + $startTimeDelay,
            'finish_time' => Utils::GetPhpUnixTimestamp() + 120,
            'course_alias' => $courseAlias,
            'assignment_type' => 'homework',
            'course' => $course,
        ]);
        $assignmentResult = CourseController::apiCreateAssignment($r);
        $assignment = AssignmentsDAO::getByAliasAndCourse($assignmentAlias, $course->course_id);
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
                $r = new Request([
                    'auth_token' => $adminLogin->auth_token,
                    'name' => Utils::CreateRandomString(),
                    'alias' => Utils::CreateRandomString(),
                    'description' => Utils::CreateRandomString(),
                    'start_time' => (Utils::GetPhpUnixTimestamp()),
                    'finish_time' => (Utils::GetPhpUnixTimestamp() + 120),
                    'course_alias' => $courseAlias,
                    'assignment_type' => $assignmentType
                ]);

                $assignmentAlias[] = $r['alias'];
                CourseController::apiCreateAssignment($r);
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
     * @param Users $student
     */
    public static function addStudentToCourse($courseData, $student = null, ?ScopedLoginToken $login = null) {
        if (is_null($student)) {
            $student = UserFactory::createUser();
        }

        $course = CoursesDAO::getByAlias($courseData['course_alias']);
        $group = GroupsDAO::getByPK($course->group_id);
        if (is_null($login)) {
            $login = OmegaupTestCase::login($courseData['admin']);
        }
        GroupController::apiAddUser(new Request([
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
            $responses[] = CourseController::apiAddProblem(new Request([
                'auth_token' => $login->auth_token,
                'course_alias' => $courseAlias,
                'assignment_alias' => $assignmentAlias,
                'problem_alias' => $problem['problem']->alias,
            ]));
        }

        return $responses;
    }

    public static function submitRunsToAssignmentsInCourse(
        $courseData,
        array $students,
        array $assignmentAliases,
        array $problemAssignmentsMap
    ) {
        $course = CoursesDAO::getByAlias($courseData['course_alias']);
        $expectedScores = [];
        for ($s = 0; $s < count($students); $s++) {
            $studentUsername = $students[$s]->username;
            $expectedScores[$studentUsername] = [];
            $studentLogin = OmegaupTestCase::login($students[$s]);

            // Loop through all problems inside assignments created
            $p = 0;
            foreach ($assignmentAliases as $assignmentAlias) {
                $assignment = AssignmentsDAO::getByAliasAndCourse(
                    $assignmentAlias,
                    $course->course_id
                );

                $expectedScores[$studentUsername][$assignmentAlias] = 0;

                foreach ($problemAssignmentsMap[$assignmentAlias] as $problemData) {
                    $p++;
                    if ($s % 2 == $p % 2) {
                        // PA run
                        $runResponsePA = RunController::apiCreate(new Request([
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
                            $runResponseAC = RunController::apiCreate(new Request([
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
        CourseController::apiIntroDetails(new Request([
            'auth_token' => $login->auth_token,
            'course_alias' => $courseAssignmentData['request']['course_alias'],
        ]));
    }

    public static function openAssignmentCourse($courseAssignmentData, $user) {
        // Log in as course adminy
        $login = OmegaupTestCase::login($user);

        // Call api
        CourseController::apiIntroDetails(new Request([
            'auth_token' => $login->auth_token,
            'course_alias' => $courseAssignmentData['request']['course_alias'],
            'assignment_alias' => $courseAssignmentData['request']['assignment_alias'],
        ]));
    }

    public static function openProblemInCourseAssignment($courseAssignmentData, $problemData, $user) {
        // Log in the user
        $login = OmegaupTestCase::login($user);

        // Call api
        ProblemController::apiDetails(new Request([
            'course_alias' => $courseAssignmentData['request']['course_alias'],
            'assignment_alias' => $courseAssignmentData['request']['assignment_alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
            'auth_token' => $login->auth_token,
        ]));
    }
}
