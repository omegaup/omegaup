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

        $this->assertSame('ok', $response['status']);
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
        $this->assertSame(
            $identity2->username,
            $response['ranking'][0]['username']
        );
        $this->assertSame(
            100.0,
            $response['ranking'][0]['problems'][0]['points']
        );
        $this->assertSame(1, $response['ranking'][0]['problems'][0]['runs']);
        // Contestant 1 should be changed
        $this->assertSame(
            $identity1->username,
            $response['ranking'][1]['username']
        );
        $this->assertSame(
            0.0,
            $response['ranking'][1]['problems'][0]['points']
        );
        $this->assertSame(0, $response['ranking'][1]['problems'][0]['runs']);
    }

    public function disqualifyTypeProvider(): array {
        return [
            ['ByGuid', 'user_3', 1],
            ['ByUserAndProblem', 'user_1', 3],
            ['ByUser', 'user_2', 6],
        ];
    }

    /**
     * @dataProvider disqualifyTypeProvider
     */
    public function testDisqualifyByType(
        string $type,
        string $identityUsername,
        int $expectedDisqualifiedRuns
    ) {
        $problemsAliases = ['problem1', 'problem2', 'problem3'];
        $contestantsRunsByProblem = [
            'user_1' => [3, 2, 1],
            'user_2' => [2, 3, 1],
            'user_3' => [1, 2, 2],
        ];

        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Create 3 problems
        $problemsData = [];
        foreach ($problemsAliases as $index => $problemAlias) {
            $problemsData[$index] = \OmegaUp\Test\Factories\Problem::createProblem(
                new \OmegaUp\Test\Factories\ProblemParams([
                    'alias' => $problemAlias,
                ])
            );

            // Add the problems to the contest
            \OmegaUp\Test\Factories\Contest::addProblemToContest(
                $problemsData[$index],
                $contestData
            );
        }

        $identities = [];
        $runsCount = 0;
        foreach ($contestantsRunsByProblem as $contestantUsername => $contestantRuns) {
            // Create 3 contestants
            [
                'identity' => $identities[$contestantUsername]
            ] = \OmegaUp\Test\Factories\User::createUser(
                new \OmegaUp\Test\Factories\UserParams([
                    'username' => $contestantUsername,
                ])
            );

            foreach ($contestantRuns as $problemIndex => $runs) {
                $runsData = [];
                foreach (range(0, $runs - 1) as $i => $_) {
                    // Create new runs
                    $runsData[$i] = \OmegaUp\Test\Factories\Run::createRun(
                        $problemsData[$problemIndex],
                        $contestData,
                        $identities[$contestantUsername]
                    );
                    \OmegaUp\Test\Factories\Run::gradeRun($runsData[$i]);
                    // Two minutes later for every submission
                    \OmegaUp\Time::setTimeForTesting(
                        \OmegaUp\Time::get() + 60 * 2
                    );
                    $runsCount++;
                }
            }
        }

        $login = self::login($contestData['director']);

        $requestParams = ['auth_token' => $login->auth_token];
        if ($type === 'ByGuid') {
            $requestParams['run_alias'] = $runsData[0]['response']['guid'];
        } else {
            $requestParams['username'] = $identityUsername;
            $requestParams['contest_alias'] = $contestData['request']['alias'];
            if ($type === 'ByUserAndProblem') {
                $requestParams['problem_alias'] = 'problem1';
            }
        }
        \OmegaUp\Controllers\Run::apiDisqualify(
            new \OmegaUp\Request($requestParams)
        );

        [
            'runs' => $runs,
            'totalRuns' => $totalRuns,
        ] = \OmegaUp\Controllers\Contest::apiRuns(
            new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $login->auth_token,
            ])
        );

        $this->assertSame($runsCount, $totalRuns);

        $runTypes = array_map(fn ($run) => [
            'username' => $run['username'],
            'problem_alias' => $run['alias'],
            'contest_alias' => $run['contest_alias'],
            'type' => $run['type'],
        ], $runs);

        $disqualifiedRuns = array_filter(
            $runTypes,
            fn ($run) => $run['type'] === 'disqualified'
        );

        $this->assertCount($expectedDisqualifiedRuns, $disqualifiedRuns);

        foreach ($disqualifiedRuns as $run) {
            $this->assertSame($run['username'], $identityUsername);
        }
    }
}
