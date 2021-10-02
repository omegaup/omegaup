<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

class CourseStudentAddTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Basic apiAddStudent test
     */
    public function testAddStudentToCourse() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();
        ['user' => $student, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $adminLogin = \OmegaUp\Test\ControllerTestCase::login(
            $courseData['admin']
        );
        \OmegaUp\Controllers\Course::apiAddStudent(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $identity->username,
            'course_alias' => $courseData['course_alias'],
        ]));

        // Validate student was added
        $course = \OmegaUp\DAO\Courses::getByAlias($courseData['course_alias']);
        $this->assertNotNull($course);

        $studentsInGroup = \OmegaUp\DAO\GroupsIdentities::getByPK(
            $course->group_id,
            $identity->identity_id
        );

        $this->assertNotNull($studentsInGroup);
    }

    public function testGetNotificationForManualRegistration() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $adminLogin = \OmegaUp\Test\ControllerTestCase::login(
            $courseData['admin']
        );
        \OmegaUp\Controllers\Course::apiAddStudent(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $identity->username,
            'course_alias' => $courseData['course_alias'],
        ]));

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Notification::apiMyList(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        );
        $notificationContents = $response['notifications'][0]['contents'];

        $this->assertCount(1, $response['notifications']);
        $this->assertEquals(
            \OmegaUp\DAO\Notifications::COURSE_REGISTRATION_MANUAL,
            $notificationContents['type']
        );
        $this->assertEquals(
            $courseData['course_name'],
            $notificationContents['body']['localizationParams']['courseName']
        );
    }

    /**
     * apiAddStudent test with a duplicate student.
     */
    public function testAddDuplicateStudentToCourse() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourse(
            null,
            null,
            \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC,
            'optional'
        );
        ['user' => $student, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\User::createPrivacyStatement(
            'course_optional_consent'
        );
        \OmegaUp\Test\Factories\User::createPrivacyStatement(
            'course_required_consent'
        );

        $adminLogin = \OmegaUp\Test\ControllerTestCase::login(
            $courseData['admin']
        );
        // Student is added to the course
        $response = \OmegaUp\Controllers\Course::apiAddStudent(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $identity->username,
            'course_alias' => $courseData['course_alias'],
        ]));

        // User was added to the course, but it is the first access
        $userLogin = \OmegaUp\Test\ControllerTestCase::login($identity);
        $details = \OmegaUp\Controllers\Course::apiIntroDetails(new \OmegaUp\Request([
            'auth_token' => $userLogin->auth_token,
            'course_alias' => $courseData['request']['alias']
        ]));
        // Asserting isFirstTimeAccess
        $this->assertEquals(
            'optional',
            $details['course']['requests_user_information']
        );
        $this->assertEquals(1, $details['isFirstTimeAccess']);

        $gitObjectId = $details['statements']['privacy']['gitObjectId'];
        $statementType = $details['statements']['privacy']['statementType'];
        // Add the same student. It only updates share_user_information field.
        $response = \OmegaUp\Controllers\Course::apiAddStudent(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $identity->username,
            'course_alias' => $courseData['course_alias'],
            'share_user_information' => 1,
            'git_object_id' => $gitObjectId,
            'statement_type' => $statementType,
        ]));

        // Asserting shouldShowResults is on, because admin cannot update share_user_information
        $this->assertEquals(1, $details['isFirstTimeAccess']);

        // User join course for first time.
        \OmegaUp\Controllers\Course::apiAddStudent(new \OmegaUp\Request([
            'auth_token' => $userLogin->auth_token,
            'usernameOrEmail' => $identity->username,
            'course_alias' => $courseData['course_alias'],
            'share_user_information' => 1,
            'privacy_git_object_id' => $gitObjectId,
            'statement_type' => $statementType,
        ]));

        // User join course twice.
        \OmegaUp\Controllers\Course::apiAddStudent(new \OmegaUp\Request([
            'auth_token' => $userLogin->auth_token,
            'usernameOrEmail' => $identity->username,
            'course_alias' => $courseData['course_alias'],
            'share_user_information' => 1,
            'privacy_git_object_id' => $gitObjectId,
            'statement_type' => $statementType,
        ]));

        // User agrees sharing his information
        $details = \OmegaUp\Controllers\Course::apiIntroDetails(new \OmegaUp\Request([
            'auth_token' => $userLogin->auth_token,
            'course_alias' => $courseData['request']['alias']
        ]));
        // Asserting shouldShowResults is off
        $this->assertEquals(0, $details['shouldShowResults']);
    }

    /**
     * Basic apiRemoveStudent test
     */
    public function testRemoveStudentFromCourse() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();
        ['user' => $student, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $adminLogin = \OmegaUp\Test\ControllerTestCase::login(
            $courseData['admin']
        );
        \OmegaUp\Controllers\Course::apiAddStudent(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $identity->username,
            'course_alias' => $courseData['course_alias'],
        ]));

        \OmegaUp\Controllers\Course::apiRemoveStudent(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $identity->username,
            'course_alias' => $courseData['course_alias']
        ]));

        // Validate student was removed.
        $course = \OmegaUp\DAO\Courses::getByAlias($courseData['course_alias']);
        $this->assertNotNull($course);

        $studentsInGroup = \OmegaUp\DAO\GroupsIdentities::getByGroupId(
            $course->group_id
        );

        $this->assertNotNull($studentsInGroup);
        $this->assertEquals(0, count($studentsInGroup));
    }

    /**
     * Students can only be added by course admins
     */
    public function testAddStudentNonAdmin() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();
        ['user' => $student, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        ['user' => $nonAdminUser, 'identity' => $nonAdminIdentity] = \OmegaUp\Test\Factories\User::createUser();

        $nonAdminLogin = \OmegaUp\Test\ControllerTestCase::login(
            $nonAdminIdentity
        );
        try {
            \OmegaUp\Controllers\Course::apiAddStudent(new \OmegaUp\Request([
                'auth_token' => $nonAdminLogin->auth_token,
                'usernameOrEmail' => $identity->username,
                'course_alias' => $courseData['course_alias'],
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Can't self-register unless Public
     */
    public function testSelfAddStudentNoPublic() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();
        ['user' => $student, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = \OmegaUp\Test\ControllerTestCase::login($identity);
        try {
            \OmegaUp\Controllers\Course::apiAddStudent(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'usernameOrEmail' => $identity->username,
                'course_alias' => $courseData['course_alias'],
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Can self-register if course is Public
     */
    public function testSelfAddStudentPublic() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourse(
            null,
            null,
            \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC
        );
        ['user' => $student, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $login = \OmegaUp\Test\ControllerTestCase::login($identity);
        \OmegaUp\Controllers\Course::apiAddStudent(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $identity->username,
            'course_alias' => $courseData['course_alias'],
        ]));

        // Validate student was added
        $course = \OmegaUp\DAO\Courses::getByAlias($courseData['course_alias']);
        $this->assertNotNull($course);

        $studentsInGroup = \OmegaUp\DAO\GroupsIdentities::getByPK(
            $course->group_id,
            $identity->identity_id
        );

        $this->assertNotNull($studentsInGroup);
    }

    /**
     * Test showIntro with public and private contests
     */
    public function testShouldShowIntro() {
        $courseDataPrivate = \OmegaUp\Test\Factories\Course::createCourse();
        $courseDataPublic = \OmegaUp\Test\Factories\Course::createCourse(
            null,
            null,
            \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC
        );
        ['user' => $student, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Before or after adding student to private course, intro should not show
        $studentLogin = \OmegaUp\Test\ControllerTestCase::login($identity);
        try {
            \OmegaUp\Controllers\Course::apiIntroDetails(new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'course_alias' => $courseDataPrivate['course_alias']
                ]));
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            // OK!
        }

        $adminLogin = \OmegaUp\Test\ControllerTestCase::login(
            $courseDataPrivate['admin']
        );
        $response = \OmegaUp\Controllers\Course::apiAddStudent(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $identity->username,
            'course_alias' => $courseDataPrivate['course_alias'],
            ]));

        // Before or after adding student to private course, intro should not show
        $studentLogin = \OmegaUp\Test\ControllerTestCase::login($identity);
        $details = \OmegaUp\Controllers\Course::apiIntroDetails(new \OmegaUp\Request([
            'auth_token' => $studentLogin->auth_token,
            'course_alias' => $courseDataPrivate['course_alias']
            ]));
        $this->assertEquals(false, $details['shouldShowResults']);

        // Before adding student to public course, intro should show
        $studentLogin = \OmegaUp\Test\ControllerTestCase::login($identity);
        \OmegaUp\Controllers\Course::apiIntroDetails(new \OmegaUp\Request([
            'auth_token' => $studentLogin->auth_token,
            'course_alias' => $courseDataPublic['course_alias']
        ]));

        $adminLogin = \OmegaUp\Test\ControllerTestCase::login(
            $courseDataPublic['admin']
        );
        $response = \OmegaUp\Controllers\Course::apiAddStudent(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $identity->username,
            'course_alias' => $courseDataPublic['course_alias'],
            ]));
        $details = \OmegaUp\Controllers\Course::apiIntroDetails(new \OmegaUp\Request([
            'auth_token' => $studentLogin->auth_token,
            'course_alias' => $courseDataPublic['course_alias']
            ]));
        // After adding student to public course, intro should not show
        $this->assertEquals(false, $details['shouldShowResults']);
    }

    /**
     * User accepts teacher
     */
    public function testUserAcceptsTeacher() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();
        ['user' => $student, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Admin adds user into the course
        $adminLogin = \OmegaUp\Test\ControllerTestCase::login(
            $courseData['admin']
        );
        \OmegaUp\Controllers\Course::apiAddStudent(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $identity->username,
            'course_alias' => $courseData['course_alias'],
        ]));

        // User enters the course and intro details must be shown.
        $studentLogin = \OmegaUp\Test\ControllerTestCase::login($identity);
        $details = \OmegaUp\Controllers\Course::apiIntroDetails(new \OmegaUp\Request([
            'auth_token' => $studentLogin->auth_token,
            'course_alias' => $courseData['course_alias'],
        ]));
        $this->assertEquals(true, $details['shouldShowAcceptTeacher']);

        $gitObjectId = $details['statements']['acceptTeacher']['gitObjectId'];

        // User joins the course and accepts the organizer as teacher
        \OmegaUp\Controllers\Course::apiAddStudent(new \OmegaUp\Request([
            'auth_token' => $studentLogin->auth_token,
            'usernameOrEmail' => $identity->username,
            'course_alias' => $courseData['course_alias'],
            'accept_teacher_git_object_id' => $gitObjectId,
            'accept_teacher' => true,
        ]));
        $details = \OmegaUp\Controllers\Course::getIntroDetails(new \OmegaUp\Request([
            'auth_token' => $studentLogin->auth_token,
            'course_alias' => $courseData['course_alias']
        ]));

        // After adding student to course, intro should not show
        $this->assertArrayNotHasKey('shouldShowAcceptTeacher', $details);
    }

    public function testAddUserWithBasicInformation() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourse(
            /*$admin=*/            null,
            /*$adminLogin=*/ null,
            /*$admissionMode=*/ \OmegaUp\Controllers\Course::ADMISSION_MODE_PRIVATE,
            /*$requestsUserInformation=*/ 'no',
            /*$showScoreboard=*/ 'false',
            /*$courseDuration=*/ 120,
            /*$courseAlias=*/ null,
            /*$needsBasicInformation=*/ true
        );
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Admin adds user into the course
        $adminLogin = \OmegaUp\Test\ControllerTestCase::login(
            $courseData['admin']
        );
        \OmegaUp\Controllers\Course::apiAddStudent(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $identity->username,
            'course_alias' => $courseData['course_alias'],
        ]));

        // User enters the course and intro details must be shown.
        $studentLogin = \OmegaUp\Test\ControllerTestCase::login($identity);
        $details = \OmegaUp\Controllers\Course::apiIntroDetails(
            new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        // At this moment needs_basic_information flag should be turned on
        $this->assertTrue($details['needsBasicInformation']);

        // Users need update their profile, at least the basic information is
        // required (school, state and country), to join this course
        ['school' => $school] = \OmegaUp\Test\Factories\Schools::createSchool();
        $states = \OmegaUp\DAO\States::getByCountry('MX');
        \OmegaUp\Controllers\User::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $studentLogin->auth_token,
            'school_id' => $school->school_id,
            'country_id' => 'MX',
            'state_id' => $states[0]->state_id,
        ]));

        $details = \OmegaUp\Controllers\Course::apiIntroDetails(
            new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        // needs_basic_information flag is no longer turned on
        $this->assertFalse($details['needsBasicInformation']);
    }
}
