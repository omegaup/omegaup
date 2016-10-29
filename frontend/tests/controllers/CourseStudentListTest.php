<?php

/**
 *
 * @author @joemmanuel
 */

class CourseStudentListTest extends OmegaupTestCase {
    /**
     * Basic apiStudentList test
     */
    public function testCourseStudentList() {
        // Create a course
        $courseData = CoursesFactory::createCourse();

        // Add some students to course
        $students = array();
        for ($i = 0; $i < 3; $i++) {
            $students[$i] = CoursesFactory::addStudentToCourse($courseData);
        }

        // Call apiStudentList from admin
        $response = CourseController::apiListStudents(new Request(array(
            'auth_token' => self::login($courseData['user'] /*admin*/),
            'course_alias' => $courseData['course_alias']
        )));

        $this->assertEquals('ok', $response['status']);
        foreach ($students as $s) {
            $this->assertArrayContainsInKey($response['students'], 'username', $s->username);
        }
    }

    /**
     * List can only be retreived from admin
     * @expectedException ForbiddenAccessException
     */
    public function testCourseStudentListNonAdmin() {
        $courseData = CoursesFactory::createCourse();

        // Call apiStudentList from other random user
        $response = CourseController::apiListStudents(new Request(array(
            'auth_token' => self::login(UserFactory::createUser()),
            'course_alias' => $courseData['course_alias']
        )));
    }

    /**
     * Course does not exists test
     * @expectedException InvalidParameterException
     */
    public function testCourseStudentListInvalidCourse() {
        $courseData = CoursesFactory::createCourse();

        // Call apiStudentList from other random user
        $response = CourseController::apiListStudents(new Request(array(
            'auth_token' => self::login(UserFactory::createUser()),
            'course_alias' => 'foo'
        )));
    }
}
