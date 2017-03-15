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
        $assignment_alias = Utils::CreateRandomString();
        $course = CourseController::apiCreateAssignment(new Request([
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => $assignment_alias,
            'description' => Utils::CreateRandomString(),
            'start_time' => (Utils::GetPhpUnixTimestamp() + 60),
            'finish_time' => (Utils::GetPhpUnixTimestamp() + 120),
            'course_alias' => $courseAlias,
            'assignment_type' => 'homework'
        ]));

        // There should exist 1 assignment with this alias
        $assignments = AssignmentsDAO::search([
            'alias' => $assignment_alias,
        ]);
        $this->assertEquals(1, count($assignments));
        $assignment = $assignments[0];

        // Add a problem to the assignment.
        $problemData = ProblemsFactory::createProblem(null, null, 1, $user, null, $login);
        $points = 1337;
        CourseController::apiAddProblem(new Request([
            'auth_token' => $login->auth_token,
            'course_alias' => $courseAlias,
            'assignment_alias' => $assignment->alias,
            'problem_alias' => $problemData['problem']->alias,
            'points' => $points,
        ]));

        $problems = ProblemsetProblemsDAO::search([
            'problemset_id' => $assignment->problemset_id,
        ]);
        $this->assertEquals(1, count($problems));
        $this->assertEquals($points, $problems[0]->points);
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

    /**
     * Try to create an assignment with inverted times.
     * @expectedException InvalidParameterException
     */
    public function testCreateAssignmentWithInvertedTimes() {
        $admin = UserFactory::createUser();
        $adminLogin = OmegaupTestCase::login($admin);
        $courseData = CoursesFactory::createCourse($admin, $adminLogin);

        CourseController::apiCreateAssignment(new Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => Utils::CreateRandomString(),
            'description' => Utils::CreateRandomString(),
            'start_time' => (Utils::GetPhpUnixTimestamp() + 120),
            'finish_time' => (Utils::GetPhpUnixTimestamp() + 60),
            'course_alias' => $courseData['course_alias'],
            'assignment_type' => 'homework'
        ]));
    }
}
