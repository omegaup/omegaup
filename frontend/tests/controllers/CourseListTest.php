<?php

/**
 *
 * @author pablo
 */

class CourseListTest extends \OmegaUp\Test\ControllerTestCase {
    public function setUp() {
        parent::setUp();
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithNAssignmentsPerType(
            ['homework' => 3, 'test' => 2]
        );
        $this->admin_user = $courseData['admin'];
        $this->course_alias = $courseData['course_alias'];
        ['user' => $this->other_user, 'identity' => $this->other_identity] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $this->other_identity
        );
    }

    protected $admin_user;
    protected $course_user;
    protected $other_user;
    protected $identity_user;
    protected $other_identity;
    protected $course_alias;

    public function testGetCourseForAdminUser() {
        // Call the details API
        $adminLogin = self::login($this->admin_user);
        $response = \OmegaUp\Controllers\Course::apiListCourses(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
        ]));

        $this->assertArrayHasKey('admin', $response);
        $this->assertArrayHasKey('student', $response);

        $this->assertEquals(1, count($response['admin']));
        $course_array = $response['admin'][0];
        \OmegaUp\Validators::validateNumber(
            $course_array['finish_time'],
            'finish_time'
        );
        $this->assertEquals(3, $course_array['counts']['homework']);
        $this->assertEquals(2, $course_array['counts']['test']);
    }

    public function testGetCourseListForNormalUser() {
        $otherUserLogin = self::login($this->other_identity);
        $response = \OmegaUp\Controllers\Course::apiListCourses(new \OmegaUp\Request([
            'auth_token' => $otherUserLogin->auth_token,
        ]));

        $this->assertArrayHasKey('admin', $response);
        $this->assertArrayHasKey('student', $response);
        $studentCourses = array_filter($response['student'], function ($course) {
            return !boolval($course['public']);
        });
        $this->assertEquals(1, count($studentCourses));
        $course_array = $response['student'][0];
        \OmegaUp\Validators::validateNumber(
            $course_array['finish_time'],
            'finish_time'
        );
        $this->assertEquals(3, $course_array['counts']['homework']);
        $this->assertEquals(2, $course_array['counts']['test']);
    }
}
