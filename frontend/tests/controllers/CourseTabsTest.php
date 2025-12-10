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
            requestsUserInformation: 'no',
            showScoreboard: 'false',
            courseDuration: null,
        );

        $recommendedCourse = \OmegaUp\DAO\Courses::getByAlias(
            $courseData['course_alias']
        );
        $recommendedCourse->recommended = 1;
        \OmegaUp\DAO\Courses::update($recommendedCourse);
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
            $response['templateProperties']['payload']['courses']['public']
        );
        $this->assertSame(
            $coursesAliases['public'][0],
            $response['templateProperties']['payload']['courses']['public'][0]['alias']
        );
        $this->assertFalse(
            $response['templateProperties']['payload']['courses']['public'][0]['alreadyStarted']
        );
    }

    public function testAuthenticatedUserTabs() {
        $coursesAliases = [
            'public' => [],
            'enrolled' => [],
            'finished' => [],
        ];

        // Create three public courses and a private course:
        // - First one with 1 student, 1 lesson and unlimited duration => for public and enrolled tabs
        // - Second one is a public course with no students
        // - Third one is an archived course => not listed in tabs
        // - The private course, the user completes totally => for finished tab
        $courseData = \OmegaUp\Test\Factories\Course::createCourse(
            null,
            null,
            \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC,
            requestsUserInformation: 'no',
            showScoreboard: 'false',
            courseDuration: null
        );
        $recommendedCourse = \OmegaUp\DAO\Courses::getByAlias(
            $courseData['course_alias']
        );
        $recommendedCourse->recommended = 1;
        \OmegaUp\DAO\Courses::update($recommendedCourse);
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
            requestsUserInformation: 'no',
            showScoreboard: 'false',
            courseDuration: null,
        );
        $recommendedCourse = \OmegaUp\DAO\Courses::getByAlias(
            $courseData['course_alias']
        );
        $recommendedCourse->recommended = 1;
        \OmegaUp\DAO\Courses::update($recommendedCourse);
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

        // Now add the first private course and make the student complete it
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
            2,
            $response['templateProperties']['payload']['courses']['public']
        );
        $this->assertSame(
            $coursesAliases['public'][0],
            $response['templateProperties']['payload']['courses']['public'][0]['alias']
        );
        $this->assertTrue(
            $response['templateProperties']['payload']['courses']['public'][0]['alreadyStarted']
        );
        $this->assertSame(
            $coursesAliases['public'][1],
            $response['templateProperties']['payload']['courses']['public'][1]['alias']
        );
        $this->assertFalse(
            $response['templateProperties']['payload']['courses']['public'][1]['alreadyStarted']
        );
        $this->assertSame(
            $coursesAliases['enrolled'][0],
            $response['templateProperties']['payload']['courses']['enrolled'][0]['alias']
        );
        $this->assertSame(
            $coursesAliases['finished'][0],
            $response['templateProperties']['payload']['courses']['finished'][0]['alias']
        );
    }

    public function testEnrolledCourseWithOneFinishedAssignment() {
        // Create a course with two assignments
        // Make the student solve one of the assignments and verify
        // if the course is not marked as finished
        [ 'identity' => $student ] = \OmegaUp\Test\Factories\User::createUser();
        // Finally add the second private course and make the student complete just one assignment
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $student
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
            $student
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $adminLogin = self::login($courseData['admin']);
        $extraAssignmentAlias = 'extra_assignment';
        \OmegaUp\Controllers\Course::apiCreateAssignment(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'name' => \OmegaUp\Test\Utils::createRandomString(),
                'alias' => $extraAssignmentAlias,
                'description' => \OmegaUp\Test\Utils::createRandomString(),
                'start_time' => (\OmegaUp\Time::get()),
                'finish_time' => (\OmegaUp\Time::get() + 120),
                'course_alias' => $courseData['course_alias'],
                'assignment_type' => 'homework'
            ])
        );
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $adminLogin,
            $courseData['course_alias'],
            $extraAssignmentAlias,
            [ $problemData ]
        );

        $response = \OmegaUp\Controllers\Course::getCourseTabsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => self::login($student)->auth_token,
            ])
        );

        $this->assertEmpty(
            $response['templateProperties']['payload']['courses']['public']
        );
        $this->assertEmpty(
            $response['templateProperties']['payload']['courses']['enrolled']
        );
        $this->assertCount(
            1,
            $response['templateProperties']['payload']['courses']['finished']
        );
        $this->assertSame(
            $courseData['course_alias'],
            $response['templateProperties']['payload']['courses']['finished'][0]['alias']
        );
    }
}
