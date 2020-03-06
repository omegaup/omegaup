<?php

/**
 * A course might require registration to participate on it.
 *
 * @author juan.pablo@omegaup.com
 */
class CourseRegistrationTest extends \OmegaUp\Test\ControllerTestCase {
    private static $curator = null;

    public static function setUpBeforeClass() {
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

    public function testCreateCourseWithRegistrationMode() {
        $adminLogin = self::login(self::$curator);
        $school = SchoolsFactory::createSchool()['school'];
        $alias = \OmegaUp\Test\Utils::createRandomString();

        $response = \OmegaUp\Controllers\Course::apiCreate(
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

        // Get updated course
        $course = \OmegaUp\DAO\Courses::getByAlias($alias);
        $this->assertEquals($course->admission_mode, 'registration');
    }
}
