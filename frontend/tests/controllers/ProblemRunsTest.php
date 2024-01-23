<?php
/**
 * Tests getting runs of a problem.
 */
class ProblemRunsTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Contestant submits runs and admin is able to get them.
     */
    public function testGetRunsForProblem() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $contestants = [];
        $runs = [];
        $runsMapping = [
            [
                ['runs' => 3, 'score' => 0.83, 'execution' => 'EXECUTION_INTERRUPTED', 'output' => 'OUTPUT_INTERRUPTED', 'status_memory' => 'MEMORY_AVAILABLE', 'status_runtime' => 'RUNTIME_EXCEEDED'],
                ['runs' => 4, 'score' => 1.0, 'execution' => 'EXECUTION_FINISHED', 'output' => 'OUTPUT_CORRECT', 'status_memory' => 'MEMORY_AVAILABLE', 'status_runtime' => 'RUNTIME_AVAILABLE'],
                ['runs' => 1, 'score' => 0, 'execution' => 'EXECUTION_COMPILATION_ERROR', 'output' => 'OUTPUT_INTERRUPTED', 'status_memory' => 'MEMORY_NOT_AVAILABLE', 'status_runtime' => 'RUNTIME_NOT_AVAILABLE'],
            ],
            [
                [
                    'total' => 0.7,
                    'finalVerdict' => 'TLE',
                    'points_per_group' => [
                        ['group_name' => 'easy', 'score' => (0.8 / 3), 'verdict' => 'TLE'],   // 0.26
                        ['group_name' => 'medium', 'score' => (0.3 / 3), 'verdict' => 'TLE'], // 0.10
                        ['group_name' => 'hard', 'score' => (1.0 / 3),'verdict' => 'AC'],     // 0.33
                    ],
                ],
                [
                    'total' => 0.6,
                    'finalVerdict' => 'AC',
                    'points_per_group' => [
                        ['group_name' => 'easy', 'score' => (0.4 / 3), 'verdict' => 'AC'],    // 0.13
                        ['group_name' => 'medium', 'score' => (1.0 / 3), 'verdict' => 'AC'],  // 0.33
                        ['group_name' => 'hard', 'score' => (0.4 / 3),'verdict' => 'AC'],     // 0.13
                    ],
                ],
                [
                    'total' => 0,
                    'finalVerdict' => 'CE',
                    'points_per_group' => [],
                ],
            ],
        ];
        for ($i = 0; $i < 3; ++$i) {
            ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
            $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
                $problemData,
                $identity
            );
            \OmegaUp\Test\Factories\Run::gradeRun(
                points: $runsMapping[1][$i]['total'],
                runData: $runData,
                verdict: $runsMapping[1][$i]['finalVerdict'],
                problemsetScoreMode: 'max_per_group',
                runScoreByGroups: $runsMapping[1][$i]['points_per_group']
            );
            $contestants[] = $identity;
            $runs[] = $runData;
        }

        // Regular users cannot use "show_all".
        try {
            $login = self::login($contestants[0]);
            \OmegaUp\Controllers\Problem::apiRuns(new \OmegaUp\Request([
                'problem_alias' => $problemData['problem']->alias,
                'auth_token' => $login->auth_token,
                'show_all' => true,
            ]));
            $this->fail('Should not have been able to call this API');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            // OK.
        }

        // Each user can only see their own runs.
        for ($i = 0; $i < count($contestants); ++$i) {
            $login = self::login($contestants[$i]);
            $response = \OmegaUp\Controllers\Problem::apiRuns(new \OmegaUp\Request([
                'problem_alias' => $problemData['problem']->alias,
                'auth_token' => $login->auth_token,
            ]));
            $this->assertCount(1, $response['runs']);
            $this->assertSame(
                $runs[$i]['response']['guid'],
                $response['runs'][0]['guid']
            );
            $this->assertSame(
                $runsMapping[0][$i]['output'],
                $response['runs'][0]['output']
            );
            $this->assertSame(
                $runsMapping[0][$i]['execution'],
                $response['runs'][0]['execution']
            );
            $this->assertSame(
                $runsMapping[0][$i]['status_memory'],
                $response['runs'][0]['status_memory']
            );
            $this->assertSame(
                $runsMapping[0][$i]['status_runtime'],
                $response['runs'][0]['status_runtime']
            );
        }

        // Admins can also see each contestants' runs.
        $login = self::login($problemData['author']);
        for ($i = 0; $i < count($contestants); ++$i) {
            $response = \OmegaUp\Controllers\Problem::apiRuns(new \OmegaUp\Request([
                'problem_alias' => $problemData['problem']->alias,
                'auth_token' => $login->auth_token,
                'show_all' => true,
                'username' => $contestants[$i]->username,
            ]));
            $this->assertCount(1, $response['runs']);
            $this->assertSame(
                $runs[$i]['response']['guid'],
                $response['runs'][0]['guid']
            );
        }

        // Admins can see all contestants' runs.
        $response = \OmegaUp\Controllers\Problem::apiRuns(new \OmegaUp\Request([
            'problem_alias' => $problemData['problem']->alias,
            'auth_token' => $login->auth_token,
            'show_all' => true,
        ]));
        $this->assertCount(3, $response['runs']);
        // Runs are sorted in reverse order.
        for ($i = 0; $i < count($contestants); ++$i) {
            $this->assertSame(
                $runs[$i]['response']['guid'],
                $response['runs'][count($contestants) - $i - 1]['guid']
            );
        }

        // Test execution filter
        $response = \OmegaUp\Controllers\Problem::apiRuns(new \OmegaUp\Request([
            'problem_alias' => $problemData['problem']->alias,
            'auth_token' => $login->auth_token,
            'show_all' => true,
            'execution' => 'EXECUTION_INTERRUPTED'
        ]));

        $this->assertCount(1, $response['runs']);

        // Test output filter
        $response = \OmegaUp\Controllers\Problem::apiRuns(new \OmegaUp\Request([
            'problem_alias' => $problemData['problem']->alias,
            'auth_token' => $login->auth_token,
            'show_all' => true,
            'output' => 'OUTPUT_INTERRUPTED'
        ]));

        $this->assertCount(2, $response['runs']);
    }

    public function testUserHasTriedToSolvedProblem() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        // Never tried, never solved
        $this->assertFalse(\OmegaUp\DAO\Problems::hasTriedToSolveProblem(
            $problemData['problem'],
            $identity->identity_id
        ));
        // Tried, but didn't solve the problem
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData, 0, 'WA', 60);
        $this->assertFalse(\OmegaUp\DAO\Problems::isProblemSolved(
            $problemData['problem'],
            $identity->identity_id
        ));
        $this->assertTrue(\OmegaUp\DAO\Problems::hasTriedToSolveProblem(
            $problemData['problem'],
            $identity->identity_id
        ));
        // Already tried and solved also
        $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
            $problemData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        $this->assertTrue(\OmegaUp\DAO\Problems::isProblemSolved(
            $problemData['problem'],
            $identity->identity_id
        ));
        $this->assertTrue(\OmegaUp\DAO\Problems::hasTriedToSolveProblem(
            $problemData['problem'],
            $identity->identity_id
        ));
    }

    public function testUserHasTriedToSolveProblemWithDifferentVerdicts() {
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        $pointsGroup = [
            [
                'execution' => 'EXECUTION_JUDGE_ERROR',
                'output' => 'OUTPUT_INTERRUPTED',
                'status_memory' => 'MEMORY_NOT_AVAILABLE',
                'status_runtime' => 'RUNTIME_NOT_AVAILABLE',
                'points_per_group' => [
                    ['group_name' => 'easy', 'score' => 0.0, 'verdict' => 'JE'],
                    ['group_name' => 'medium', 'score' => 0.0, 'verdict' => 'CE'],
                    ['group_name' => 'hard', 'score' => 0.0, 'verdict' => 'CE'],
                ],
            ],
            [
                'execution' => 'EXECUTION_COMPILATION_ERROR',
                'output' => 'OUTPUT_INTERRUPTED',
                'status_memory' => 'MEMORY_NOT_AVAILABLE',
                'status_runtime' => 'RUNTIME_NOT_AVAILABLE',
                'points_per_group' => [],
            ],
            [
                'execution' => 'EXECUTION_INTERRUPTED',
                'output' => 'OUTPUT_EXCEEDED',
                'status_memory' => 'MEMORY_AVAILABLE',
                'status_runtime' => 'RUNTIME_EXCEEDED',
                'points_per_group' => [
                    ['group_name' => 'easy', 'score' => (0.4 / 3), 'verdict' => 'AC'],
                    ['group_name' => 'medium', 'score' => 0.0, 'verdict' => 'TLE'],
                    ['group_name' => 'hard', 'score' => 0.0, 'verdict' => 'OLE'],
                ],
            ],
            [
                'execution' => 'EXECUTION_FINISHED',
                'output' => 'OUTPUT_INCORRECT',
                'status_memory' => 'MEMORY_AVAILABLE',
                'status_runtime' => 'RUNTIME_AVAILABLE',
                'points_per_group' => [
                    ['group_name' => 'easy', 'score' => (0.4 / 3), 'verdict' => 'AC'],
                    ['group_name' => 'medium', 'score' => (1.0 / 3), 'verdict' => 'AC'],
                    ['group_name' => 'hard', 'score' => (0.2 / 3), 'verdict' => 'WA'],
                ],
            ],
            [
                'execution' => 'EXECUTION_FINISHED',
                'output' => 'OUTPUT_CORRECT',
                'status_memory' => 'MEMORY_AVAILABLE',
                'status_runtime' => 'RUNTIME_AVAILABLE',
                'points_per_group' => [
                    ['group_name' => 'easy', 'score' => (0.4 / 3), 'verdict' => 'AC'],
                    ['group_name' => 'medium', 'score' => (1.0 / 3), 'verdict' => 'AC'],
                    ['group_name' => 'hard', 'score' => (0.4 / 3), 'verdict' => 'AC'],
                ],
            ],
        ];

        for ($i  = 0; $i < 5; ++$i) {
            $runData = \OmegaUp\Test\Factories\Run::createRunToProblem(
                $problemData,
                $identity
            );
            \OmegaUp\Test\Factories\Run::gradeRun(
                points: 0,
                runData: $runData,
                problemsetScoreMode: 'max_per_group',
                runScoreByGroups: $pointsGroup[$i]['points_per_group']
            );
        }

        $response = \OmegaUp\DAO\Runs::getAllRuns(
            $problemsetId = null,
            $status = null,
            $verdict = null,
            $problemId = $problemData['problem']->problem_id,
            $language = null,
            $identityId = $identity->identity_id,
            $offset = null,
            $rowCount = null,
        );

        for ($i  = 0; $i < 5; ++$i) {
            $this->assertSame(
                $response['runs'][4 - $i]['execution'],
                $pointsGroup[$i]['execution']
            );
            $this->assertSame(
                $response['runs'][4 - $i]['output'],
                $pointsGroup[$i]['output']
            );
            $this->assertSame(
                $response['runs'][4 - $i]['status_memory'],
                $pointsGroup[$i]['status_memory']
            );
            $this->assertSame(
                $response['runs'][4 - $i]['status_runtime'],
                $pointsGroup[$i]['status_runtime']
            );
        }
    }
}
