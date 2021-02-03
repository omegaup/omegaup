<?php

class CourseCreateTest extends \OmegaUp\Test\ControllerTestCase {
    private static $curator = null;
    private static $curatorIdentity = null;

    public static function setUpBeforeClass(): void {
        parent::setUpBeforeClass();

        $curatorGroup = \OmegaUp\DAO\Groups::findByAlias(
            \OmegaUp\Authorization::COURSE_CURATOR_GROUP_ALIAS
        );

        [
            'user' => self::$curator,
            'identity' => self::$curatorIdentity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\DAO\GroupsIdentities::create(
            new \OmegaUp\DAO\VO\GroupsIdentities([
                'group_id' => $curatorGroup->group_id,
                'identity_id' => self::$curatorIdentity->identity_id,
            ])
        );
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
     */
    public function testCreateCourseDuplicatedName() {
        $sameAlias = \OmegaUp\Test\Utils::createRandomString();
        $sameName = \OmegaUp\Test\Utils::createRandomString();

        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();

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
        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\Course::apiCreate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => $sameName,
                'alias' => $sameAlias,
                'description' => \OmegaUp\Test\Utils::createRandomString(),
                'start_time' => (\OmegaUp\Time::get() + 60),
                'finish_time' => (\OmegaUp\Time::get() + 120)
            ]));
            $this->fail('Should have thrown exception');
        } catch (\OmegaUp\Exceptions\DuplicatedEntryInDatabaseException $e) {
            $this->assertEquals('aliasInUse', $e->getMessage());
        }
    }

    public function testCreateCourseWithDefinedLanguages() {
        $alias = \OmegaUp\Test\Utils::createRandomString();
        $name = \OmegaUp\Test\Utils::createRandomString();
        $expectedLanguages = ['py2', 'py3'];

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);

        $response = \OmegaUp\Controllers\Course::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => $name,
                'alias' => $alias,
                'description' => \OmegaUp\Test\Utils::createRandomString(),
                'start_time' => (\OmegaUp\Time::get() + 60),
                'finish_time' => (\OmegaUp\Time::get() + 120),
                'languages' => implode(',', $expectedLanguages),
            ])
        );

        $this->assertEquals('ok', $response['status']);
        $course = \OmegaUp\Controllers\Course::apiDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'alias' => $alias,
        ]));

        $this->assertTrue(
            !array_diff($course['languages'], $expectedLanguages)
        );
    }

    public function testCreateCourseWithWrongLanguage() {
        $alias = \OmegaUp\Test\Utils::createRandomString();
        $name = \OmegaUp\Test\Utils::createRandomString();
        $expectedLanguages = ['py2', 'px3'];

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\Course::apiCreate(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'name' => $name,
                    'alias' => $alias,
                    'description' => \OmegaUp\Test\Utils::createRandomString(),
                    'start_time' => (\OmegaUp\Time::get() + 60),
                    'finish_time' => (\OmegaUp\Time::get() + 120),
                    'languages' => implode(',', $expectedLanguages),
                ])
            );
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals('parameterNotInExpectedSet', $e->getMessage());
        }
    }

    public function testEditLanguagesInCourse() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();
        $login = self::login($courseData['admin']);
        $course = \OmegaUp\Controllers\Course::apiDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'alias' => $courseData['course_alias'],
        ]));

        $this->assertTrue(
            !array_diff(
                $course['languages'],
                array_keys(\OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES)
            )
        );

        $expectedNewLanguages = ['py2', 'py3'];

        $response = \OmegaUp\Controllers\Course::apiUpdate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'alias' => $courseData['course_alias'],
                'languages' => implode(',', $expectedNewLanguages),
            ])
        );

        $this->assertEquals('ok', $response['status']);
        $course = \OmegaUp\Controllers\Course::apiDetails(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'alias' => $courseData['course_alias'],
        ]));

        $this->assertTrue(
            !array_diff($course['languages'], $expectedNewLanguages)
        );
    }

    public function testCreateSchoolAssignment() {
        // Create a test course
        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();

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
        $response = \OmegaUp\Controllers\Course::apiCreateAssignment(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => \OmegaUp\Test\Utils::createRandomString(),
                'alias' => $assignmentAlias,
                'description' => \OmegaUp\Test\Utils::createRandomString(),
                'start_time' => (\OmegaUp\Time::get() + 60),
                'finish_time' => (\OmegaUp\Time::get() + 120),
                'course_alias' => $courseAlias,
                'assignment_type' => 'homework'
            ])
        );
        $this->assertEquals('ok', $response['status']);

        // There should exist 1 assignment with this alias
        $course = \OmegaUp\DAO\Courses::getByAlias($courseAlias);
        $assignment = \OmegaUp\DAO\Assignments::getByAliasAndCourse(
            $assignmentAlias,
            $course->course_id
        );
        $this->assertNotNull($assignment);

        // Add a problem to the assignment.
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(
            new \OmegaUp\Test\Factories\ProblemParams([
                'visibility' => 'public',
                'user' => $user
            ]),
            $login
        );
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
     * A PHPUnit data provider for the test with problems in a course.
     *
     * @return list<array{0: list<int|null|string>, 1: float|null}>
     */
    public function assignmentWithProblemsAndPointsValueProvider(): array {
        return [
            [[null, null, null], 300.0],
            [[100, 80, 20], 200.0],
            [[50, null, 20], 170.0],
            [[50, null, '80'], 230.0],
            [['wrong_value', null, '80'], null],
        ];
    }

    /**
     * @param list<int|null>
     *
     * @dataProvider assignmentWithProblemsAndPointsValueProvider
     */
    public function testCreateSchoolAssignmentWithProblems(
        array $problemPoints,
        ?float $expectedTotalPoints
    ) {
        // Create a test course
        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();
        $courseAlias = \OmegaUp\Test\Utils::createRandomString();
        $login = self::login($identity);

        // Call api
        $response = \OmegaUp\Controllers\Course::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => \OmegaUp\Test\Utils::createRandomString(),
                'alias' => $courseAlias,
                'description' => \OmegaUp\Test\Utils::createRandomString(),
                'start_time' => (\OmegaUp\Time::get() + 60),
                'finish_time' => (\OmegaUp\Time::get() + 120)
            ])
        );
        $this->assertEquals('ok', $response['status']);

        // Create problems
        $numberOfProblems = count($problemPoints);
        $pointsTotal = 0;
        $problemsData = [];
        foreach ($problemPoints as $points) {
            $problemRequest = \OmegaUp\Test\Factories\Problem::createProblem(
                new \OmegaUp\Test\Factories\ProblemParams(['user' => $user]),
                $login
            )['request'];
            $currentProblemData = ['alias' => $problemRequest['problem_alias']];
            if (!is_null($points)) {
                $currentProblemData['points'] = $points;
            }
            $problemsData[] = $currentProblemData;
        }

        // Create the assignment
        $login = self::login($identity);
        $assignmentAlias = \OmegaUp\Test\Utils::createRandomString();
        $assignmentRequest = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'alias' => $assignmentAlias,
            'description' => \OmegaUp\Test\Utils::createRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120),
            'course_alias' => $courseAlias,
            'assignment_type' => 'homework',
            'problems' => json_encode($problemsData)
        ]);
        if (is_null($expectedTotalPoints)) {
            try {
                \OmegaUp\Controllers\Course::apiCreateAssignment(
                    $assignmentRequest
                );
                $this->fail('Should have thrown exception');
            } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
                $this->assertEquals('parameterNotANumber', $e->getMessage());
                return;
            }
        }
        $response = \OmegaUp\Controllers\Course::apiCreateAssignment(
            $assignmentRequest
        );
        $this->assertEquals('ok', $response['status']);

        // There should exist 1 assignment with this alias
        $course = \OmegaUp\DAO\Courses::getByAlias($courseAlias);
        $assignment = \OmegaUp\DAO\Assignments::getByAliasAndCourse(
            $assignmentAlias,
            $course->course_id
        );
        $this->assertNotNull($assignment);

        // Check problems were added to the underlying problemset
        $problems = \OmegaUp\DAO\ProblemsetProblems::getByProblemset(
            $assignment->problemset_id
        );
        $this->assertCount($numberOfProblems, $problems);
        $this->assertEquals($expectedTotalPoints, $assignment->max_points);
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
     */
    public function testCreateAssignmentWithInvertedTimes() {
        ['user' => $admin, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $adminLogin = \OmegaUp\Test\ControllerTestCase::login($identity);
        $courseData = \OmegaUp\Test\Factories\Course::createCourse(
            $identity,
            $adminLogin
        );

        try {
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
            $this->fail('Should have thrown exception');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals('courseInvalidStartTime', $e->getMessage());
        }
    }

    public function testCreateAssignmentWithUnlimitedDuration() {
        ['user' => $admin, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $adminLogin = \OmegaUp\Test\ControllerTestCase::login($identity);

        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $identity,
            $adminLogin,
            \OmegaUp\Controllers\Course::ADMISSION_MODE_PRIVATE,
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
            $this->fail('Should have thrown exception');
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
        $this->assertEquals($finishTime, $assignment->finish_time->time);
    }

    /**
     * Public course can't be created by default
     */
    public function testCreatePublicCourseFailForNonCurator() {
        [
            'user' => $user,
            'identity' => $identity,
        ] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\Course::apiCreate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => \OmegaUp\Test\Utils::createRandomString(),
                'alias' => \OmegaUp\Test\Utils::createRandomString(),
                'description' => \OmegaUp\Test\Utils::createRandomString(),
                'start_time' => (\OmegaUp\Time::get() + 60),
                'finish_time' => (\OmegaUp\Time::get() + 120),
                'admission_mode' => \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC,
            ]));
            $this->fail('Should have thrown exception');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    public function testCreateCourseWithInvalidAlias() {
        $login = self::login(self::$curatorIdentity);
        try {
            \OmegaUp\Controllers\Course::apiCreate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'name' => \OmegaUp\Test\Utils::createRandomString(),
                'alias' => 'wrong alias',
                'description' => \OmegaUp\Test\Utils::createRandomString(),
                'start_time' => (\OmegaUp\Time::get() + 60),
                'finish_time' => (\OmegaUp\Time::get() + 120),
                'admission_mode' => \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC,
            ]));
            $this->fail('Should have thrown exception');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals('parameterInvalid', $e->getMessage());
        }
    }

    /**
     * Only curators can make Public courses
     */
    public function testCreatePublicCourse() {
        $login = self::login(self::$curatorIdentity);
        $school = \OmegaUp\Test\Factories\Schools::createSchool()['school'];
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'alias' => \OmegaUp\Test\Utils::createRandomString(),
            'description' => \OmegaUp\Test\Utils::createRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120),
            'school_id' => $school->school_id,
            'admission_mode' => \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC,
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
            \OmegaUp\Controllers\Course::ADMISSION_MODE_PRIVATE,
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
            \OmegaUp\Controllers\Course::ADMISSION_MODE_PRIVATE,
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
        $this->assertEquals($newFinishTime, $newCourse->finish_time->time);
    }

    /**
     * Updating admission_mode for Courses, testing all the diferent modes to
     * join a course: Public, Private and Registration
     */
    public function testUpdateCourseAdmissionMode() {
        $adminLogin = self::login(self::$curatorIdentity);
        $school = \OmegaUp\Test\Factories\Schools::createSchool()['school'];
        $alias = \OmegaUp\Test\Utils::createRandomString();
        $r = new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'name' => \OmegaUp\Test\Utils::createRandomString(),
            'alias' => $alias,
            'description' => \OmegaUp\Test\Utils::createRandomString(),
            'start_time' => (\OmegaUp\Time::get() + 60),
            'finish_time' => (\OmegaUp\Time::get() + 120),
            'school_id' => $school->school_id,
        ]);

        $response = \OmegaUp\Controllers\Course::apiCreate($r);

        $course = \OmegaUp\DAO\Courses::getByAlias($alias);

        // The admission mode for a course should be default private
        $this->assertEquals(
            $course->admission_mode,
            \OmegaUp\Controllers\Course::ADMISSION_MODE_PRIVATE
        );

        // Should update to public the admission mode
        \OmegaUp\Controllers\Course::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $alias,
            'name' => $course->name,
            'description' => $course->description,
            'alias' => $course->alias,
            'admission_mode' => \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC,
        ]));
        $course = \OmegaUp\DAO\Courses::getByAlias($alias);
        $this->assertEquals(
            $course->admission_mode,
            \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC
        );

        // Should update to registration the admission mode
        \OmegaUp\Controllers\Course::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $alias,
            'name' => $course->name,
            'description' => $course->description,
            'alias' => $course->alias,
            'admission_mode' => \OmegaUp\Controllers\Course::ADMISSION_MODE_REGISTRATION,
        ]));
        $course = \OmegaUp\DAO\Courses::getByAlias($alias);
        $this->assertEquals(
            $course->admission_mode,
            \OmegaUp\Controllers\Course::ADMISSION_MODE_REGISTRATION
        );
    }

    /**
     * Course can't be updated to Public by non-curator user
     */
    public function testUpdatePublicCourseFailForNonCurator() {
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $school = \OmegaUp\Test\Factories\Schools::createSchool()['school'];
        $alias = \OmegaUp\Test\Utils::createRandomString();

        $login = self::login($identity);

        \OmegaUp\Controllers\Course::apiCreate(
            new \OmegaUp\Request(
                [
                    'auth_token' => $login->auth_token,
                    'name' => \OmegaUp\Test\Utils::createRandomString(),
                    'alias' => $alias,
                    'description' => \OmegaUp\Test\Utils::createRandomString(),
                    'start_time' => (\OmegaUp\Time::get() + 60),
                    'finish_time' => (\OmegaUp\Time::get() + 120),
                    'admission_mode' => \OmegaUp\Controllers\Course::ADMISSION_MODE_PRIVATE,
                ]
            )
        );
        $course = \OmegaUp\DAO\Courses::getByAlias($alias);

        // Should not update to public the admission mode
        try {
            \OmegaUp\Controllers\Course::apiUpdate(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'course_alias' => $alias,
                'name' => $course->name,
                'description' => $course->description,
                'alias' => $course->alias,
                'admission_mode' => \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC,
            ]));
            $this->fail(
                'Should have thrown exception, because user is not curator'
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }

        // But user should be update to 'with registration' mode because there
        // is no restriction.
        \OmegaUp\Controllers\Course::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'course_alias' => $alias,
            'name' => $course->name,
            'description' => $course->description,
            'alias' => $course->alias,
            'admission_mode' => \OmegaUp\Controllers\Course::ADMISSION_MODE_REGISTRATION,
        ]));
        $course = \OmegaUp\DAO\Courses::getByAlias($alias);
        $this->assertEquals(
            $course->admission_mode,
            \OmegaUp\Controllers\Course::ADMISSION_MODE_REGISTRATION
        );
    }
}
