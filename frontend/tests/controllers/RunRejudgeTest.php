<?php
/**
 * Description of RunRejudgeTest
 */

class RunRejudgeTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Basic test of rerun
     */
    public function testRejudgeWithoutCompileError() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create our contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create a run
        $runData = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );

        // Grade the run
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $detourGrader = new \OmegaUp\Test\ScopedGraderDetour();

        // Build request
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'run_alias' => $runData['response']['guid'],
        ]);

        // Call API
        $response = \OmegaUp\Controllers\Run::apiRejudge($r);

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(1, $detourGrader->getGraderCallCount());
    }

    public function testRejudgeWithoutCompileErrorByTeachingAssistant() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a course
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment();
        $courseAlias = $courseData['course_alias'];
        $assignmentAlias = $courseData['assignment_alias'];

        // Login
        $adminLogin = self::login($courseData['admin']);

        // Add the problem to the assignment
        \OmegaUp\Test\Factories\Course::addProblemsToAssignment(
            $adminLogin,
            $courseAlias,
            $assignmentAlias,
            [$problemData]
        );

        // Create our participant
        ['identity' => $participant] = \OmegaUp\Test\Factories\User::createUser();

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

        // Create user
        ['identity' => $teachingAssistantUser] = \OmegaUp\Test\Factories\User::createUser();

        // Login
        $adminLogin = self::login($courseData['admin']);

        // add user like teaching assistant
        \OmegaUp\Controllers\Course::apiAddTeachingAssistant(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'usernameOrEmail' => $teachingAssistantUser->username,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        $course = \OmegaUp\DAO\Courses::getByAlias(
            $courseData['course_alias']
        );

        $this->assertTrue(
            \OmegaUp\Authorization::isTeachingAssistant(
                $teachingAssistantUser,
                $course
            )
        );

        // login teaching assistant
        $teachingAssistantLogin = self::login($teachingAssistantUser);

        $detourGrader = new \OmegaUp\Test\ScopedGraderDetour();

        // Build request
        $r = new \OmegaUp\Request([
            'auth_token' => $teachingAssistantLogin->auth_token,
            'run_alias' => $runData['response']['guid'],
        ]);

        // Call API
        $response = \OmegaUp\Controllers\Run::apiRejudge($r);

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals(1, $detourGrader->getGraderCallCount());
    }
}
