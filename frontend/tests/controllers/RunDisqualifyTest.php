<?php
/**
 * Unittest for disqualifying run
 */
class RunDisqualifyTest extends \OmegaUp\Test\ControllerTestCase {
    public function proveDisqualifyProvider(): array {
        return [
            'teaching assistant can disqualify in public course' => [
                'public',
                'apiAddTeachingAssistant',
                'isTeachingAssistant',
            ],
            'teaching assistant can disqualify in private course' => [
                'private',
                'apiAddTeachingAssistant',
                'isTeachingAssistant',
            ],
            'admin can disqualify in public course' => [
                'public',
                'apiAddAdmin',
                'isCourseAdmin',
            ],
            'admin can disqualify in private course' => [
                'private',
                'apiAddAdmin',
                'isCourseAdmin',
            ]
        ];
    }
    /**
     * @dataProvider proveDisqualifyProvider
     */
    public function testDisqualifyByAdminAndTeachingAssistant(
        string $admissionMode,
        string $nameApi,
        string $role
    ) {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a course
        $courseData = \OmegaUp\Test\Factories\Course::createCourseWithOneAssignment(
            admissionMode: $admissionMode
        );
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

        // Create student
        ['identity' => $student] = \OmegaUp\Test\Factories\User::createUser();

        // Add student to course
        \OmegaUp\Test\Factories\Course::addStudentToCourse(
            $courseData,
            $student
        );

        // Create a run for assignment
        $runData = \OmegaUp\Test\Factories\Run::createCourseAssignmentRun(
            $problemData,
            $courseData,
            $student
        );

        // Grade the run
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        // Create user
        ['identity' => $user] = \OmegaUp\Test\Factories\User::createUser();

        // Login
        $adminLogin = self::login($courseData['admin']);

        \OmegaUp\Controllers\Course::$nameApi(
            new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
                'usernameOrEmail' => $user->username,
                'course_alias' => $courseData['course_alias'],
            ])
        );

        $userLogin = self::login($user);
        $course = \OmegaUp\DAO\Courses::getByAlias(
            $courseData['course_alias']
        );

        $this->assertTrue(
            \OmegaUp\Authorization::$role(
                $user,
                $course
            )
        );

        // Call API
        $response = \OmegaUp\Controllers\Run::apiDisqualify(new \OmegaUp\Request([
            'auth_token' => $userLogin->auth_token,
            'run_alias' => $runData['response']['guid']
        ]));

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
