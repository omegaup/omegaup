<?php

/**
 *
 * @author @joemmanuel
 */

class CourseStudentAddTest extends OmegaupTestCase {
    /**
     * Basic apiAddStudent test
     */
    public function testAddStudentToCourse() {
        $courseData = CoursesFactory::createCourse();
        $student = UserFactory::createUser();

        $adminLogin = OmegaupTestCase::login($courseData['admin']);
        $response = CourseController::apiAddStudent(new Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $student->username,
            'course_alias' => $courseData['course_alias']
            ]));

        $this->assertEquals('ok', $response['status']);

        // Validate student was added
        $course = CoursesDAO::getByAlias($courseData['course_alias']);
        $this->assertNotNull($course);

        $studentsInGroup = GroupsUsersDAO::search(new GroupsUsers([
            'group_id' => $course->group_id,
            'user_id' => $student->user_id
            ]));

        $this->assertNotNull($studentsInGroup);
        $this->assertEquals(1, count($studentsInGroup));
    }

    /**
     * apiAddStudent test with a duplicate student.
     */
    public function testAddDuplicateStudentToCourse() {
        $courseData = CoursesFactory::createCourse();
        $student = UserFactory::createUser();

        $adminLogin = OmegaupTestCase::login($courseData['admin']);
        $response = CourseController::apiAddStudent(new Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $student->username,
            'course_alias' => $courseData['course_alias']
        ]));

        $this->assertEquals('ok', $response['status']);

        // Add the same student. Should throw.
        try {
            $response = CourseController::apiAddStudent(new Request([
                'auth_token' => $adminLogin->auth_token,
                'usernameOrEmail' => $student->username,
                'course_alias' => $courseData['course_alias']
            ]));
            $this->fail('Expected DuplicatedEntryInDatabaseException');
        } catch (DuplicatedEntryInDatabaseException $e) {
            // OK.
        }
    }

    /**
     * Basic apiRemoveStudent test
     */
    public function testRemoveStudentFromCourse() {
        $courseData = CoursesFactory::createCourse();
        $student = UserFactory::createUser();

        $adminLogin = OmegaupTestCase::login($courseData['admin']);
        $response = CourseController::apiAddStudent(new Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $student->username,
            'course_alias' => $courseData['course_alias']
        ]));
        $this->assertEquals('ok', $response['status']);

        $response = CourseController::apiRemoveStudent(new Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $student->username,
            'course_alias' => $courseData['course_alias']
        ]));
        $this->assertEquals('ok', $response['status']);

        // Validate student was removed.
        $course = CoursesDAO::getByAlias($courseData['course_alias']);
        $this->assertNotNull($course);

        $studentsInGroup = GroupsUsersDAO::search(new GroupsUsers([
            'group_id' => $course->group_id,
            'user_id' => $student->user_id
        ]));

        $this->assertNotNull($studentsInGroup);
        $this->assertEquals(0, count($studentsInGroup));
    }

    /**
     * Students can only be added by course admins
     *
     * @expectedException ForbiddenAccessException
     */
    public function testAddStudentNonAdmin() {
        $courseData = CoursesFactory::createCourse();
        $student = UserFactory::createUser();
        $nonAdminUser = UserFactory::createUser();

        $nonAdminLogin = OmegaupTestCase::login($nonAdminUser);
        CourseController::apiAddStudent(new Request([
            'auth_token' => $nonAdminLogin->auth_token,
            'usernameOrEmail' => $student->username,
            'course_alias' => $courseData['course_alias']
            ]));
    }

    /**
     * Can't self-register unless Public
     * @expectedException ForbiddenAccessException
     */
    public function testSelfAddStudentNoPublic() {
        $courseData = CoursesFactory::createCourse();
        $student = UserFactory::createUser();

        $login = OmegaupTestCase::login($student);
        CourseController::apiAddStudent(new Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $student->username,
            'course_alias' => $courseData['course_alias']
            ]));
    }

    /**
     * Can self-register if course is Public
     */
    public function testSelfAddStudentPublic() {
        $courseData = CoursesFactory::createCourse(null, null, true /*public*/);
        $student = UserFactory::createUser();

        $login = OmegaupTestCase::login($student);
        $response = CourseController::apiAddStudent(new Request([
            'auth_token' => $login->auth_token,
            'usernameOrEmail' => $student->username,
            'course_alias' => $courseData['course_alias']
            ]));

        $this->assertEquals('ok', $response['status']);

        // Validate student was added
        $course = CoursesDAO::getByAlias($courseData['course_alias']);
        $this->assertNotNull($course);

        $studentsInGroup = GroupsUsersDAO::search(new GroupsUsers([
            'group_id' => $course->group_id,
            'user_id' => $student->user_id
            ]));

        $this->assertNotNull($studentsInGroup);
        $this->assertEquals(1, count($studentsInGroup));
    }

    /**
     * Test showIntro with public and private contests
     */
    public function testShouldShowIntro() {
        $courseDataPrivate = CoursesFactory::createCourse();
        $courseDataPublic = CoursesFactory::createCourse(null, null, true);
        $student = UserFactory::createUser();

        // Before or after adding student to private course, intro should not show
        $studentLogin = OmegaupTestCase::login($student);
        try {
            CourseController::shouldShowIntro(new Request([
                'auth_token' => $studentLogin->auth_token,
                'course_alias' => $courseDataPrivate['course_alias']
                ]));
        } catch (NotFoundException $e) {
            // OK!
        }

        $adminLogin = OmegaupTestCase::login($courseDataPrivate['admin']);
        $response = CourseController::apiAddStudent(new Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $student->username,
            'course_alias' => $courseDataPrivate['course_alias']
            ]));

        // Before or after adding student to private course, intro should not show
        $studentLogin = OmegaupTestCase::login($student);
        $this->assertEquals(false, CourseController::shouldShowIntro(new Request([
            'auth_token' => $studentLogin->auth_token,
            'course_alias' => $courseDataPrivate['course_alias']
            ])));

        // Before adding student to public course, intro should show
        $studentLogin = OmegaupTestCase::login($student);
        $this->assertEquals(true, CourseController::shouldShowIntro(new Request([
            'auth_token' => $studentLogin->auth_token,
            'course_alias' => $courseDataPublic['course_alias']
            ])));

        $adminLogin = OmegaupTestCase::login($courseDataPublic['admin']);
        $response = CourseController::apiAddStudent(new Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $student->username,
            'course_alias' => $courseDataPublic['course_alias']
            ]));

        // After adding student to public course, intro should not show
        $studentLogin = OmegaupTestCase::login($student);
        $this->assertEquals(false, CourseController::shouldShowIntro(new Request([
            'auth_token' => $studentLogin->auth_token,
            'course_alias' => $courseDataPublic['course_alias']
            ])));
    }
}
