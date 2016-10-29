<?php

class CoursesFactory {
    public static function createCourse(Users $user = null) {
        if (is_null($user)) {
            $user = UserFactory::createUser();
        }

        $courseAlias = Utils::CreateRandomString();

        $r = new Request(array(
            'auth_token' => OmegaupTestCase::login($user),
            'name' => Utils::CreateRandomString(),
            'alias' => $courseAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => (Utils::GetPhpUnixTimestamp() + 60),
            'finish_time' => (Utils::GetPhpUnixTimestamp() + 120)
        ));

        $response = CourseController::apiCreate($r);

        return array(
            'request' => $r,
            'user' => $user,
            'course_alias' => $courseAlias,
        );
    }

    public static function createCourseWithOneAssignment(Users $user = null) {
        if (is_null($user)) {
            $user = UserFactory::createUser();
        }

        // Create the course
        $courseFactoryResult = self::createCourse($user);
        $courseAlias = $courseFactoryResult['course_alias'];

        // Create the assignment
        $assignmentAlias = Utils::CreateRandomString();

        $r = new Request(array(
            'auth_token' => OmegaupTestCase::login($user),
            'name' => Utils::CreateRandomString(),
            'alias' => $assignmentAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => (Utils::GetPhpUnixTimestamp() + 60),
            'finish_time' => (Utils::GetPhpUnixTimestamp() + 120),
            'course_alias' => $courseAlias,
            'assignment_type' => 'homework'
        ));
        $assignmentResult = CourseController::apiCreateAssignment($r);

        return array(
            'course_alias' => $courseAlias,
            'assignment_alias' => $assignmentAlias,
            'request' => $r,
            'user' => $user
        );
    }

    public static function createCourseWithAssignments($nAssignments) {
        return self::createCourseWithNAssignmentsPerType(array(
            'homework' => $nAssignments
        ));
    }

    public static function createCourseWithNAssignmentsPerType($assignmentsPerType) {
        $courseFactoryResult = self::createCourse();
        $courseAlias = $courseFactoryResult['course_alias'];
        $user = $courseFactoryResult['user'];

        foreach ($assignmentsPerType as $assignmentType => $count) {
            for ($i = 0; $i < $count; $i++) {
                $r = new Request(array(
                    'auth_token' => OmegaupTestCase::login($user),
                    'name' => Utils::CreateRandomString(),
                    'alias' => Utils::CreateRandomString(),
                    'description' => Utils::CreateRandomString(),
                    'start_time' => (Utils::GetPhpUnixTimestamp() + 60),
                    'finish_time' => (Utils::GetPhpUnixTimestamp() + 120),
                    'course_alias' => $courseAlias,
                    'assignment_type' => $assignmentType
                ));

                CourseController::apiCreateAssignment($r);
            }
        }

        return array(
            'user' => $user,
            'course_alias' => $courseAlias
        );
    }

    /**
     * Add a Student to a course
     * @param Array $courseData [from self::createCourse]
     * @param Users $student
     */
    public static function addStudentToCourse($courseData, $student = null) {
        // TODO(pablo & joe): Fix this when course and groups are related by an id.
        if (is_null($student)) {
            $student = UserFactory::createUser();
        }

        GroupController::apiAddUser(new Request(array(
            'auth_token' => OmegaupTestCase::login($courseData['user']),
            'usernameOrEmail' => $student->username,
            'group_alias' => $courseData['course_alias']
        )));

        return $student;
    }
}
