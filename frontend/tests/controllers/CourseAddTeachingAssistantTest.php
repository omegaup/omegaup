<?php

/**
 * Test administrative tasks for teaching assistant team
 */
class CourseAddTeachingAssistantTest extends \OmegaUp\Test\ControllerTestCase {
    public function testCanAddTeachingAssistantAnotherTeachingAssistant() {
        // Create a course
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();

        // create admin
        ['identity' => $adminUser] = \OmegaUp\Test\Factories\User::createAdminUser();

        // login admin
        $adminLogin = self::login($adminUser);

        // create normal user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // admin is able to add a teaching assistant
        \OmegaUp\Controllers\Course::apiAddTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'usernameOrEmail' => $identity->username,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        // login user
        $userLogin = self::login($identity);
        $course = \OmegaUp\DAO\Courses::getByAlias(
            $courseData['course_alias']
        );

        $this->assertTrue(
            \OmegaUp\Authorization::isTeachingAssistant(
                $identity,
                $course
            )
        );

        // create another normal user
        ['identity' => $identityUser2] = \OmegaUp\Test\Factories\User::createUser();

        // teaching assistant can't add another teaching assistant
        try {
            \OmegaUp\Controllers\Course::apiAddTeachingAssistant(
                new \OmegaUp\Request([
                    'auth_token' => $userLogin->auth_token,
                    'usernameOrEmail' => $identityUser2->username,
                    'course_alias' => $courseData['course_alias'],
                ])
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }
}
