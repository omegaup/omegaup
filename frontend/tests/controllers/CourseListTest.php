<?php

/**
 *
 * @author pablo
 */

class CourseListTest extends OmegaupTestCase {
    public function setUp() {
        parent::setUp();
        $courseData = CoursesFactory::createCourseWithNAssignmentsPerType(
            ['homework' => 3, 'test' => 2]
        );
        $this->admin_user = $courseData['admin'];
        $this->course_alias = $courseData['course_alias'];
        $this->other_user = UserFactory::createUser();

        CoursesFactory::addStudentToCourse($courseData, $this->other_user);
    }

    protected $admin_user;
    protected $course_user;
    protected $other_user;
    protected $course_alias;

    public function testGetCourseForAdminUser() {
        // Call the details API
        $adminLogin = self::login($this->admin_user);
        $response = CourseController::apiListCourses(new Request(array(
            'auth_token' => $adminLogin->auth_token,
        )));

        $this->assertEquals('ok', $response['status']);
        $this->assertArrayHasKey('admin', $response);
        $this->assertArrayHasKey('student', $response);

        $this->assertEquals(1, count($response['admin']));
        $course_array = $response['admin'][0];
        Validators::isNumber(
            $course_array['finish_time'],
            'finish_time',
            true /* required */
        );
        $this->assertEquals(3, $course_array['counts']['homework']);
        $this->assertEquals(2, $course_array['counts']['test']);
    }

    public function testGetCourseListForNormalUser() {
        $otherUserLogin = self::login($this->other_user);
        $response = CourseController::apiListCourses(new Request(array(
            'auth_token' => $otherUserLogin->auth_token,
        )));

        $this->assertEquals('ok', $response['status']);
        $this->assertArrayHasKey('admin', $response);
        $this->assertArrayHasKey('student', $response);

        $this->assertEquals(1, count($response['student']));
        $course_array = $response['student'][0];
        Validators::isNumber(
            $course_array['finish_time'],
            'finish_time',
            true /* required */
        );
        $this->assertEquals(3, $course_array['counts']['homework']);
        $this->assertEquals(2, $course_array['counts']['test']);
    }
}
