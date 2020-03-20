<?php

/**
 * A course might require registration to participate on it.
 *
 * @author juan.pablo@omegaup.com
 */
class CourseRegistrationTest extends \OmegaUp\Test\ControllerTestCase {
    private static $curator = null;

    public static function setUpBeforeClass(): void {
        parent::setUpBeforeClass();

        $curatorGroup = \OmegaUp\DAO\Groups::findByAlias(
            \OmegaUp\Authorization::COURSE_CURATOR_GROUP_ALIAS
        );

        [
            'identity' => self::$curator,
        ] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\DAO\GroupsIdentities::create(
            new \OmegaUp\DAO\VO\GroupsIdentities([
                'group_id' => $curatorGroup->group_id,
                'identity_id' => self::$curator->identity_id,
            ])
        );
    }

    private static function createCourseWithRegistrationMode() {
        $adminLogin = self::login(self::$curator);
        $school = SchoolsFactory::createSchool()['school'];
        $alias = \OmegaUp\Test\Utils::createRandomString();

        \OmegaUp\Controllers\Course::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'name' => \OmegaUp\Test\Utils::createRandomString(),
                'alias' => $alias,
                'description' => \OmegaUp\Test\Utils::createRandomString(),
                'start_time' => (\OmegaUp\Time::get() + 60),
                'finish_time' => (\OmegaUp\Time::get() + 120),
                'school_id' => $school->school_id,
            ])
        );

        $course = \OmegaUp\DAO\Courses::getByAlias($alias);

        // Update to registration the admission mode
        \OmegaUp\Controllers\Course::apiUpdate(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'course_alias' => $alias,
            'name' => $course->name,
            'description' => $course->description,
            'alias' => $course->alias,
            'admission_mode' => \OmegaUp\Controllers\Course::ADMISSION_MODE_REGISTRATION,
        ]));

        return [
            'course' => $course,
            'adminLogin' => $adminLogin,
        ];
    }

    /**
     * @param list<\OmegaUp\DAO\VO\Courses> $courses
     */
    private function assertCourseIsPresentInArray($courses, string $alias) {
        foreach ($courses as $course) {
            if ($course['alias'] === $alias) {
                return true;
            }
        }
        return false;
    }

    public function testCourseIsPresentInStudentList() {
        $course = self::createCourseWithRegistrationMode()['course'];
        ['identity' => $student] = \OmegaUp\Test\Factories\User::createUser();
        $studentLogin = self::login($student);

        // Course should appear in public course list for students
        $coursesList = \OmegaUp\Controllers\Course::apiListCourses(
            new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
            ])
        );

        $this->assertArrayContainsWithPredicate(
            $coursesList['student'],
            function ($studentCourse) use ($course): bool {
                return $studentCourse['alias'] === $course->alias;
            }
        );
    }

    public function testRequestIsShownInIntroDetails() {
        [
            'course' => $course,
            'adminLogin' => $adminLogin,
        ] = self::createCourseWithRegistrationMode();
        ['identity' => $student] = \OmegaUp\Test\Factories\User::createUser();
        $studentLogin = self::login($student);

        $response = \OmegaUp\Controllers\Course::apiIntroDetails(
            new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'course_alias' => $course->alias,
            ])
        );

        // In courses with registration we should be able to see all the user
        // registration keys, except userRegistrationAccepted, because user
        // has not been accepted yet
        $this->assertArrayHasKey('userRegistrationRequested', $response);
        $this->assertArrayHasKey('userRegistrationAnswered', $response);
        $this->assertArrayNotHasKey('userRegistrationAccepted', $response);

        // In a public or private course, user registration keys do not exist
        $response = \OmegaUp\Controllers\Course::apiCreate(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'name' => \OmegaUp\Test\Utils::createRandomString(),
                'alias' => \OmegaUp\Test\Utils::createRandomString(),
                'description' => \OmegaUp\Test\Utils::createRandomString(),
                'start_time' => (\OmegaUp\Time::get() + 60),
                'finish_time' => (\OmegaUp\Time::get() + 120)
            ])
        );

        $this->assertArrayNotHasKey('userRegistrationRequested', $response);
        $this->assertArrayNotHasKey('userRegistrationAnswered', $response);
        $this->assertArrayNotHasKey('userRegistrationAccepted', $response);
    }

    /**
     * Uers only can register into a course with registration mode
     */
    public function testRegisterForCourse() {
        $course = self::createCourseWithRegistrationMode()['course'];
        ['identity' => $student] = \OmegaUp\Test\Factories\User::createUser();
        $studentLogin = self::login($student);

        $response = \OmegaUp\Controllers\Course::apiRegisterForCourse(
            new \OmegaUp\Request([
                'auth_token' => $studentLogin->auth_token,
                'course_alias' => $course->alias,
            ])
        );

        $registration = \OmegaUp\DAO\CourseIdentityRequest::getByPK(
            $student->identity_id,
            $course->course_id
        );

        $this->assertNotEmpty($registration);
    }
}
