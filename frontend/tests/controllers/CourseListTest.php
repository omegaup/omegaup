<?php

/**
 *
 * @author pablo
 */

class CourseListTest extends \OmegaUp\Test\ControllerTestCase {
    public function setUp(): void {
        parent::setUp();
        foreach (range(0, 1) as $course) {
            $publicCourseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
                /*$admin=*/                null,
                /*$adminLogin=*/null,
                \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC
            );
            $this->courseAliases[] = $publicCourseData['course_alias'];
        }
        $privateCourseData = \OmegaUp\Test\Factories\Course::createCourseWithNAssignmentsPerType(
            ['homework' => 3, 'test' => 2]
        );
        $this->courseAliases[] = $privateCourseData['course_alias'];

        $this->adminUser = $privateCourseData['admin'];
        [
            'identity' => $this->identity,
        ] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $privateCourseData,
            $this->identity
        );

        // This course shouldn't affect all the tests as it won't be listed
        $publicArchivedCourseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            /*$admin=*/            null,
            /*$adminLogin=*/ null,
            \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC
        );
        $archivedCourse = \OmegaUp\DAO\Courses::getByPK(
            $publicArchivedCourseData['course']->course_id
        );
        $archivedCourse->archived = 1;
        \OmegaUp\DAO\Courses::update($archivedCourse);
        $this->courseAliases[] = $publicArchivedCourseData['course_alias'];
    }

    protected $adminUser;
    protected $identity;
    protected $courseAliases;

    public function testGetCourseForAdminUser() {
        // Call the details API
        $adminLogin = self::login($this->adminUser);
        $response = \OmegaUp\Controllers\Course::apiListCourses(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
            ])
        );

        $this->assertArrayHasKey('admin', $response);
        $this->assertArrayHasKey('student', $response);

        $this->assertEquals(1, count($response['admin']));
        $course_array = $response['admin'][0];
        \OmegaUp\Validators::validateNumber(
            $course_array['finish_time']->time,
            'finish_time'
        );
        $this->assertEquals(3, $course_array['counts']['homework']);
        $this->assertEquals(2, $course_array['counts']['test']);
    }

    public function testGetCourseListForNormalUser() {
        $userLogin = self::login($this->identity);
        $response = \OmegaUp\Controllers\Course::apiListCourses(
            new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
            ])
        );

        $this->assertArrayHasKey('admin', $response);
        $this->assertArrayHasKey('student', $response);
        $studentCourses = array_filter(
            $response['student'],
            fn ($course) => $course['admission_mode'] !== \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC
        );
        $this->assertEquals(1, count($studentCourses));
        $course_array = $response['student'][0];
        \OmegaUp\Validators::validateNumber(
            $course_array['finish_time']->time,
            'finish_time'
        );
        $this->assertEquals(3, $course_array['counts']['homework']);
        $this->assertEquals(2, $course_array['counts']['test']);
    }

    public function testGetCourseListForSmarty() {
        $userLogin = self::login($this->identity);

        // Public courses are visible in student courses list when users were
        // explicitly invited or users join the public course by themselves
        $this->assertNumberOfCoursesByType(
            $userLogin,
            /*$numberOfStudentCourses=*/1,
            /*$numberOfPublicCourses=*/2
        );

        // User joins to one of the public courses
        \OmegaUp\Controllers\Course::apiAddStudent(new \OmegaUp\Request([
            'auth_token' => $userLogin->auth_token,
            'usernameOrEmail' => $this->identity->username,
            'course_alias' => $this->courseAliases[0],
        ]));

        $this->assertNumberOfCoursesByType(
            $userLogin,
            /*$numberOfStudentCourses=*/2,
            /*$numberOfPublicCourses=*/2
        );
    }

    private function assertNumberOfCoursesByType(
        \OmegaUp\Test\ScopedLoginToken $userLogin,
        int $numberOfStudentCourses,
        int $numberOfPublicCourses
    ) {
        $response = \OmegaUp\Controllers\Course::getCourseSummaryListDetailsForSmarty(
            new \OmegaUp\Request([
                'auth_token' => $userLogin->auth_token,
            ])
        )['smartyProperties']['payload']['courses'];
        $currentStudentCourses = $response['student']['filteredCourses']['current']['courses'];
        $currentPublicCourses = $response['public']['filteredCourses']['current']['courses'];
        $this->assertArrayHasKey('student', $response);
        $this->assertArrayHasKey('public', $response);

        // Asserts user has been invited to a private course
        $this->assertCount($numberOfStudentCourses, $currentStudentCourses);
        // Asserts there are two public courses
        $this->assertCount($numberOfPublicCourses, $currentPublicCourses);
    }
}
