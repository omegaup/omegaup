<?php

class CoursesFactory {
    public static function createCourse(Users $admin = null, ScopedLoginToken $adminLogin = null) {
        if (is_null($admin)) {
            $admin = UserFactory::createUser();
            $adminLogin = OmegaupTestCase::login($admin);
        }

        $courseAlias = Utils::CreateRandomString();

        $r = new Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => $courseAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => (Utils::GetPhpUnixTimestamp() + 60),
            'finish_time' => (Utils::GetPhpUnixTimestamp() + 120)
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
}
