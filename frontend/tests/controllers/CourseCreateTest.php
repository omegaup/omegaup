<?php

class CourseCreateTest extends \OmegaUp\Test\ControllerTestCase {
    private static $curator = null;
    private static $curatorIdentity = null;

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();

        $curatorGroup = \OmegaUp\DAO\Groups::findByAlias(
            \OmegaUp\Authorization::COURSE_CURATOR_GROUP_ALIAS
        );

        ['user' => self::$curator, 'identity' => self::$curatorIdentity] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\DAO\GroupsIdentities::create(new \OmegaUp\DAO\VO\GroupsIdentities([
            'group_id' => $curatorGroup->group_id,
            'identity_id' => self::$curatorIdentity->identity_id,
        ]));
    }

    /**
     * Create course hot path
     */
    public function testCreateSchoolCourse() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'alias' => \OmegaUp\Test\Utils::createRandomString(),
            'description' => \OmegaUp\Test\Utils::createRandomString(),
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
     * Create course with unlimited duration
     */
    public function testCreateCourseWithUnlimitedDuration() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $name = \OmegaUp\Test\Utils::createRandomString();
        $response = \OmegaUp\Controllers\Course::apiCreate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => $name,
            'alias' => \OmegaUp\Test\Utils::createRandomString(),
            'description' => \OmegaUp\Test\Utils::createRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'unlimited_duration' => true,
        ]));

        $courses = \OmegaUp\DAO\Courses::findByName(
            $name
        );

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(
            1,
            count($courses)
        );
        $this->assertNull($courses[0]->finish_time);
    }

    /**
     * Two courses cannot have the same alias
     *
     * @expectedException \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     */
    public function testCreateCourseDuplicatedName() {
        $sameAlias = \OmegaUp\Test\Utils::createRandomString();
        $sameName = \OmegaUp\Test\Utils::createRandomString();

        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => $sameName,
            'alias' => $sameAlias,
            'description' => \OmegaUp\Test\Utils::createRandomString(),
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
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => $sameName,
            'alias' => $sameAlias,
            'description' => \OmegaUp\Test\Utils::createRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120)
        ]);

        \OmegaUp\Controllers\Course::apiCreate($r);
    }

    public function testCreateSchoolAssignment() {
        // Create a test course
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $courseAlias = \OmegaUp\Test\Utils::createRandomString();

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'alias' => $courseAlias,
            'description' => \OmegaUp\Test\Utils::createRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120)
        ]);

        // Call api
        $response = \OmegaUp\Controllers\Course::apiCreate($r);
        $this->assertEquals('ok', $response['status']);

        // Create a test course
        $login = self::login($identity);
        $assignmentAlias = \OmegaUp\Test\Utils::createRandomString();
        $response = \OmegaUp\Controllers\Course::apiCreateAssignment(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'alias' => $assignmentAlias,
            'description' => \OmegaUp\Test\Utils::createRandomString(),
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
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
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
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();

        $adminLogin = self::login($courseData['admin']);
        $response = \OmegaUp\Controllers\Course::apiListAssignments(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias']
        ]));

        $this->assertEquals(1, count($response['assignments']));

        // Create another course with 5 assignment and list them
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithAssignments(
            5
        );

        $adminLogin = self::login($courseData['admin']);
        $response = \OmegaUp\Controllers\Course::apiListAssignments(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias']
        ]));

        $this->assertEquals(5, count($response['assignments']));
    }

    public function testDuplicateAssignmentAliases() {
        ['user' => $admin, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $adminLogin = \OmegaUp\Test\ControllerTestCase::login($identity);

        $assignmentAlias = \OmegaUp\Test\Utils::createRandomString();

        // Create the course number 1
        $courseFactoryResult = \OmegaUp\Test\Factories\Course::createCourse(
            $identity,
            $adminLogin
        );
        $courseAlias = $courseFactoryResult['course_alias'];

        // Create the assignment number 1
        \OmegaUp\Controllers\Course::apiCreateAssignment(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'alias' => $assignmentAlias,
            'description' => \OmegaUp\Test\Utils::createRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120),
            'course_alias' => $courseAlias,
            'assignment_type' => 'homework'
        ]));

        // Create the course number 2
        $courseFactoryResult = \OmegaUp\Test\Factories\Course::createCourse(
            $identity,
            $adminLogin
        );
        $courseAlias = $courseFactoryResult['course_alias'];

        // Create the assignment number 2
        \OmegaUp\Controllers\Course::apiCreateAssignment(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'alias' => $assignmentAlias,
            'description' => \OmegaUp\Test\Utils::createRandomString(),
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
        ['user' => $admin, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $adminLogin = \OmegaUp\Test\ControllerTestCase::login($identity);
        $courseData = \OmegaUp\Test\Factories\Course::createCourse(
            $identity,
            $adminLogin
        );

        \OmegaUp\Controllers\Course::apiCreateAssignment(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'alias' => \OmegaUp\Test\Utils::createRandomString(),
            'description' => \OmegaUp\Test\Utils::createRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 120),
            'finish_time' => (\OmegaUp\Time::get() + 60),
            'course_alias' => $courseData['course_alias'],
            'assignment_type' => 'homework'
        ]));
    }

    public function testCreateAssignmentWithUnlimitedDuration() {
        ['user' => $admin, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $adminLogin = \OmegaUp\Test\ControllerTestCase::login($identity);

        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $identity,
            $adminLogin,
            false,
            'no',
            'true'
        );

        // Try to create an assignment with unlimited duration
        // in a non unlimited duration course.
        try {
            \OmegaUp\Controllers\Course::apiCreateAssignment(new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'name' => \OmegaUp\Test\Utils::createRandomString(),
                'alias' => \OmegaUp\Test\Utils::createRandomString(),
                'description' => \OmegaUp\Test\Utils::createRandomString(),
                'course_alias' => $courseData['course_alias'],
                'assignment_type' => 'homework',
                'start_time' => (\OmegaUp\Time::get() + 120),
            ]));
            $this->assertFail('Should have thrown exception');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals('parameterEmpty', $e->getMessage());
        }

        // Now update the course in order to be of unlimited duration
        \OmegaUp\Controllers\Course::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'name' => $courseData['request']['course']->name,
            'description' => $courseData['request']['course']->description,
            'alias' => $courseData['request']['course']->alias,
            'show_scoreboard' => false,
            'finish_time' => (\OmegaUp\Time::get() - 18360),
            'unlimited_duration' => true
        ]));

        $updatedCourse = \OmegaUp\DAO\Courses::getByPK(
            $courseData['request']['course']->course_id
        );

        // This assignment should have unlimited duration
        $assignmentAlias = \OmegaUp\Test\Utils::createRandomString();
        \OmegaUp\Controllers\Course::apiCreateAssignment(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'alias' => $assignmentAlias,
            'description' => \OmegaUp\Test\Utils::createRandomString(),
            'course_alias' => $courseData['course_alias'],
            'assignment_type' => 'homework',
            'start_time' => (\OmegaUp\Time::get() + 120),
            'unlimited_duration' => true,
        ]));

        $assignment = \OmegaUp\DAO\Assignments::getByAliasAndCourse(
            $assignmentAlias,
            $updatedCourse->course_id
        );
        $this->assertNull($assignment->finish_time);

        // Create an assignment with duration so big (but not unlimited)
        // in an unlimited duration course
        $assignmentAlias = \OmegaUp\Test\Utils::createRandomString();
        $finishTime = (\OmegaUp\Time::get() + 12000000);
        \OmegaUp\Controllers\Course::apiCreateAssignment(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'alias' => $assignmentAlias,
            'description' => \OmegaUp\Test\Utils::createRandomString(),
            'course_alias' => $courseData['course_alias'],
            'assignment_type' => 'homework',
            'start_time' => (\OmegaUp\Time::get() + 120),
            'finish_time' => $finishTime,
        ]));

        $assignment = \OmegaUp\DAO\Assignments::getByAliasAndCourse(
            $assignmentAlias,
            $updatedCourse->course_id
        );
        $this->assertEquals($finishTime, $assignment->finish_time);
    }

    /**
     * Public course can't be created by default
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testCreatePublicCourseFailForNonCurator() {
        ['user' => $user, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'alias' => \OmegaUp\Test\Utils::createRandomString(),
            'description' => \OmegaUp\Test\Utils::createRandomString(),
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
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'alias' => \OmegaUp\Test\Utils::createRandomString(),
            'description' => \OmegaUp\Test\Utils::createRandomString(),
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
        ['user' => $admin, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $adminLogin = \OmegaUp\Test\ControllerTestCase::login($identity);

        // Creating a course with one assignment and turning on show_scoreboard flag
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $identity,
            $adminLogin,
            false,
            'no',
            'true'
        );
        $group = \OmegaUp\DAO\Groups::getByPK(
            $courseData['request']['course']->group_id
        );
        // User not linked to course
        ['user' => $user, 'identity' => $identityUser] = \OmegaUp\Test\Factories\User::createUser();

        // User linked to course
        ['user' => $student, 'identity' => $identityStudent] = \OmegaUp\Test\Factories\User::createUser();
        $response = \OmegaUp\Controllers\Course::apiAddStudent(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $identityStudent->username,
            'course_alias' => $courseData['course_alias'],
        ]));

        $course = \OmegaUp\DAO\Courses::getByPK(
            $courseData['request']['course']->course_id
        );
        $studentLogin = \OmegaUp\Test\ControllerTestCase::login(
            $identityStudent
        );
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

    public function testUpdateCourseFinishTime() {
        ['user' => $admin, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $adminLogin = \OmegaUp\Test\ControllerTestCase::login($identity);

        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $identity,
            $adminLogin,
            false,
            'no',
            'true'
        );

        // Should not update the finish time
        \OmegaUp\Controllers\Course::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'name' => $courseData['request']['course']->name,
            'description' => $courseData['request']['course']->description,
            'alias' => $courseData['request']['course']->alias,
            'show_scoreboard' => false,
        ]));
        $course = \OmegaUp\DAO\Courses::getByPK(
            $courseData['request']['course']->course_id
        );
        $this->assertEquals(
            $courseData['request']['course']->finish_time,
            $course->finish_time
        );

        // Should update the finish time as expected
        \OmegaUp\Controllers\Course::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'name' => $courseData['request']['course']->name,
            'description' => $courseData['request']['course']->description,
            'alias' => $courseData['request']['course']->alias,
            'show_scoreboard' => false,
            'finish_time' => (\OmegaUp\Time::get() + 360)
        ]));
        $newCourse = \OmegaUp\DAO\Courses::getByPK(
            $courseData['request']['course']->course_id
        );
        $this->assertNotEquals(
            $courseData['request']['course']->finish_time,
            $newCourse->finish_time
        );

        // Should set the finish time as null
        \OmegaUp\Controllers\Course::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'name' => $courseData['request']['course']->name,
            'description' => $courseData['request']['course']->description,
            'alias' => $courseData['request']['course']->alias,
            'show_scoreboard' => false,
            'finish_time' => (\OmegaUp\Time::get() - 18360),
            'unlimited_duration' => true
        ]));
        $newCourse = \OmegaUp\DAO\Courses::getByPK(
            $courseData['request']['course']->course_id
        );
        $this->assertNull($newCourse->finish_time);

        // Now update one more time with the unlimited_duration set as false
        $newFinishTime = (\OmegaUp\Time::get() + 540);
        \OmegaUp\Controllers\Course::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
            'name' => $courseData['request']['course']->name,
            'description' => $courseData['request']['course']->description,
            'alias' => $courseData['request']['course']->alias,
            'show_scoreboard' => false,
            'finish_time' => $newFinishTime,
            'unlimited_duration' => false
        ]));
        $newCourse = \OmegaUp\DAO\Courses::getByPK(
            $courseData['request']['course']->course_id
        );
        $this->assertEquals($newFinishTime, $newCourse->finish_time);
    }
}
