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
}
