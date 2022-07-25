<?php

use OmegaUp\Controllers\Course;

/**
 * Test administrative tasks for teaching assistant team
 */
class UserTeachingAssistantTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Basic test for users with teaching assistant role
     */
    public function testUserHasTeachingAssistantRole() {
        ['identity' => $teachingAssistantIdentity] = \OmegaUp\Test\Factories\User::createTeachingAssistantUser();
        ['identity' => $mentorIdentity] = \OmegaUp\Test\Factories\User::createMentorIdentity();

        // Asserting that user belongs to the teaching assistant group
        $this->assertTrue(
            \OmegaUp\Authorization::isTeachingAssistant(
                $teachingAssistantIdentity
            )
        );

        // Asserting that user doesn't belong to the teaching assistant group
        $this->assertFalse(
            \OmegaUp\Authorization::isTeachingAssistant(
                $mentorIdentity
            )
        );
    }

    public function testCanCreatePublicCourseTeachingAssistant() {
        $publicCourseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            admissionMode: \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC
        );
        ['identity' => $teachingAssistantIdentity] = \OmegaUp\Test\Factories\User::createTeachingAssistantUser();

        $teachingAssistantLogin = self::login($teachingAssistantIdentity);

        $publicCourses = \OmegaUp\Controllers\Course::getCourseMineDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $teachingAssistantLogin->auth_token,
            ])
        )['templateProperties']['payload']['courses']['admin']['filteredCourses']['teachingAssistant']['courses'];
        ;

        $this->assertCount(1, $publicCourses);
        $this->assertEquals(
            $publicCourseData['course']->alias,
            $publicCourses[0]['alias']
        );
    }
}
