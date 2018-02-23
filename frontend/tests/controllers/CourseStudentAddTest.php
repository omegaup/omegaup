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

        // Add the same student. It only updates accept_disclose_info field.
        $response = CourseController::apiAddStudent(new Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $student->username,
            'course_alias' => $courseData['course_alias'],
            'accept_disclose_info' => 1
        ]));
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
            CourseController::apiIntroDetails(new Request([
                'auth_token' => $studentLogin->auth_token,
                'course_alias' => $courseDataPrivate['course_alias']
                ]));
        } catch (ForbiddenAccessException $e) {
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
        $intro_details = CourseController::apiIntroDetails(new Request([
            'auth_token' => $studentLogin->auth_token,
            'course_alias' => $courseDataPrivate['course_alias']
            ]));
        $this->assertEquals(false, $intro_details['shouldShowResults']);

        // Before adding student to public course, intro should show
        $studentLogin = OmegaupTestCase::login($student);
        $intro_details = CourseController::apiIntroDetails(new Request([
            'auth_token' => $studentLogin->auth_token,
            'course_alias' => $courseDataPublic['course_alias']
            ]));
        $this->assertEquals('ok', $intro_details['status']);

        $adminLogin = OmegaupTestCase::login($courseDataPublic['admin']);
        $response = CourseController::apiAddStudent(new Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $student->username,
            'course_alias' => $courseDataPublic['course_alias']
            ]));
        $intro_details = CourseController::apiIntroDetails(new Request([
            'auth_token' => $studentLogin->auth_token,
            'course_alias' => $courseDataPublic['course_alias']
            ]));
        // After adding student to public course, intro should not show
        $this->assertEquals(false, $intro_details['shouldShowResults']);
    }
}
