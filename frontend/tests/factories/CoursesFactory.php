<?php

class CoursesFactory {
    public static function createCourse(Users $admin = null, ScopedLoginToken $adminLogin = null, $public = false) {
        if (is_null($admin)) {
            $admin = UserFactory::createUser();
            $adminLogin = OmegaupTestCase::login($admin);
        }

        if ($public != false) {
            $curatorGroup = GroupsDAO::FindByAlias(
                Authorization::CURATOR_GROUP_ALIAS
            );

            GroupsUsersDAO::save(new GroupsUsers([
                'group_id' => $curatorGroup->group_id,
                'user_id' => $admin->user_id,
                'role_id' => Authorization::ADMIN_ROLE,
            ]));
        }

        $courseAlias = Utils::CreateRandomString();

        $r = new Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => $courseAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => (Utils::GetPhpUnixTimestamp() + 60),
            'finish_time' => (Utils::GetPhpUnixTimestamp() + 120),
            'public' => $public
        ]);

        $response = CourseController::apiCreate($r);

        return [
            'request' => $r,
            'admin' => $admin,
            'course_alias' => $courseAlias,
        ];
    }

    public static function createCourseWithOneAssignment(Users $admin = null, ScopedLoginToken $adminLogin = null) {
        if (is_null($admin)) {
            $admin = UserFactory::createUser();
            $adminLogin = OmegaupTestCase::login($admin);
        }

        // Create the course
        $courseFactoryResult = self::createCourse($admin, $adminLogin);
        $courseAlias = $courseFactoryResult['course_alias'];

        // Create the assignment
        $assignmentAlias = Utils::CreateRandomString();

        $r = new Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => $assignmentAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => Utils::GetPhpUnixTimestamp(),
            'finish_time' => (Utils::GetPhpUnixTimestamp() + 120),
            'course_alias' => $courseAlias,
            'assignment_type' => 'homework'
        ]);
        $assignmentResult = CourseController::apiCreateAssignment($r);

        return [
            'course_alias' => $courseAlias,
            'assignment_alias' => $assignmentAlias,
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
                    'start_time' => (Utils::GetPhpUnixTimestamp() - 60),
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
    public static function addStudentToCourse($courseData, $student = null) {
        if (is_null($student)) {
            $student = UserFactory::createUser();
        }

        $course = CoursesDAO::getByAlias($courseData['course_alias']);
        $group = GroupsDAO::getByPK($course->group_id);
        $adminLogin = OmegaupTestCase::login($courseData['admin']);
        GroupController::apiAddUser(new Request([
            'auth_token' => $adminLogin->auth_token,
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
                        RunsFactory::gradeRun(null /*runData*/, 0.5, 'PA', null, $runResponsePA['guid']);
                        $expectedScores[$studentUsername][$assignmentAlias] += 50;

                        if (($s + $p) % 3 == 0) {
                            // 100 pts run
                            $runResponseAC = RunController::apiCreate(new Request([
                                'auth_token' => $studentLogin->auth_token,
                                'problemset_id' => $assignment->problemset_id,
                                'problem_alias' => $problemData['request']['alias'],
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
}
