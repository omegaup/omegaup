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
        $students = [];
        for ($i = 0; $i < 3; $i++) {
            $students[$i] = CoursesFactory::addStudentToCourse($courseData);
        }

        // Call apiStudentList by an admin
        $adminLogin = self::login($courseData['admin']);
        $response = CourseController::apiListStudents(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias']
        ]));

        $this->assertEquals('ok', $response['status']);
        foreach ($students as $s) {
            $this->assertArrayContainsInKey($response['students'], 'username', $s->username);
        }
    }

    /**
     * List can only be retreived by an admin
     * @expectedException ForbiddenAccessException
     */
    public function testCourseStudentListNonAdmin() {
        $courseData = CoursesFactory::createCourse();

        // Call apiStudentList by another random user
        $userLogin = self::login(UserFactory::createUser());
        $response = CourseController::apiListStudents(new Request([
            'auth_token' => $userLogin->auth_token,
            'course_alias' => $courseData['course_alias']
        ]));
    }

    /**
     * Course does not exists test
     * @expectedException InvalidParameterException
     */
    public function testCourseStudentListInvalidCourse() {
        // Call apiStudentList by another random user
        $userLogin = self::login(UserFactory::createUser());
        $response = CourseController::apiListStudents(new Request([
            'auth_token' => $userLogin->auth_token,
            'course_alias' => 'foo'
        ]));
    }

    /**
     * API returns correct counts of assignments by type
     */
    public function testCounts() {
        $homeworkCount = 3;
        $testCount = 2;
        $courseData = CoursesFactory::createCourseWithNAssignmentsPerType(
            ['homework' => $homeworkCount, 'test' => $testCount]
        );

        $adminLogin = self::login($courseData['admin']);
        $response = CourseController::apiListStudents(new Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $courseData['course_alias']
        ]));

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals($homeworkCount, $response['counts']['homework']);
        $this->assertEquals($testCount, $response['counts']['test']);
    }
}
