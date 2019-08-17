<?php

class CourseCreateTest extends OmegaupTestCase {
    private static $curator = null;

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();

        $curatorGroup = GroupsDAO::findByAlias(
            Authorization::COURSE_CURATOR_GROUP_ALIAS
        );

        self::$curator = UserFactory::createUser();
        $identity = IdentitiesDAO::getByPK(self::$curator->main_identity_id);
        GroupsIdentitiesDAO::create(new \OmegaUp\DAO\VO\GroupsIdentities([
            'group_id' => $curatorGroup->group_id,
            'identity_id' => $identity->identity_id,
        ]));
    }

    /**
     * Create course hot path
     */
    public function testCreateSchoolCourse() {
        $user = UserFactory::createUser();

        $login = self::login($user);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => Utils::CreateRandomString(),
            'description' => Utils::CreateRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120)
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
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => $sameName,
            'alias' => $sameAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120)
        ]);

        $response = CourseController::apiCreate($r);

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(1, count(CoursesDAO::findByName($r['name'])));

        // Create a new Course with different alias and name
        $user = UserFactory::createUser();

        $login = self::login($user);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => $sameName,
            'alias' => $sameAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120)
        ]);

        CourseController::apiCreate($r);
    }

    public function testCreateSchoolAssignment() {
        // Create a test course
        $user = UserFactory::createUser();

        $courseAlias = Utils::CreateRandomString();

        $login = self::login($user);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => $courseAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120)
        ]);

        // Call api
        $response = CourseController::apiCreate($r);
        $this->assertEquals('ok', $response['status']);

        // Create a test course
        $login = self::login($user);
        $assignmentAlias = Utils::CreateRandomString();
        $response = CourseController::apiCreateAssignment(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => $assignmentAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120),
            'course_alias' => $courseAlias,
            'assignment_type' => 'homework'
        ]));
        $this->assertEquals('ok', $response['status']);

        // There should exist 1 assignment with this alias
        $course = CoursesDAO::getByAlias($courseAlias);
        $assignment = AssignmentsDAO::getByAliasAndCourse($assignmentAlias, $course->course_id);
        $this->assertNotNull($assignment);

        // Add a problem to the assignment.
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => 1,
            'user' => $user
        ]), $login);
        $points = 1337;
        CourseController::apiAddProblem(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'course_alias' => $courseAlias,
            'assignment_alias' => $assignment->alias,
            'problem_alias' => $problemData['problem']->alias,
            'points' => $points,
        ]));

        $problems = ProblemsetProblemsDAO::getByProblemset($assignment->problemset_id);
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
        $response = CourseController::apiListAssignments(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias']
        ]));

        $this->assertEquals(1, count($response['assignments']));

        // Create another course with 5 assignment and list them
        $courseData = CoursesFactory::createCourseWithAssignments(5);

        $adminLogin = self::login($courseData['admin']);
        $response = CourseController::apiListAssignments(new \OmegaUp\Request([
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
        CourseController::apiCreateAssignment(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => $assignmentAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120),
            'course_alias' => $courseAlias,
            'assignment_type' => 'homework'
        ]));

        // Create the course number 2
        $courseFactoryResult = CoursesFactory::createCourse($admin, $adminLogin);
        $courseAlias = $courseFactoryResult['course_alias'];

        // Create the assignment number 2
        CourseController::apiCreateAssignment(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => $assignmentAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120),
            'course_alias' => $courseAlias,
            'assignment_type' => 'homework'
        ]));
    }

    /**
     * Try to create an assignment with inverted times.
     * @expectedException \OmegaUp\Exceptions\InvalidParameterException
     */
    public function testCreateAssignmentWithInvertedTimes() {
        $admin = UserFactory::createUser();
        $adminLogin = OmegaupTestCase::login($admin);
        $courseData = CoursesFactory::createCourse($admin, $adminLogin);

        CourseController::apiCreateAssignment(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => Utils::CreateRandomString(),
            'description' => Utils::CreateRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 120),
            'finish_time' => (\OmegaUp\Time::get() + 60),
            'course_alias' => $courseData['course_alias'],
            'assignment_type' => 'homework'
        ]));
    }

    /**
     * Public course can't be created by default
     * @expectedException ForbiddenAccessException
     */
    public function testCreatePublicCourseFailForNonCurator() {
        $user = UserFactory::createUser();

        $login = self::login($user);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => Utils::CreateRandomString(),
            'description' => Utils::CreateRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120),
            'public' => 1,
        ]);

        $response = CourseController::apiCreate($r);
    }

    /**
     * Only curators can make Public courses
     */
    public function testCreatePublicCourse() {
        $login = self::login(self::$curator);
        $school = SchoolsFactory::createSchool()['school'];
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => Utils::CreateRandomString(),
            'description' => Utils::CreateRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120),
            'school_id' => $school->school_id,
            'public' => 1,
        ]);

        $response = CourseController::apiCreate($r);

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(1, count(CoursesDAO::findByName($r['name'])));
        $this->assertEquals($school->name, CourseController::apiDetails($r)['school_name']);
    }

    /**
     * Test updating show_scoreboard attribute in the Course object
     */
    public function testUpdateCourseShowScoreboard() {
        $admin = UserFactory::createUser();
        $adminLogin = OmegaupTestCase::login($admin);

        // Creating a course with one assignment and turning on show_scoreboard flag
        $courseData = CoursesFactory::createCourseWithOneAssignment($admin, $adminLogin, null, null, 'true');
        $group = GroupsDAO::getByPK($courseData['request']['course']->group_id);
        // User not linked to course
        $user = UserFactory::createUser();
        $identityUser = IdentitiesDAO::getByPK($user->main_identity_id);
        // User linked to course
        $student = UserFactory::createUser();
        $identityStudent = IdentitiesDAO::getByPK($student->main_identity_id);
        $response = CourseController::apiAddStudent(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $student->username,
            'course_alias' => $courseData['course_alias'],
        ]));

        $course = CoursesDAO::getByPK($courseData['request']['course']->course_id);
        $studentLogin = OmegaupTestCase::login($student);
        // Scoreboard have to be visible to associated user
        $this->assertTrue(CourseController::shouldShowScoreboard(
            $identityStudent,
            $course,
            $group
        ));
        // But, Scoreboard shouldn't  be visible to unassociated user
        $this->assertFalse(CourseController::shouldShowScoreboard(
            $identityUser,
            $course,
            $group
        ));

        // Turning off show_scoreboard flag
        CourseController::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'name' => $courseData['request']['course']->name,
            'description' => $courseData['request']['course']->description,
            'alias' => $courseData['request']['course']->alias,
            'show_scoreboard' => 'false',
        ]));

        $course = CoursesDAO::getByPK($courseData['request']['course']->course_id);

        // Scoreboard shouldn't be visible to associated or unassociated user
        $this->assertFalse(CourseController::shouldShowScoreboard(
            $identityStudent,
            $course,
            $group
        ));
        $this->assertFalse(CourseController::shouldShowScoreboard(
            $identityUser,
            $course,
            $group
        ));
    }
}
