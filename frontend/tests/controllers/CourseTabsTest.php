<?php

class CourseTabsTest extends \OmegaUp\Test\ControllerTestCase {
    public function testPublicCoursesTab() {
        $coursesAliases = [
            'general' => [],
            'enrolled' => [],
            'finished' => [],
        ];

        // Create two public courses:
        // - First one with 1 student, 1 lesson and unlimited duration
        // - Second one is an archived course
        $courseData = \OmegaUp\Test\Factories\Course::createCourse(
            null,
            null,
            \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC,
            /* $requestsUserInformation */ 'no',
            /* $showScoreboard */'false',
            /* $courseDuration= */ null,
        );
        $admin = $courseData['admin'];
        [ 'identity' => $identity ] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $identity
        );

        $adminLogin = self::login($admin);
        \OmegaUp\Controllers\Course::apiCreateAssignment(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'name' => 'Test assignment',
                'alias' => \OmegaUp\Test\Utils::createRandomString(),
                'description' => \OmegaUp\Test\Utils::createRandomString(),
                'start_time' => (\OmegaUp\Time::get() + 60),
                'unlimited_duration' => true,
                'course_alias' => $courseData['course_alias'],
                'assignment_type' => 'lesson'
            ])
        );
        $coursesAliases['general'][] = $courseData['course_alias'];

        $courseData = \OmegaUp\Test\Factories\Course::createCourse(
            null,
            null,
            \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC,
        );
        $archivedCourse = \OmegaUp\DAO\Courses::getByAlias(
            $courseData['course_alias']
        );
        $archivedCourse->archived = 1;
        \OmegaUp\DAO\Courses::update($archivedCourse);

        $response = \OmegaUp\Controllers\Course::getCourseTabsForTypeScript(
            new \OmegaUp\Request()
        );
        $this->assertCount(
            1,
            $response['smartyProperties']['payload']['courses']['general']
        );
        $this->assertEquals(
            $coursesAliases['general'][0],
            $response['smartyProperties']['payload']['courses']['general'][0]['alias']
        );
    }
}
