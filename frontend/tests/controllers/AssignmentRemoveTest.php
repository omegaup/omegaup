<?php

/**
 * @author juan.pablo
 */
class AssignmentRemoveTest extends \OmegaUp\Test\ControllerTestCase {
    private static $admin = null;
    private static $participant = null;
    private static $login = null;
    private static $loginParticipant = null;
    private static $courseData = null;
    private static $problemData = null;

    public function setUp(): void {
        parent::setUp();

        ['identity' => self::$admin] = \OmegaUp\Test\Factories\User::createUser();
        self::$login = self::login(self::$admin);

        self::$courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            self::$admin,
            self::$login
        );

        self::$problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            self::$login,
            self::$courseData['course_alias'],
            self::$courseData['assignment_alias'],
            [self::$problemData]
        );

        [
            'identity' => self::$participant,
        ] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            self::$courseData,
            self::$participant
        );

        self::$loginParticipant = self::login(self::$participant);
    }

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

    /**
     * A participant joins the assignment, and then, this assignment is removed
     * by admin
     */
    public function testAssignmentRemoveWithAccessLog() {
        $courseAlias = self::$courseData['course_alias'];
        $assignmentAlias = self::$courseData['assignment_alias'];

        $getAssignmentResponse = \OmegaUp\Controllers\Course::apiAssignmentDetails(
            new \OmegaUp\Request([
                'auth_token' => self::$loginParticipant->auth_token,
                'course' => $courseAlias,
                'assignment' => $assignmentAlias,
            ])
        );

        self::$login = self::login(self::$admin);

        \OmegaUp\Controllers\Course::apiRemoveAssignment(
            new \OmegaUp\Request([
                'auth_token' => self::$login->auth_token,
                'assignment_alias' => $assignmentAlias,
                'course_alias' => $courseAlias,
            ])
        );

        $response = \OmegaUp\Controllers\Course::apiListAssignments(
            new \OmegaUp\Request([
                'auth_token' => self::$login->auth_token,
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
        $courseAlias = self::$courseData['course_alias'];
        $assignmentAlias = self::$courseData['assignment_alias'];

        $getAssignmentResponse = \OmegaUp\Controllers\Course::apiAssignmentDetails(
            new \OmegaUp\Request([
                'auth_token' => self::$loginParticipant->auth_token,
                'course' => $courseAlias,
                'assignment' => $assignmentAlias,
            ])
        );

        \OmegaUp\Test\Factories\Course::openProblemInCourseAssignment(
            self::$courseData,
            self::$problemData,
            self::$participant
        );

        self::$login = self::login(self::$admin);

        \OmegaUp\Controllers\Course::apiRemoveAssignment(
            new \OmegaUp\Request([
                'auth_token' => self::$login->auth_token,
                'assignment_alias' => $assignmentAlias,
                'course_alias' => $courseAlias,
            ])
        );

        $response = \OmegaUp\Controllers\Course::apiListAssignments(
            new \OmegaUp\Request([
                'auth_token' => self::$login->auth_token,
                'course_alias' => $courseAlias
            ])
        );
        $this->assertEmpty($response['assignments']);
    }

    public function testAssignmentRemoveWithSubmittedRuns() {
        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            self::$problemData,
            self::$courseData,
            self::$participant
        );

        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        self::$login = self::login(self::$admin);

        try {
            \OmegaUp\Controllers\Course::apiRemoveAssignment(
                new \OmegaUp\Request([
                    'auth_token' => self::$login->auth_token,
                    'assignment_alias' => self::$courseData['assignment_alias'],
                    'course_alias' => self::$courseData['course_alias'],
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
