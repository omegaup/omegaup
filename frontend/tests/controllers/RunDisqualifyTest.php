<?php
/**
 * Unittest for disqualifying run
 */
class RunDisqualifyTest extends \OmegaUp\Test\ControllerTestCase {
    public function testDisqualifyByAdmin() {
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

        // Create a new run
        $runData = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );

        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'run_alias' => $runData['response']['guid']
        ]);

        // Call API
        $response = \OmegaUp\Controllers\Run::apiDisqualify($r);

        $this->assertEquals('ok', $response['status']);
    }

    public function testDisqualifyByTeachingAssistant() {
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

        // login teaching assistant
        $teachingAssistantLogin = self::login($teachingAssistantUser);
        $course = \OmegaUp\DAO\Courses::getByAlias(
            $courseData['course_alias']
        );

        $this->assertTrue(
            \OmegaUp\Authorization::isTeachingAssistant(
                $teachingAssistantUser,
                $course
            )
        );

        $r = new \OmegaUp\Request([
            'auth_token' => $teachingAssistantLogin->auth_token,
            'run_alias' => $runData['response']['guid']
        ]);

        // Call API
        $response = \OmegaUp\Controllers\Run::apiDisqualify($r);

        $this->assertEquals('ok', $response['status']);
    }

    public function testDisqualifyScoreboard() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create our contestants
        ['identity' => $identity1] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();

        // Create new runs
        $runData1 = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity1
        );
        $runData2 = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity2
        );

        \OmegaUp\Test\Factories\Run::gradeRun($runData1);
        \OmegaUp\Test\Factories\Run::gradeRun($runData2);

        // Disqualify run by contestant1
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'run_alias' => $runData1['response']['guid']
        ]);
        \OmegaUp\Controllers\Run::apiDisqualify($r);

        // Check scoreboard
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]);
        $response = \OmegaUp\Controllers\Contest::apiScoreboard($r);

        // Contestant 2 should not be changed
        $this->assertEquals(
            $identity2->username,
            $response['ranking'][0]['username']
        );
        $this->assertEquals(
            100,
            $response['ranking'][0]['problems'][0]['points']
        );
        $this->assertEquals(1, $response['ranking'][0]['problems'][0]['runs']);
        // Contestant 1 should be changed
        $this->assertEquals(
            $identity1->username,
            $response['ranking'][1]['username']
        );
        $this->assertEquals(
            0,
            $response['ranking'][1]['problems'][0]['points']
        );
        $this->assertEquals(0, $response['ranking'][1]['problems'][0]['runs']);
    }
}
