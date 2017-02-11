<?php

class CourseCreateTest extends OmegaupTestCase {
    /**
     * Create course hot path
     */
    public function testCreateSchoolCourse() {
        $user = UserFactory::createUser();

        $login = self::login($user);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => Utils::CreateRandomString(),
            'description' => Utils::CreateRandomString(),
            'start_time' => (Utils::GetPhpUnixTimestamp() + 60),
            'finish_time' => (Utils::GetPhpUnixTimestamp() + 120)
        ]);

        $response = CourseController::apiCreate($r);

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(1, count(CoursesDAO::findByName($r['name'])));
    }

    /**
     * Two courses cannot have the same alias
     *
     * @expectedException DuplicatedEntryInDatabaseException
     */
    public function testCreateCourseDuplicatedName() {
        $sameAlias = Utils::CreateRandomString();
        $sameName = Utils::CreateRandomString();

        $user = UserFactory::createUser();

        $login = self::login($user);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'name' => $sameName,
            'alias' => $sameAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => (Utils::GetPhpUnixTimestamp() + 60),
            'finish_time' => (Utils::GetPhpUnixTimestamp() + 120)
        ]);

        $response = CourseController::apiCreate($r);

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(1, count(CoursesDAO::findByName($r['name'])));

        // Create a new Course with different alias and name
        $user = UserFactory::createUser();

        $login = self::login($user);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'name' => $sameName,
            'alias' => $sameAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => (Utils::GetPhpUnixTimestamp() + 60),
            'finish_time' => (Utils::GetPhpUnixTimestamp() + 120)
        ]);

        CourseController::apiCreate($r);
    }

    public function testCreateSchoolAssignment() {
        // Create a test course
        $user = UserFactory::createUser();

        $courseAlias = Utils::CreateRandomString();

        $login = self::login($user);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => $courseAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => (Utils::GetPhpUnixTimestamp() + 60),
            'finish_time' => (Utils::GetPhpUnixTimestamp() + 120)
        ]);

        // Call api
        $course = CourseController::apiCreate($r);
        $this->assertEquals('ok', $course['status']);

        // Create a test course
        $login = self::login($user);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => Utils::CreateRandomString(),
            'description' => Utils::CreateRandomString(),
            'start_time' => (Utils::GetPhpUnixTimestamp() + 60),
            'finish_time' => (Utils::GetPhpUnixTimestamp() + 120),
            'course_alias' => $courseAlias,
            'assignment_type' => 'homework'
        ]);
        $course = CourseController::apiCreateAssignment($r);

        // There should exist 1 assignment with this alias
        $this->assertEquals(1, count(AssignmentsDAO::search(
            array('alias' => $r['alias'])
        )));
    }

    /**
     * Tests course/apiListAssignments
     */
    public function testListCourseAssignments() {
        // Create 1 course with 1 assignment
        $courseData = CoursesFactory::createCourseWithOneAssignment();

        $adminLogin = self::login($courseData['admin']);
        $response = CourseController::apiListAssignments(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias']
        ]));

        $this->assertEquals(1, count($response['assignments']));

        // Create another course with 5 assignment and list them
        $courseData = CoursesFactory::createCourseWithAssignments(5);

        $adminLogin = self::login($courseData['admin']);
        $response = CourseController::apiListAssignments(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias']
        ]));

        $this->assertEquals(5, count($response['assignments']));
    }

    public function testDuplicateAssignmentAliases() {
        $admin = UserFactory::createUser();
        $adminLogin = OmegaupTestCase::login($admin);

        $assignmentAlias = Utils::CreateRandomString();

        // Create the course number 1
        $courseFactoryResult = CoursesFactory::createCourse($admin, $adminLogin);
        $courseAlias = $courseFactoryResult['course_alias'];

        // Create the assignment number 1
        CourseController::apiCreateAssignment(new Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => $assignmentAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => (Utils::GetPhpUnixTimestamp() + 60),
            'finish_time' => (Utils::GetPhpUnixTimestamp() + 120),
            'course_alias' => $courseAlias,
            'assignment_type' => 'homework'
        ]));

        // Create the course number 2
        $courseFactoryResult = CoursesFactory::createCourse($admin, $adminLogin);
        $courseAlias = $courseFactoryResult['course_alias'];

        // Create the assignment number 2
        CourseController::apiCreateAssignment(new Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => $assignmentAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => (Utils::GetPhpUnixTimestamp() + 60),
            'finish_time' => (Utils::GetPhpUnixTimestamp() + 120),
            'course_alias' => $courseAlias,
            'assignment_type' => 'homework'
        ]));
    }
}
