<?php

class CourseCreateTest extends OmegaupTestCase {
    private static $curator = null;
    private static $curatorIdentity = null;

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();

        $curatorGroup = \OmegaUp\DAO\Groups::findByAlias(
            \OmegaUp\Authorization::COURSE_CURATOR_GROUP_ALIAS
        );

        ['user' => self::$curator, 'identity' => self::$curatorIdentity] = UserFactory::createUser();
        \OmegaUp\DAO\GroupsIdentities::create(new \OmegaUp\DAO\VO\GroupsIdentities([
            'group_id' => $curatorGroup->group_id,
            'identity_id' => self::$curatorIdentity->identity_id,
        ]));
    }

    /**
     * Create course hot path
     */
    public function testCreateSchoolCourse() {
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => Utils::CreateRandomString(),
            'description' => Utils::CreateRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120)
        ]);

        $response = \OmegaUp\Controllers\Course::apiCreate($r);

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(
            1,
            count(
                \OmegaUp\DAO\Courses::findByName(
                    $r['name']
                )
            )
        );
    }

    /**
     * Two courses cannot have the same alias
     *
     * @expectedException \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     */
    public function testCreateCourseDuplicatedName() {
        $sameAlias = Utils::CreateRandomString();
        $sameName = Utils::CreateRandomString();

        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => $sameName,
            'alias' => $sameAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120)
        ]);

        $response = \OmegaUp\Controllers\Course::apiCreate($r);

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(
            1,
            count(
                \OmegaUp\DAO\Courses::findByName(
                    $r['name']
                )
            )
        );

        // Create a new Course with different alias and name
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => $sameName,
            'alias' => $sameAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120)
        ]);

        \OmegaUp\Controllers\Course::apiCreate($r);
    }

    public function testCreateSchoolAssignment() {
        // Create a test course
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        $courseAlias = Utils::CreateRandomString();

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => $courseAlias,
            'description' => Utils::CreateRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120)
        ]);

        // Call api
        $response = \OmegaUp\Controllers\Course::apiCreate($r);
        $this->assertEquals('ok', $response['status']);

        // Create a test course
        $login = self::login($identity);
        $assignmentAlias = Utils::CreateRandomString();
        $response = \OmegaUp\Controllers\Course::apiCreateAssignment(new \OmegaUp\Request([
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
        $course = \OmegaUp\DAO\Courses::getByAlias($courseAlias);
        $assignment = \OmegaUp\DAO\Assignments::getByAliasAndCourse(
            $assignmentAlias,
            $course->course_id
        );
        $this->assertNotNull($assignment);

        // Add a problem to the assignment.
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => 1,
            'user' => $user
        ]), $login);
        $points = 1337;
        \OmegaUp\Controllers\Course::apiAddProblem(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'course_alias' => $courseAlias,
            'assignment_alias' => $assignment->alias,
            'problem_alias' => $problemData['problem']->alias,
            'points' => $points,
        ]));

        $problems = \OmegaUp\DAO\ProblemsetProblems::getByProblemset(
            $assignment->problemset_id
        );
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
        $response = \OmegaUp\Controllers\Course::apiListAssignments(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias']
        ]));

        $this->assertEquals(1, count($response['assignments']));

        // Create another course with 5 assignment and list them
        $courseData = CoursesFactory::createCourseWithAssignments(5);

        $adminLogin = self::login($courseData['admin']);
        $response = \OmegaUp\Controllers\Course::apiListAssignments(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias']
        ]));

        $this->assertEquals(5, count($response['assignments']));
    }

    public function testDuplicateAssignmentAliases() {
        ['user' => $admin, 'identity' => $identity] = UserFactory::createUser();
        $adminLogin = OmegaupTestCase::login($identity);

        $assignmentAlias = Utils::CreateRandomString();

        // Create the course number 1
        $courseFactoryResult = CoursesFactory::createCourse(
            $identity,
            $adminLogin
        );
        $courseAlias = $courseFactoryResult['course_alias'];

        // Create the assignment number 1
        \OmegaUp\Controllers\Course::apiCreateAssignment(new \OmegaUp\Request([
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
        $courseFactoryResult = CoursesFactory::createCourse(
            $identity,
            $adminLogin
        );
        $courseAlias = $courseFactoryResult['course_alias'];

        // Create the assignment number 2
        \OmegaUp\Controllers\Course::apiCreateAssignment(new \OmegaUp\Request([
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
        ['user' => $admin, 'identity' => $identity] = UserFactory::createUser();
        $adminLogin = OmegaupTestCase::login($identity);
        $courseData = CoursesFactory::createCourse($identity, $adminLogin);

        \OmegaUp\Controllers\Course::apiCreateAssignment(new \OmegaUp\Request([
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
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testCreatePublicCourseFailForNonCurator() {
        ['user' => $user, 'identity' => $identity] = UserFactory::createUser();

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => Utils::CreateRandomString(),
            'alias' => Utils::CreateRandomString(),
            'description' => Utils::CreateRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120),
            'public' => 1,
        ]);

        $response = \OmegaUp\Controllers\Course::apiCreate($r);
    }

    /**
     * Only curators can make Public courses
     */
    public function testCreatePublicCourse() {
        $login = self::login(self::$curatorIdentity);
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

        $response = \OmegaUp\Controllers\Course::apiCreate($r);

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(
            1,
            count(
                \OmegaUp\DAO\Courses::findByName(
                    $r['name']
                )
            )
        );
        $this->assertEquals(
            $school->name,
            \OmegaUp\Controllers\Course::apiDetails(
                $r
            )['school_name']
        );
    }

    /**
     * Test updating show_scoreboard attribute in the Course object
     */
    public function testUpdateCourseShowScoreboard() {
        ['user' => $admin, 'identity' => $identity] = UserFactory::createUser();
        $adminLogin = OmegaupTestCase::login($identity);

        // Creating a course with one assignment and turning on show_scoreboard flag
        $courseData = CoursesFactory::createCourseWithOneAssignment(
            $identity,
            $adminLogin,
            null,
            null,
            'true'
        );
        $group = \OmegaUp\DAO\Groups::getByPK(
            $courseData['request']['course']->group_id
        );
        // User not linked to course
        ['user' => $user, 'identity' => $identityUser] = UserFactory::createUser();

        // User linked to course
        ['user' => $student, 'identity' => $identityStudent] = UserFactory::createUser();
        $response = \OmegaUp\Controllers\Course::apiAddStudent(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $identityStudent->username,
            'course_alias' => $courseData['course_alias'],
        ]));

        $course = \OmegaUp\DAO\Courses::getByPK(
            $courseData['request']['course']->course_id
        );
        $studentLogin = OmegaupTestCase::login($identityStudent);
        // Scoreboard have to be visible to associated user
        $this->assertTrue(\OmegaUp\Controllers\Course::shouldShowScoreboard(
            $identityStudent,
            $course,
            $group
        ));
        // But, Scoreboard shouldn't  be visible to unassociated user
        $this->assertFalse(\OmegaUp\Controllers\Course::shouldShowScoreboard(
            $identityUser,
            $course,
            $group
        ));

        // Turning off show_scoreboard flag
        \OmegaUp\Controllers\Course::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'name' => $courseData['request']['course']->name,
            'description' => $courseData['request']['course']->description,
            'alias' => $courseData['request']['course']->alias,
            'show_scoreboard' => 'false',
        ]));

        $course = \OmegaUp\DAO\Courses::getByPK(
            $courseData['request']['course']->course_id
        );

        // Scoreboard shouldn't be visible to associated or unassociated user
        $this->assertFalse(\OmegaUp\Controllers\Course::shouldShowScoreboard(
            $identityStudent,
            $course,
            $group
        ));
        $this->assertFalse(\OmegaUp\Controllers\Course::shouldShowScoreboard(
            $identityUser,
            $course,
            $group
        ));
    }
}
