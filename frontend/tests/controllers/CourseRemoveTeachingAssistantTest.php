<?php

/**
 * Test administrative tasks for teaching assistant team
 */
class CourseRemoveTeachingAssistantTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Tests remove teaching assistant
     */
    public function testRemoveTeachingAssistant() {
        // Create a course
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();

        // create admin
        ['identity' => $adminUser] = \OmegaUp\Test\Factories\User::createAdminUser();

        // login admin
        $adminLogin = self::login($adminUser);

        // create a normal user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // admin add user like teaching assistant
        \OmegaUp\Controllers\Course::apiAddTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'usernameOrEmail' => $identity->username,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        $admins = \OmegaUp\Controllers\Course::apiAdmins(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        $teachingAssistants = array_map(
            fn ($admin): string => $admin['username'],
            $admins['teaching_assistants']
        );

        $this->assertContains($identity->username, $teachingAssistants);

        \OmegaUp\Controllers\Course::apiRemoveTeachingAssistant(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'usernameOrEmail' => $identity->username,
            'course_alias' => $courseData['course_alias'],
        ]));

        $admins = \OmegaUp\Controllers\Course::apiAdmins(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        $teachingAssistants = array_map(
            fn ($admin): string => $admin['username'],
            $admins['teaching_assistants']
        );
        $this->assertNotContains($identity->username, $teachingAssistants);
    }

    public function testCanTeachingAssistantRemoveOtherTeachingAssistant() {
        // Create a course
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();

        // create admin
        ['identity' => $adminUser] = \OmegaUp\Test\Factories\User::createAdminUser();

        // login admin
        $adminLogin = self::login($adminUser);

        // create 2 normal users
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();

        // admin add users like teaching assistants
        \OmegaUp\Controllers\Course::apiAddTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'usernameOrEmail' => $identity->username,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        \OmegaUp\Controllers\Course::apiAddTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'usernameOrEmail' => $identity2->username,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        $admins = \OmegaUp\Controllers\Course::apiAdmins(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        $teachingAssistants = array_map(
            fn ($admin): string => $admin['username'],
            $admins['teaching_assistants']
        );
        $this->assertContains($identity->username, $teachingAssistants);
        $this->assertContains($identity2->username, $teachingAssistants);

        //login teaching assistant
        $teachingAssistantLogin = self::login($identity);

        // teaching assistant can't be delete by other teaching assistant
        try {
            \OmegaUp\Controllers\Course::apiRemoveTeachingAssistant(new \OmegaUp\Request([
                'auth_token' => $teachingAssistantLogin->auth_token,
                'usernameOrEmail' => $identity2->username,
                'course_alias' => $courseData['course_alias'],
            ]));
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }
}
