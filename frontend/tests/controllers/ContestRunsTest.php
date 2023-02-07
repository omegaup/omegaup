<?php
/**
 * Description of ContestRunsTest
 */

class ContestRunsTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Contestant submits runs and admin is able to get them
     */
    public function testGetRunsForContest() {
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

        // Create request
        $login = self::login($contestData['director']);

        // Call API
        $response = \OmegaUp\Controllers\Contest::apiRuns(
            new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $login->auth_token,
            ])
        );

        // Assert
        $this->assertSame(1, count($response['runs']));
        $this->assertSame(
            $runData['response']['guid'],
            $response['runs'][0]['guid']
        );
        $this->assertSame(
            $identity->username,
            $response['runs'][0]['username']
        );

        // Contest admin should be able to view run, even if not problem admin.
        $directorIdentity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $contestData['director']->username
        );
        $this->assertFalse(\OmegaUp\Authorization::isProblemAdmin(
            $directorIdentity,
            $problemData['problem']
        ));
        $response = \OmegaUp\Controllers\Run::apiDetails(new \OmegaUp\Request([
            'problemset_id' => $contestData['contest']->problemset_id,
            'run_alias' => $response['runs'][0]['guid'],
            'auth_token' => $login->auth_token,
        ]));

        $this->assertSame($runData['request']['source'], $response['source']);
    }

    /**
     * Contestant submits runs and admin is able to get them into the contest
     * details for typescript
     */
    public function testGetRunsForContestDetails() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Get problems and add them to the contest
        $problems = [];
        foreach (range(0, 4) as $index) {
            $problems[$index] = \OmegaUp\Test\Factories\Problem::createProblem();

            \OmegaUp\Test\Factories\Contest::addProblemToContest(
                $problems[$index],
                $contestData
            );
        }

        // Create our contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create one run for every problem
        $runs = [];
        foreach ($problems as $index => $problem) {
            $runs[$index] = \OmegaUp\Test\Factories\Run::createRun(
                $problem,
                $contestData,
                $identity
            );

            // Grade the run
            \OmegaUp\Test\Factories\Run::gradeRun($runs[$index]);
        }

        // Create request
        $login = self::login($contestData['director']);

        // Explicitly join contest
        \OmegaUp\Controllers\Contest::apiOpen(
            new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $login->auth_token,
            ])
        );

        // Call API
        $response = \OmegaUp\Controllers\Contest::getContestDetailsForTypeScript(
            new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $login->auth_token,
            ])
        )['templateProperties']['payload']['adminPayload'];

        // Assert
        $this->assertCount(5, $response['allRuns']);

        $guidsExpected = array_map(
            fn ($run) => $run['response']['guid'],
            $runs
        );
        $guidsActual = array_reverse(
            array_map(
                fn ($run) => $run['guid'],
                $response['allRuns']
            )
        );

        $this->assertSame($guidsExpected, $guidsActual);
    }

    /**
     * Contestant submits runs and admin edits the points of a problem
     * while the contest is active
     */
    public function testEditProblemsetPointsDuringAContest() {
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

        // Build request
        $directorLogin = self::login($contestData['director']);

        // Call API
        $response = \OmegaUp\Controllers\Contest::apiRuns(
            new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $directorLogin->auth_token,
            ])
        );

        $this->assertSame(100.0, $response['runs'][0]['contest_score']);

        $r = new \OmegaUp\Request([
            'auth_token' => $directorLogin->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'problem_alias' => $problemData['request']['problem_alias'],
            'points' => 80,
            'order_in_contest' => 1,
        ]);

        // Call API with different points value
        \OmegaUp\Controllers\Contest::apiAddProblem($r);

        // Call API
        $response = \OmegaUp\Controllers\Contest::apiRuns(
            new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $directorLogin->auth_token,
            ])
        );

        $this->assertSame(80.0, $response['runs'][0]['contest_score']);
    }

    /**
     * A PHPUnit data provider for the contest with max_per_group mode.
     *
     * @return array{0: float, 1: list<array: {total: float, points_per_group: list<array: {group_name: string, score: float, verdict: string}>}>}
     */
    public function runsMappingPerGroupProvider(): array {
        return [
            [
                0.75,
                [
                    [
                        'total' => 0.25,
                        'points_per_group' => [
                            ['group_name' => 'sample', 'score' => 0.0, 'verdict' => 'WA'],
                            ['group_name' => 'easy', 'score' => 0.25, 'verdict' => 'AC'],
                            ['group_name' => 'medium', 'score' => 0.0, 'verdict' => 'JE'],
                            ['group_name' => 'hard', 'score' => 0.0,'verdict' => 'OLE'],
                        ],
                    ],
                    [
                        'total' => 0.25,
                        'points_per_group' => [
                            ['group_name' => 'sample', 'score' => 0.0, 'verdict' => 'WA'],
                            ['group_name' => 'easy', 'score' => 0.0, 'verdict' => 'TLE'],
                            ['group_name' => 'medium', 'score' => 0.0, 'verdict' => 'TLE'],
                            ['group_name' => 'hard', 'score' => 0.25,'verdict' => 'AC'],
                        ],
                    ],
                    [
                        'total' => 0.50,
                        'points_per_group' => [
                            ['group_name' => 'sample', 'score' => 0.0, 'verdict' => 'WA'],
                            ['group_name' => 'easy', 'score' => 0.25, 'verdict' => 'AC'],
                            ['group_name' => 'medium', 'score' => 0.25, 'verdict' => 'AC'],
                            ['group_name' => 'hard', 'score' => 0.0,'verdict' => 'WA'],
                        ],
                    ],
                ],
            ],
            [
                0.9,
                [
                    [
                        'total' => 0.25,
                        'points_per_group' => [
                            ['group_name' => 'sample', 'score' => 0.25, 'verdict' => 'AC'],
                            ['group_name' => 'easy', 'score' => 0.0, 'verdict' => 'WA'],
                            ['group_name' => 'medium', 'score' => 0.0, 'verdict' => 'JE'],
                            ['group_name' => 'hard', 'score' => 0.0,'verdict' => 'OLE'],
                        ],
                    ],
                    [
                        'total' => 0.25,
                        'points_per_group' => [
                            ['group_name' => 'sample', 'score' => 0.1, 'verdict' => 'PA'],
                            ['group_name' => 'easy', 'score' => 0.1, 'verdict' => 'PA'],
                            ['group_name' => 'medium', 'score' => 0.1, 'verdict' => 'PA'],
                            ['group_name' => 'hard', 'score' => 0.25,'verdict' => 'AC'],
                        ],
                    ],
                    [
                        'total' => 0.50,
                        'points_per_group' => [
                            ['group_name' => 'sample', 'score' => 0.2, 'verdict' => 'PA'],
                            ['group_name' => 'easy', 'score' => 0.2, 'verdict' => 'PA'],
                            ['group_name' => 'medium', 'score' => 0.2, 'verdict' => 'PA'],
                            ['group_name' => 'hard', 'score' => 0.0,'verdict' => 'WA'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param list<array: {total: float, points_per_group: list<array: {group_name: string, score: float, verdict: string}>}> $runsMapping
     *
     * @dataProvider runsMappingPerGroupProvider
     */
    public function testScoreboardEventsForContestInMaxPerGroupMode(
        float $expectedScore,
        array $runsMapping
    ) {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a contest scoreMode
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'scoreMode' => 'max_per_group',
                'scoreboardPct' => 100,
            ])
        );

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create our contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $time = \OmegaUp\Time::get();

        // Create and grade some runs every five minutes
        foreach ($runsMapping as $run) {
            \OmegaUp\Time::setTimeForTesting($time + (5 * 60));

            $runData = \OmegaUp\Test\Factories\Run::createRun(
                $problemData,
                $contestData,
                $identity
            );

            \OmegaUp\Test\Factories\Run::gradeRun(
                runData: $runData,
                points: $run['total'],
                verdict: 'PA',
                submitDelay: null,
                runGuid: null,
                runId: null,
                problemsetPoints: 100,
                outputFilesContent: null,
                problemsetScoreMode: 'max_per_group',
                runScoreByGroups: $run['points_per_group']
            );
            $time = \OmegaUp\Time::get();
        }

        // Create request as a contestant
        $login = self::login($identity);
        $runs = \OmegaUp\Controllers\Problem::apiDetails(
            new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'problem_alias' => $problemData['problem']->alias,
                'auth_token' => $login->auth_token,
            ])
        )['runs'];

        $maxPerGroupScore = \OmegaUp\Test\Factories\Run::getMaxPerGroupScore(
            $runs
        );

        $this->assertSame($maxPerGroupScore, $expectedScore);
    }
}
