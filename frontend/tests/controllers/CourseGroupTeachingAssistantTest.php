<?php

/**
 * Test administrative tasks for teaching assistant team
 */
class CourseGroupTeachingAssistantTest extends \OmegaUp\Test\ControllerTestCase {
    public function testAddGroupTeachingAssistant() {
        // Create a course
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();

        // create admin
        ['identity' => $adminUser] = \OmegaUp\Test\Factories\User::createAdminUser();

        // login admin
        $adminLogin = self::login($adminUser);

        // create normal user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();

        $groupData = \OmegaUp\Test\Factories\Groups::createGroup();
        \OmegaUp\Test\Factories\Groups::addUserToGroup($groupData, $identity);
        \OmegaUp\Test\Factories\Groups::addUserToGroup($groupData, $identity2);

        // admin is able to add a teaching assistant
        \OmegaUp\Controllers\Course::apiAddGroupTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'group' => $groupData['request']['alias'],
                'course_alias' => $courseData['course_alias'],
            ])
        );

        // login user
        $userLogin = self::login($identity);

        $this->assertTrue(
            \OmegaUp\Authorization::isMemberOfAnyGroup(
                $identity,
                [$groupData['group']]
            )
        );

        // teaching assistant can't add another teaching assistant
        try {
            \OmegaUp\Controllers\Course::apiAddGroupTeachingAssistant(
                new \OmegaUp\Request([
                    'auth_token' => $userLogin->auth_token,
                    'group' => $groupData['request']['alias'],
                    'course_alias' => $courseData['course_alias'],
                ])
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    public function testRemoveGroupTeachingAssistant() {
        // Create a course
        $courseData = \OmegaUp\Test\Factories\Course::createCourse();

        // create admin
        ['identity' => $adminUser] = \OmegaUp\Test\Factories\User::createAdminUser();

        // login admin
        $adminLogin = self::login($adminUser);

        // create a normal user
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();

        $groupData = \OmegaUp\Test\Factories\Groups::createGroup();
        \OmegaUp\Test\Factories\Groups::addUserToGroup($groupData, $identity);
        \OmegaUp\Test\Factories\Groups::addUserToGroup($groupData, $identity2);

        // admin add user like teaching assistant
        \OmegaUp\Controllers\Course::apiAddGroupTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'group' => $groupData['request']['alias'],
                'course_alias' => $courseData['course_alias'],
            ])
        );

        $admins = \OmegaUp\Controllers\Course::apiAdmins(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        $groupTeachingAssistants = array_map(
            fn ($admin): string => $admin['alias'],
            $admins['group_teaching_assistants']
        );

        $this->assertContains(
            $groupData['request']['alias'],
            $groupTeachingAssistants
        );

        \OmegaUp\Controllers\Course::apiRemoveGroupTeachingAssistant(new \OmegaUp\Request([
            'auth_token' => $adminLogin->auth_token,
            'group' => $groupData['request']['alias'],
            'course_alias' => $courseData['course_alias'],
        ]));

        $admins = \OmegaUp\Controllers\Course::apiAdmins(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        $groupTeachingAssistants = array_map(
            fn ($admin): string => $admin['alias'],
            $admins['group_teaching_assistants']
        );

        $this->assertNotContains(
            $groupData['request']['alias'],
            $groupTeachingAssistants
        );
    }
}
