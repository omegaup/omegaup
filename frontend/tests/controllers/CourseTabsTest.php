<?php

class CourseTabsTest extends \OmegaUp\Test\ControllerTestCase {
    public function testUnauthenticatedUserTabs() {
        $coursesAliases = [
            'public' => [],
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
        $coursesAliases['public'][] = $courseData['course_alias'];

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
            $response['smartyProperties']['payload']['courses']['public']
        );
        $this->assertEquals(
            $coursesAliases['public'][0],
            $response['smartyProperties']['payload']['courses']['public'][0]['alias']
        );
    }

    public function testAuthenticatedUserTabs() {
        $coursesAliases = [
            'public' => [],
            'enrolled' => [],
            'finished' => [],
        ];

        // Create two public courses and one private course:
        // - First one with 1 student, 1 lesson and unlimited duration => for public and enrolled tabs
        // - Second one is an archived course => not listed in tabs
        // - Third one the user completes totally => for finished tab
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
        $coursesAliases['enrolled'][] = $courseData['course_alias'];

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
        $coursesAliases['public'][] = $courseData['course_alias'];

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

        // Now add the third course and make the student complete it
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $identity
        );
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            self::login($courseData['admin']),
            $courseData['course_alias'],
            $courseData['assignment_alias'],
            [ $problemData ]
        );
        $runData = \OmegaUp\Test\Factories\Run::createAssignmentRun(
            $courseData['course_alias'],
            $courseData['assignment_alias'],
            $problemData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        $coursesAliases['finished'][] = $courseData['course_alias'];

        $response = \OmegaUp\Controllers\Course::getCourseTabsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => self::login($identity)->auth_token,
            ])
        );

        $this->assertCount(
            1,
            $response['smartyProperties']['payload']['courses']['public']
        );
        $this->assertEquals(
            $coursesAliases['public'][0],
            $response['smartyProperties']['payload']['courses']['public'][0]['alias']
        );
        $this->assertEquals(
            $coursesAliases['enrolled'][0],
            $response['smartyProperties']['payload']['courses']['enrolled'][0]['alias']
        );
        $this->assertEquals(
            $coursesAliases['finished'][0],
            $response['smartyProperties']['payload']['courses']['finished'][0]['alias']
        );
    }
}
