<?php

class AssignmentRemoveTest extends \OmegaUp\Test\ControllerTestCase {
    public function testAssignmentRemove() {
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithAssignments(
            /*$numberOfAssignments=*/            5
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

    /**
     * A participant joins the assignment, and then, this assignment is removed
     * by admin
     */
    public function testAssignmentRemoveWithAccessLog() {
        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($admin);

        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $admin,
            $login
        );

        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseData['course_alias'],
            $courseData['assignment_alias'],
            [$problemData]
        );

        ['identity' => $participant] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $participant
        );

        $loginParticipant = self::login($participant);

        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        $getAssignmentResponse = \OmegaUp\Controllers\Course::apiAssignmentDetails(
            new \OmegaUp\Request([
                'auth_token' => $loginParticipant->auth_token,
                'course' => $courseAlias,
                'assignment' => $assignmentAlias,
            ])
        );

        $login = self::login($admin);

        \OmegaUp\Controllers\Course::apiRemoveAssignment(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'assignment_alias' => $assignmentAlias,
                'course_alias' => $courseAlias,
            ])
        );

        $response = \OmegaUp\Controllers\Course::apiListAssignments(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'course_alias' => $courseAlias
            ])
        );
        $this->assertEmpty($response['assignments']);
    }

    /**
     * A participant joins the assignment, open a problem, and then, the
     * assignment is removed by admin
     */
    public function testAssignmentRemoveWithProblemOpened() {
        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($admin);

        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $admin,
            $login
        );

        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseData['course_alias'],
            $courseData['assignment_alias'],
            [$problemData]
        );

        ['identity' => $participant] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $participant
        );

        $loginParticipant = self::login($participant);

        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        $getAssignmentResponse = \OmegaUp\Controllers\Course::apiAssignmentDetails(
            new \OmegaUp\Request([
                'auth_token' => $loginParticipant->auth_token,
                'course' => $courseAlias,
                'assignment' => $assignmentAlias,
            ])
        );

        \OmegaUp\Test\Factories\Course::openProblemInCourseAssignment(
            $courseData,
            $problemData,
            $participant
        );

        $login = self::login($admin);

        \OmegaUp\Controllers\Course::apiRemoveAssignment(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'assignment_alias' => $assignmentAlias,
                'course_alias' => $courseAlias,
            ])
        );

        $response = \OmegaUp\Controllers\Course::apiListAssignments(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'course_alias' => $courseAlias
            ])
        );
        $this->assertEmpty($response['assignments']);
    }

    public function testAssignmentRemoveWithSubmittedRuns() {
        ['identity' => $admin] = \OmegaUp\Test\Factories\User::createUser();
        $login = self::login($admin);

        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            $admin,
            $login
        );

        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $login,
            $courseData['course_alias'],
            $courseData['assignment_alias'],
            [$problemData]
        );

        ['identity' => $participant] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $participant
        );

        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemData,
            $courseData,
            $participant
        );

        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $login = self::login($admin);

        try {
            \OmegaUp\Controllers\Course::apiRemoveAssignment(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'assignment_alias' => $courseData['assignment_alias'],
                    'course_alias' => $courseData['course_alias'],
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
