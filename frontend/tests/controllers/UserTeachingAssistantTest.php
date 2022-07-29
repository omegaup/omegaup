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
        // create public course
        $publicCourseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            admissionMode: \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC
        );

        // create teaching assistant
        ['identity' => $teachingAssistantIdentity] = \OmegaUp\Test\Factories\User::createTeachingAssistantUser();

        // login teaching assistant
        $teachingAssistantLogin = self::login($teachingAssistantIdentity);

        // get teaching assistant public courses
        $publicCourses = \OmegaUp\Controllers\Course::getCourseMineDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $teachingAssistantLogin->auth_token,
            ])
        )['templateProperties']['payload']['courses']['admin']['filteredCourses']['teachingAssistant']['courses'];
        ;

        // compare with assert
        $this->assertCount(1, $publicCourses);
        $this->assertEquals(
            $publicCourseData['course']->alias,
            $publicCourses[0]['alias']
        );
    }

    public function testCanTeachingAssistantViewSubmissions() {
        // create public course
        $publicCourseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            admissionMode: \OmegaUp\Controllers\Course::ADMISSION_MODE_PUBLIC
        );
        $courseAlias = $publicCourseData['course_alias'];
        $assignmentAlias = $publicCourseData['assignment_alias'];

        // create a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // create admin login
        $loginAdmin = self::login($publicCourseData['admin']);

        // Add the problem to the assignment
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $loginAdmin,
            $courseAlias,
            $assignmentAlias,
            [$problemData]
        );
        // Create student
        ['identity' => $student] = \OmegaUp\Test\Factories\User::createUser();

        // Add student to course
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $publicCourseData,
            $student
        );

        // Create a run for assignment
        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemData,
            $publicCourseData,
            $student
        );

        // Grade the run
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        // create teaching assistant
        ['identity' => $teachingAssistantIdentity] = \OmegaUp\Test\Factories\User::createTeachingAssistantUser();

        // login teaching assistant
        $teachingAssistantLogin = self::login($teachingAssistantIdentity);

        // get submissions that teaching assistant can view for the course
        $responseTeachingAssistant = \OmegaUp\Controllers\Course::apiRuns(new \OmegaUp\Request([
            'course_alias' => $publicCourseData['request']['course_alias'],
            'assignment_alias' => $publicCourseData['request']['alias'],
            'auth_token' => $teachingAssistantLogin->auth_token,
        ]));

        // create mentor
        ['identity' => $mentorIdentity] = \OmegaUp\Test\Factories\User::createMentorIdentity();

        // login mentor
        $mentorLogin = self::login($mentorIdentity);

        // compare with assert
        $this->assertEquals(1, $responseTeachingAssistant['totalRuns']);

        // teaching assistant will be able to view run details
        try {
            \OmegaUp\Controllers\Run::apiDetails(new \OmegaUp\Request([
                'problemset_id' => $publicCourseData['assignment']->problemset_id,
                'run_alias' => $responseTeachingAssistant['runs'][0]['guid'],
                'auth_token' => $teachingAssistantLogin->auth_token,
            ]));
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }

        try {
            \OmegaUp\Controllers\Course::apiRuns(new \OmegaUp\Request([
                'course_alias' => $publicCourseData['request']['course_alias'],
                'assignment_alias' => $publicCourseData['request']['alias'],
                'auth_token' => $mentorLogin->auth_token,
            ]));
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }
}
