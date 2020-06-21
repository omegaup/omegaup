<?php

/**
 * @author juan.pablo
 */
class AssignmentUpdateTest extends \OmegaUp\Test\ControllerTestCase {
    public function testAssignmentRemove() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithAssignments(
            /*$numberOfAssignments=*/5
        );

        $login = self::login($courseData['admin']);

        $response = \OmegaUp\Controllers\Course::apiListAssignments(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'course_alias' => $courseData['course_alias']
            ])
        );
        $this->assertCount(5, $response['assignments']);

        \OmegaUp\Controllers\Course::apiRemoveAssignment(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'assignment_alias' => $courseData['assignment_aliases'][0],
                'course_alias' => $courseData['course_alias'],
            ])
        );

        $response = \OmegaUp\Controllers\Course::apiListAssignments(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'course_alias' => $courseData['course_alias']
            ])
        );
        $this->assertCount(4, $response['assignments']);
    }

    public function testAssignmentRemoveWithSubmittedRuns() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createUser();

        $login = self::login($admin);

        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $admin,
            $login
        );

        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Add the problem to the assignment
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseAlias,
            $assignmentAlias,
            [$problemData]
        );
        // Create our participant
        [
            'identity' => $participant,
        ] = \OmegaUp\Test\Factories\User::createUser();

        // Add student to course
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $participant
        );

        // Create a run for assignment
        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemData,
            $courseData,
            $participant
        );

        // Grade the run
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $login = self::login($admin);

        try {
            \OmegaUp\Controllers\Course::apiRemoveAssignment(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'assignment_alias' => $assignmentAlias,
                    'course_alias' => $courseAlias,
                ])
            );
            $this->fail('Should have thrown exception.');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertEquals(
                'courseUpdateAlreadyHasRuns',
                $e->getMessage()
            );
        }
    }
}
