<?php

/**
 * A course might require registration to participate on it.
 *
 * @author juan.pablo@omegaup.com
 */
class CourseRegistrationTest extends \OmegaUp\Test\ControllerTestCase {
    private static $curator = null;

    public static function setUpBeforeClass(): void {
        parent::setUpBeforeClass();

        $curatorGroup = \OmegaUp\DAO\Groups::findByAlias(
            \OmegaUp\Authorization::COURSE_CURATOR_GROUP_ALIAS
        );

        [
            'identity' => self::$curator,
        ] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\DAO\GroupsIdentities::create(
            new \OmegaUp\DAO\VO\GroupsIdentities([
                'group_id' => $curatorGroup->group_id,
                'identity_id' => self::$curator->identity_id,
            ])
        );
    }

    private static function createCourseWithRegistrationMode() {
        $adminLogin = self::login(self::$curator);
        $school = \OmegaUp\Test\Factories\Schools::createSchool()['school'];
        $alias = \OmegaUp\Test\Utils::createRandomString();

        \OmegaUp\Controllers\Course::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'name' => \OmegaUp\Test\Utils::createRandomString(),
                'alias' => $alias,
                'description' => \OmegaUp\Test\Utils::createRandomString(),
                'start_time' => (\OmegaUp\Time::get() + 60),
                'finish_time' => (\OmegaUp\Time::get() + 120),
                'school_id' => $school->school_id,
            ])
        );

        $course = \OmegaUp\DAO\Courses::getByAlias($alias);

        // Update to registration the admission mode
        \OmegaUp\Controllers\Course::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $alias,
            'name' => $course->name,
            'description' => $course->description,
            'alias' => $course->alias,
            'admission_mode' => \OmegaUp\Controllers\Course::ADMISSION_MODE_REGISTRATION,
        ]));

        // Get updated course
        $course = \OmegaUp\DAO\Courses::getByAlias($alias);

        return [
            'course' => $course,
            'adminLogin' => $adminLogin,
        ];
    }

    public function testCreateCourseWithRegistrationMode() {
        $course = self::createCourseWithRegistrationMode()['course'];
        $this->assertEquals($course->admission_mode, 'registration');
    }

    public function testCourseIsPresentInStudentList() {
        $course = self::createCourseWithRegistrationMode()['course'];
        ['identity' => $student] = \OmegaUp\Test\Factories\User::createUser();
        $studentLogin = self::login($student);

        // Course should appear in public course list for students
        $coursesList = \OmegaUp\Controllers\Course::apiListCourses(
            new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
            ])
        );

        $this->assertArrayContainsWithPredicate(
            $coursesList['student'],
            function ($studentCourse) use ($course): bool {
                return $studentCourse['alias'] === $course->alias;
            }
        );
    }

    public function testRequestIsShownInIntroDetails() {
        [
            'course' => $course,
            'adminLogin' => $adminLogin,
        ] = self::createCourseWithRegistrationMode();
        ['identity' => $student] = \OmegaUp\Test\Factories\User::createUser();
        $studentLogin = self::login($student);

        $response = \OmegaUp\Controllers\Course::apiIntroDetails(
            new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'course_alias' => $course->alias,
            ])
        );

        // In courses with registration we should be able to see all the user
        // registration keys, except userRegistrationAccepted, because user
        // has not been accepted yet
        $this->assertArrayHasKey('userRegistrationRequested', $response);
        $this->assertArrayHasKey('userRegistrationAnswered', $response);
        $this->assertArrayNotHasKey('userRegistrationAccepted', $response);

        $privateCourseAlias = \OmegaUp\Test\Utils::createRandomString();
        // In a public or private course, user registration keys do not exist
        \OmegaUp\Controllers\Course::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'name' => \OmegaUp\Test\Utils::createRandomString(),
                'alias' => $privateCourseAlias,
                'description' => \OmegaUp\Test\Utils::createRandomString(),
                'start_time' => (\OmegaUp\Time::get() + 60),
                'finish_time' => (\OmegaUp\Time::get() + 120)
            ])
        );

        \OmegaUp\Controllers\Course::apiAddStudent(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $student->username,
            'course_alias' => $privateCourseAlias,
        ]));

        $response = \OmegaUp\Controllers\Course::apiIntroDetails(
            new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'course_alias' => $privateCourseAlias,
            ])
        );

        $this->assertArrayNotHasKey('userRegistrationRequested', $response);
        $this->assertArrayNotHasKey('userRegistrationAnswered', $response);
        $this->assertArrayNotHasKey('userRegistrationAccepted', $response);
    }

    /**
     * Uers only can register into a course with registration mode
     */
    public function testRegisterForCourse() {
        $course = self::createCourseWithRegistrationMode()['course'];
        ['identity' => $student] = \OmegaUp\Test\Factories\User::createUser();
        $studentLogin = self::login($student);

        \OmegaUp\Controllers\Course::apiRegisterForCourse(
            new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'course_alias' => $course->alias,
            ])
        );

        $registration = \OmegaUp\DAO\CourseIdentityRequest::getByPK(
            $student->identity_id,
            $course->course_id
        );

        $this->assertNotEmpty($registration);
    }

    /**
     * Add a course and send several requests, some of them will be accepted and
     * the others wil be rejected
     */
    public function testRegisterUsersIntoCourse() {
        [
            'course' => $course,
            'adminLogin' => $adminLogin,
        ] = self::createCourseWithRegistrationMode();
        [
            'identity' => $secondaryAdmin
        ] = \OmegaUp\Test\Factories\User::createUser();
        $secondaryAdminLogin = self::login($secondaryAdmin);

        // Let's make course admin the user previously created
        $response = \OmegaUp\Controllers\Course::apiAddAdmin(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'usernameOrEmail' => $secondaryAdmin->username,
                'course_alias' => $course->alias,
            ])
        );

        // Create 5 students, all of them will request access to join the course
        $numberOfStudents = 5;
        foreach (range(0, $numberOfStudents - 1) as $studentId) {
            [
                'identity' => $students[$studentId]
            ] = \OmegaUp\Test\Factories\User::createUser();
            $studentLogin[$studentId] = self::login($students[$studentId]);

            $response = \OmegaUp\Controllers\Course::apiIntroDetails(
                new \OmegaUp\Request([
                    'auth_token' => $studentLogin[$studentId]->auth_token,
                    'course_alias' => $course->alias,
                ])
            );
            $this->assertArrayHasKey('userRegistrationRequested', $response);

            \OmegaUp\Controllers\Course::apiRegisterForCourse(
                new \OmegaUp\Request([
                    'auth_token' => $studentLogin[$studentId]->auth_token,
                    'course_alias' => $course->alias,
                ])
            );
        }

        $courseRequestMainAdmin = new \OmegaUp\Request([
            'course_alias' => $course->alias,
            'auth_token' => $adminLogin->auth_token,
        ]);
        $courseRequestSecondaryAdmin = new \OmegaUp\Request([
            'course_alias' => $course->alias,
            'auth_token' => $secondaryAdminLogin->auth_token,
        ]);
        $result = \OmegaUp\Controllers\Course::apiRequests(
            $courseRequestMainAdmin
        );

        $this->assertCount($numberOfStudents, $result['users']);

        // Expected request resutls in the first round
        $expectedRequestResult = [
            ['admin' => 'main', 'accepted' => true],
            ['admin' => 'secondary', 'accepted' => true],
            ['admin' => 'main', 'accepted' => false],
            ['admin' => 'secondary', 'accepted' => false],
            ['admin' => 'secondary', 'accepted' => null],
        ];

        // In the first round, 2 students will be accepted, 2 will be rejected
        // and the last one wil be ignored.
        foreach ($expectedRequestResult as $id => $expectedRequest) {
            if ($expectedRequest['admin'] === 'main') {
                $request = $courseRequestMainAdmin;
            } else {
                $request = $courseRequestSecondaryAdmin;
            }
            if (is_null($expectedRequest['accepted'])) {
                continue;
            }
            $request['username'] = $students[$id]->username;
            $request['resolution'] = $expectedRequest['accepted'];

            \OmegaUp\Controllers\Course::apiArbitrateRequest($request);
        }

        $result = \OmegaUp\Controllers\Course::apiRequests(
            $courseRequestMainAdmin
        )['users'];

        $this->assertRequestResultsAreEqualToExpected(
            $result,
            $expectedRequestResult
        );

        // In the second round, student with id 3 will be accepted by the
        // main admin, and last student will be rejected by the secondary admin
        $expectedRequestResult[3]['accepted'] = true;
        $expectedRequestResult[4]['accepted'] = false;

        $courseRequestMainAdmin['username'] = $students[3]->username;
        $courseRequestMainAdmin['resolution'] = true;
        \OmegaUp\Controllers\Course::apiArbitrateRequest(
            $courseRequestMainAdmin
        );

        $courseRequestSecondaryAdmin['username'] = $students[4]->username;
        $courseRequestSecondaryAdmin['accepted'] = false;
        \OmegaUp\Controllers\Course::apiArbitrateRequest(
            $courseRequestSecondaryAdmin
        );

        $result = \OmegaUp\Controllers\Course::apiRequests(
            $courseRequestMainAdmin
        )['users'];

        $this->assertRequestResultsAreEqualToExpected(
            $result,
            $expectedRequestResult
        );
    }

    private function assertRequestResultsAreEqualToExpected(
        array $result,
        array $expectedRequestResult
    ): void {
        foreach ($expectedRequestResult as $id => $expectedRequest) {
            if (is_null($expectedRequest['accepted'])) {
                continue;
            }
            $this->assertEquals(
                $expectedRequest['accepted'],
                $result[$id]['accepted']
            );
            if ($expectedRequest['admin'] === 'main') {
                $this->assertEquals(
                    self::$curator->username,
                    $result[$id]['admin']['username']
                );
            } else {
                $this->assertNotEquals(
                    self::$curator->username,
                    $result[$id]['admin']['username']
                );
            }
        }
    }
}
