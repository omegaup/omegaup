<?php
/**
 * Description of ContestScoreboardTest
 */

class ContestScoreboardTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Sets the context for a basic scoreboard test
     * @param  int     $nUsers
     * @param  array   $runMap
     * @param  boolean $runForAdmin
     * @param  boolean $runForDirector
     * @return array
     */
    private function prepareContestScoreboardData(
        int $nUsers,
        array $runMap,
        bool $runForAdmin = true,
        bool $runForDirector = true,
        string $admissionMode = 'public'
    ) {
        $problemData = [
            \OmegaUp\Test\Factories\Problem::createProblem(),
            \OmegaUp\Test\Factories\Problem::createProblem(),
        ];
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => $admissionMode]
            )
        );

        // Add the problems to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData[0],
            $contestData
        );
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData[1],
            $contestData
        );

        // Create our contestants and add them explictly to private contest
        $identities = [];
        for ($i = 0; $i < $nUsers; $i++) {
            $usernameAndName = \OmegaUp\Test\Utils::CreateRandomString();
            $userParams = [
                'username' => $usernameAndName,
                'name' => $usernameAndName,
            ];
            [
                'identity' => $identities[$i],
            ] = \OmegaUp\Test\Factories\User::createUser(
                new \OmegaUp\Test\Factories\UserParams($userParams)
            );
            if ($admissionMode !== 'private') {
                continue;
            }
            \OmegaUp\Test\Factories\Contest::addUser(
                $contestData,
                $identities[$i]
            );
        }
        $contestDirector = $contestData['director'];
        [
            'user' => $contestAdmin,
            'identity' => $contestIdentityAdmin,
        ] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Contest::addAdminUser(
            $contestData,
            $contestIdentityAdmin
        );

        foreach ($runMap as $runDescription) {
            $runData = \OmegaUp\Test\Factories\Run::createRun(
                $problemData[$runDescription['problem_idx']],
                $contestData,
                $identities[$runDescription['contestant_idx']]
            );

            \OmegaUp\Test\Factories\Run::gradeRun(
                $runData,
                $runDescription['points'],
                $runDescription['verdict'],
                $runDescription['submit_delay']
            );
            \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
        }

        if ($runForDirector) {
            $runDataDirector = \OmegaUp\Test\Factories\Run::createRun(
                $problemData[0],
                $contestData,
                $contestDirector
            );
            \OmegaUp\Test\Factories\Run::gradeRun($runDataDirector);
        }

        if ($runForAdmin) {
            $runDataAdmin = \OmegaUp\Test\Factories\Run::createRun(
                $problemData[0],
                $contestData,
                $contestIdentityAdmin
            );
            \OmegaUp\Test\Factories\Run::gradeRun($runDataAdmin);
        }

        return [
            'problemData' => $problemData,
            'contestData' => $contestData,
            'contestants' => $identities,
            'contestAdmin' => $contestAdmin,
            'contestIdentityAdmin' => $contestIdentityAdmin,
            'runMap' => $runMap,
        ];
    }

    /**
     * Basic test of scoreboard, shows at least the run
     * just submitted
     */
    public function testBasicScoreboard() {
        $runMap = [
            [
                'problem_idx' => 0,
                'contestant_idx' => 0,
                'points' => 0,
                'verdict' => 'CE',
                'submit_delay' => 60,
            ],
            [
                'problem_idx' => 0,
                'contestant_idx' => 0,
                'points' => 1,
                'verdict' => 'AC',
                'submit_delay' => 60,
            ],
            [
                'problem_idx' => 0,
                'contestant_idx' => 1,
                'points' => .9,
                'verdict' => 'PA',
                'submit_delay' => 60,
            ],
            [
                'problem_idx' => 0,
                'contestant_idx' => 2,
                'points' => 1,
                'verdict' => 'AC',
                'submit_delay' => 200,
            ],
            [
                'problem_idx' => 1,
                'contestant_idx' => 0,
                'points' => 1,
                'verdict' => 'AC',
                'submit_delay' => 200,
            ],
        ];
        $testData = $this->prepareContestScoreboardData(3, $runMap);

        // Create request
        $login = self::login($testData['contestants'][0]);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problemset_id' => $testData['contestData']['contest']->problemset_id,
        ]);

        // Create API
        $response = \OmegaUp\Controllers\Problemset::apiScoreboard($r);
        unset($login);

        // Validate that we have ranking
        $this->assertSame(3, count($response['ranking']));
        $this->assertSame(
            $testData['contestants'][0]->username,
            $response['ranking'][0]['username']
        );

        //Check totals
        $this->assertSame(200.0, $response['ranking'][0]['total']['points']);
        $this->assertSame(260.0, $response['ranking'][0]['total']['penalty']);

        // Check places
        $this->assertSame(1, $response['ranking'][0]['place']);
        $this->assertSame(2, $response['ranking'][1]['place']);
        $this->assertSame(3, $response['ranking'][2]['place']);

        // Check data per problem
        $this->assertSame(
            100.0,
            $response['ranking'][0]['problems'][0]['points']
        );
        $this->assertSame(
            60,
            $response['ranking'][0]['problems'][0]['penalty']
        );
        $this->assertSame(1, $response['ranking'][0]['problems'][0]['runs']);
        $this->assertSame(
            100.0,
            $response['ranking'][0]['problems'][1]['points']
        );
        $this->assertSame(
            200,
            $response['ranking'][0]['problems'][1]['penalty']
        );
        $this->assertSame(1, $response['ranking'][0]['problems'][1]['runs']);

        // Now get the scoreboard as an contest director
        $login = self::login($testData['contestData']['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problemset_id' => $testData['contestData']['contest']->problemset_id,
        ]);

        // Create API
        $response = \OmegaUp\Controllers\Problemset::apiScoreboard($r);

        // Validate that we have ranking
        $this->assertSame(3, count($response['ranking']));
        $this->assertSame(
            $testData['contestants'][0]->username,
            $response['ranking'][0]['username']
        );

        //Check totals
        $this->assertSame(200.0, $response['ranking'][0]['total']['points']);
        $this->assertSame(260.0, $response['ranking'][0]['total']['penalty']);

        // Check places
        $this->assertSame(1, $response['ranking'][0]['place']);
        $this->assertSame(2, $response['ranking'][1]['place']);
        $this->assertSame(3, $response['ranking'][2]['place']);

        // Check data per problem
        $this->assertSame(
            100.0,
            $response['ranking'][0]['problems'][0]['points']
        );
        $this->assertSame(
            60,
            $response['ranking'][0]['problems'][0]['penalty']
        );
        $this->assertSame(1, $response['ranking'][0]['problems'][0]['runs']);
        $this->assertSame(
            100.0,
            $response['ranking'][0]['problems'][1]['points']
        );
        $this->assertSame(
            200,
            $response['ranking'][0]['problems'][1]['penalty']
        );
        $this->assertSame(1, $response['ranking'][0]['problems'][1]['runs']);

        // getContestScoreboardDetailsForTypeScript function can get the
        // ranking too
        $ranking = \OmegaUp\Controllers\Contest::getContestScoreboardDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' =>  $testData['contestData']['contest']->alias,
            ])
        )['templateProperties']['payload']['scoreboard']['ranking'];

        // Validate that we have ranking
        $this->assertSame(3, count($ranking));
        $this->assertSame(
            $testData['contestants'][0]->username,
            $ranking[0]['username']
        );

        //Check totals
        $this->assertSame(200.0, $ranking[0]['total']['points']);
        $this->assertSame(260.0, $ranking[0]['total']['penalty']);

        // Check places
        $this->assertSame(1, $ranking[0]['place']);
        $this->assertSame(2, $ranking[1]['place']);
        $this->assertSame(3, $ranking[2]['place']);

        // Check data per problem
        $this->assertSame(100.0, $ranking[0]['problems'][0]['points']);
        $this->assertSame(60, $ranking[0]['problems'][0]['penalty']);
        $this->assertSame(1, $ranking[0]['problems'][0]['runs']);
        $this->assertSame(100.0, $ranking[0]['problems'][1]['points']);
        $this->assertSame(200, $ranking[0]['problems'][1]['penalty']);
        $this->assertSame(1, $ranking[0]['problems'][1]['runs']);
    }

    /**
     * Basic test of scoreboard with max policy.
     */
    public function testMaxPolicyScoreboard() {
        // Get two problems
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        $problemData2 = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['penaltyCalcPolicy' => 'max']
            )
        );

        // Add the problems to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData2,
            $contestData
        );

        // Create our contestants
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Create runs
        $runData = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );
        $runData1 = \OmegaUp\Test\Factories\Run::createRun(
            $problemData2,
            $contestData,
            $identity
        );

        // Grade the runs
        \OmegaUp\Test\Factories\Run::gradeRun($runData, 1, 'AC', 60);
        \OmegaUp\Test\Factories\Run::gradeRun($runData1, 1, 'AC', 200);

        // Create request
        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problemset_id' =>  $contestData['contest']->problemset_id,
        ]);

        // Create API
        $response = \OmegaUp\Controllers\Problemset::apiScoreboard($r);

        // Validate that we have ranking
        $this->assertSame(1, count($response['ranking']));
        $this->assertSame(
            $identity->username,
            $response['ranking'][0]['username']
        );

        //Check totals
        $this->assertSame(200.0, $response['ranking'][0]['total']['points']);
        $this->assertSame(200, $response['ranking'][0]['total']['penalty']);
    }

    /**
     * A PHPUnit data provider for all percentages of scoreboard show.
     *
     * @return list<array<0: float, 1: bool, 2: int, 3: float>>
     */
    public function contestPercentageScoreboardShowProvider(): array {
        return [
            [1.0, true, 1, 100.0],
            [0.0, false, 0, 0.0],
            [1.0, false, 1, 0.0],
            [0.0, true, 1, 100.0],
        ];
    }

    /**
     * Set different percentage of scoreboard for contestants
     *
     * @dataProvider contestPercentageScoreboardShowProvider
     */
    public function testScoreboardPercentajeForContestant(
        float $scoreboardPct,
        bool $showScoreboardAfter,
        int $expectedRuns,
        float $expectedPoints
    ) {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['showScoreboardAfter' => $showScoreboardAfter]
            )
        );

        // Set percentage of scoreboard show
        \OmegaUp\Test\Factories\Contest::setScoreboardPercentage(
            $contestData,
            $scoreboardPct
        );

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
        $login = self::login($identity);

        // Create API
        $response = \OmegaUp\Controllers\Problemset::apiScoreboard(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problemset_id' =>  $contestData['contest']->problemset_id,
            ])
        );

        // Validate that we have ranking
        $this->assertSame(1, count($response['ranking']));

        $this->assertSame(
            $identity->username,
            $response['ranking'][0]['username']
        );

        //Check totals
        $this->assertSame(0.0, $response['ranking'][0]['total']['points']);
        $this->assertSame(0.0, $response['ranking'][0]['total']['penalty']);

        // Check data per problem
        $this->assertSame(
            0.0,
            $response['ranking'][0]['problems'][0]['points']
        );
        $this->assertSame(
            0.0,
            $response['ranking'][0]['problems'][0]['penalty']
        );
        // When scoreboardPercentage is 0 and ShowScoreboardAfter is false,
        // the number of runs is not updated in the scoreboard
        $this->assertSame(
            $expectedRuns,
            $response['ranking'][0]['problems'][0]['runs']
        );

        $time = \OmegaUp\Time::get();

        // Create a new run 2 minutes after
        \OmegaUp\Time::setTimeForTesting($time + (2 * 60));

        $runData = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );

        // Grade the run
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        // Now, the contest has finished
        \OmegaUp\Time::setTimeForTesting($time + (5 * 60 * 60));

        $login = self::login($identity);

        // Create API
        $response = \OmegaUp\Controllers\Problemset::apiScoreboard(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problemset_id' =>  $contestData['contest']->problemset_id,
            ])
        );

        // Check data per problem
        $this->assertSame(
            $expectedPoints,
            $response['ranking'][0]['problems'][0]['points']
        );
    }

    /**
     * Set 0% of scoreboard for admins
     */
    public function testScoreboardPercentageForContestAdmin() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Set 0% of scoreboard show
        \OmegaUp\Test\Factories\Contest::setScoreboardPercentage(
            $contestData,
            0
        );

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
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problemset_id' =>  $contestData['contest']->problemset_id,
        ]);

        // Create API
        $response = \OmegaUp\Controllers\Problemset::apiScoreboard($r);

        // Validate that we have ranking
        $this->assertSame(1, count($response['ranking']));

        $this->assertSame(
            $identity->username,
            $response['ranking'][0]['username']
        );

        //Check totals
        $this->assertSame(100.0, $response['ranking'][0]['total']['points']);
        $this->assertSame(60.0, $response['ranking'][0]['total']['penalty']); /* 60 because contest started 60 mins ago in the default factory */

        // Check data per problem
        $this->assertSame(
            100.0,
            $response['ranking'][0]['problems'][0]['points']
        );
        $this->assertSame(
            60,
            $response['ranking'][0]['problems'][0]['penalty']
        );
        $this->assertSame(1, $response['ranking'][0]['problems'][0]['runs']);
    }

    /**
     * Scoreboard merge basic test
     */
    public function testScoreboardMerge() {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get contests
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        $contestData2 = \OmegaUp\Test\Factories\Contest::createContest();

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData2
        );

        // Create our contestants
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        ['identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();

        // Create a run
        $runData = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );
        $runData2 = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity2
        );
        $runData3 = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData2,
            $identity2
        );

        // Grade the run
        \OmegaUp\Test\Factories\Run::gradeRun($runData);
        \OmegaUp\Test\Factories\Run::gradeRun($runData2);
        \OmegaUp\Test\Factories\Run::gradeRun($runData3);

        // Create request
        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_aliases' => $contestData['request']['alias'] . ',' . $contestData2['request']['alias'],
        ]);

        // Call API
        $response = \OmegaUp\Controllers\Contest::apiScoreboardMerge($r);

        $this->assertSame(200.0, $response['ranking'][0]['total']['points']);
        $this->assertSame(100.0, $response['ranking'][1]['total']['points']);
        $this->assertSame(
            0.0,
            $response['ranking'][1]['contests'][$contestData2['request']['alias']]['points']
        );
    }

    /**
     * Basic tests for shareable scoreboard url
     */
    public function testScoreboardUrl() {
        // Get a private contest with 0% of scoreboard show percentage
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );
        \OmegaUp\Test\Factories\Contest::setScoreboardPercentage(
            $contestData,
            0
        );

        // Create problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create our user not added to the contest
        ['identity' => $externalIdentity] = \OmegaUp\Test\Factories\User::createUser();

        // Create our contestant, will submit 1 run
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Contest::addUser($contestData, $identity);
        $runData = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        // Get the scoreboard url by using the MyList api being the
        // contest director
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]);
        $response = \OmegaUp\Controllers\Contest::apiMyList($r);
        unset($login);

        // Look for our contest from the list and save the scoreboard tokens
        $scoreboard_url = null;
        $scoreboard_admin_url = null;
        foreach ($response['contests'] as $c) {
            if ($c['alias'] === $contestData['request']['alias']) {
                $scoreboard_url = $c['scoreboard_url'];
                $scoreboard_admin_url = $c['scoreboard_url_admin'];
                break;
            }
        }
        $this->assertNotNull($scoreboard_url);
        $this->assertNotNull($scoreboard_admin_url);

        // Call scoreboard api from the user
        $login = self::login($externalIdentity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problemset_id' =>  $contestData['contest']->problemset_id,
            'token' => $scoreboard_url,
        ]);
        $scoreboardResponse = \OmegaUp\Controllers\Problemset::apiScoreboard(
            $r
        );

        $this->assertSame(
            0.0,
            $scoreboardResponse['ranking'][0]['total']['points']
        );

        // Call scoreboard api from the user with admin token
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problemset_id' => $contestData['contest']->problemset_id,
            'token' => $scoreboard_admin_url,
        ]);
        $scoreboardResponse = \OmegaUp\Controllers\Problemset::apiScoreboard(
            $r
        );

        $this->assertSame(
            100.0,
            $scoreboardResponse['ranking'][0]['total']['points']
        );
    }

    /**
     * Test invalid token
     */
    public function testScoreboardUrlInvalidToken() {
        // Create our user not added to the contest
        ['identity' => $externalIdentity] = \OmegaUp\Test\Factories\User::createUser();

        // Get a contest with 0% of scoreboard show percentage
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Call scoreboard api from the user
        $login = self::login($externalIdentity);
        try {
            \OmegaUp\Controllers\Problemset::apiScoreboard(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'problemset_id' =>  $contestData['contest']->problemset_id,
                'token' => 'invalid token',
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('invalidScoreboardUrl', $e->getMessage());
        }
    }

    /**
     * Basic tests for shareable scoreboard url
     */
    public function testScoreboardUrlNoLogin() {
        // Get a private contest with 0% of scoreboard show percentage
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );
        \OmegaUp\Test\Factories\Contest::setScoreboardPercentage(
            $contestData,
            0
        );

        // Create problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create our contestant, will submit 1 run
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        \OmegaUp\Test\Factories\Contest::addUser($contestData, $identity);
        $runData = \OmegaUp\Test\Factories\Run::createRun(
            $problemData,
            $contestData,
            $identity
        );
        \OmegaUp\Test\Factories\Run::gradeRun($runData);

        // Get the scoreboard url by using the AdminList api being the
        // contest director
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]);
        $response = \OmegaUp\Controllers\Contest::apiAdminList($r);
        unset($login);

        // Look for our contest from the list and save the scoreboard tokens
        $scoreboard_url = null;
        $scoreboard_admin_url = null;
        foreach ($response['contests'] as $c) {
            if ($c['alias'] === $contestData['request']['alias']) {
                $scoreboard_url = $c['scoreboard_url'];
                $scoreboard_admin_url = $c['scoreboard_url_admin'];
                break;
            }
        }
        $this->assertNotNull($scoreboard_url);
        $this->assertNotNull($scoreboard_admin_url);

        // Call scoreboard api from the user
        $scoreboardResponse = \OmegaUp\Controllers\Problemset::apiScoreboard(new \OmegaUp\Request([
            'problemset_id' =>  $contestData['contest']->problemset_id,
            'token' => $scoreboard_url
        ]));

        $this->assertSame(
            0.0,
            $scoreboardResponse['ranking'][0]['total']['points']
        );

        // Call scoreboard api from the user with admin token
        $scoreboardResponse = \OmegaUp\Controllers\Problemset::apiScoreboard(new \OmegaUp\Request([
            'problemset_id' => $contestData['contest']->problemset_id,
            'token' => $scoreboard_admin_url
        ]));

        $this->assertSame(
            100.0,
            $scoreboardResponse['ranking'][0]['total']['points']
        );
    }

    /**
     * Basic happy path for Scoreboard events
     */
    public function testBasicScoreboardEventsPositive() {
        $runMap = [
            [
                'problem_idx' => 0,
                'contestant_idx' => 0,
                'points' => 0,
                'verdict' => 'CE',
                'submit_delay' => 60,
            ],
            [
                'problem_idx' => 0,
                'contestant_idx' => 0,
                'points' => 1,
                'verdict' => 'AC',
                'submit_delay' => 60,
            ],
            [
                'problem_idx' => 0,
                'contestant_idx' => 1,
                'points' => .9,
                'verdict' => 'PA',
                'submit_delay' => 60,
            ],
            [
                'problem_idx' => 0,
                'contestant_idx' => 2,
                'points' => 1,
                'verdict' => 'AC',
                'submit_delay' => 200,
            ],
            [
                'problem_idx' => 1,
                'contestant_idx' => 0,
                'points' => 1,
                'verdict' => 'AC',
                'submit_delay' => 200,
            ],
            [
                'problem_idx' => 1,
                'contestant_idx' => 2,
                'points' => 0,
                'verdict' => 'CE',
                'submit_delay' => 200,
            ],
        ];

        $testData = $this->prepareContestScoreboardData(3, $runMap);
        $login = self::login($testData['contestants'][0]);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problemset_id' => $testData['contestData']['contest']->problemset_id,
        ]);

        $response = \OmegaUp\Controllers\Problemset::apiScoreboardEvents($r);

        // From the map above, there are 4 meaningful combinations for events
        $this->assertSame(4, count($response['events']));
        $this->assertRunMapEntryIsOnEvents(
            $runMap[1],
            $testData,
            $response['events']
        );
        $this->assertRunMapEntryIsOnEvents(
            $runMap[2],
            $testData,
            $response['events']
        );
        $this->assertRunMapEntryIsOnEvents(
            $runMap[3],
            $testData,
            $response['events']
        );
        $this->assertRunMapEntryIsOnEvents(
            $runMap[4],
            $testData,
            $response['events']
        );
        $this->assertRunMapEntryIsOnEvents(
            $runMap[5],
            $testData,
            $response['events'],
            false /*sholdBeIn*/
        );

        // getContestScoreboardDetailsForTypeScript function can get the
        // scoreboardEvents too
        $events = \OmegaUp\Controllers\Contest::getContestScoreboardDetailsForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' =>  $testData['contestData']['contest']->alias,
            ])
        )['templateProperties']['payload']['scoreboardEvents'];

        // From the map above, there are 4 meaningful combinations for events
        $this->assertSame(4, count($events));
        $this->assertRunMapEntryIsOnEvents($runMap[1], $testData, $events);
        $this->assertRunMapEntryIsOnEvents($runMap[2], $testData, $events);
        $this->assertRunMapEntryIsOnEvents($runMap[3], $testData, $events);
        $this->assertRunMapEntryIsOnEvents($runMap[4], $testData, $events);
        $this->assertRunMapEntryIsOnEvents(
            $runMap[5],
            $testData,
            $events,
            false /*sholdBeIn*/
        );
    }

    /**
     * Verify an entry on Scoreboard events maps to an expected input value
     * @param  array  $runMapEntry
     * @param  array  $testData
     * @param  array  $events
     */
    private function assertRunMapEntryIsOnEvents(
        array $runMapEntry,
        array $testData,
        array $events,
        $shouldBeIn = true
    ) {
        $username = $testData['contestants'][$runMapEntry['contestant_idx']]->username;
        $problemAlias = $testData['problemData'][$runMapEntry['problem_idx']]['request']['problem_alias'];
        $eventFound = null;
        foreach ($events as $event) {
            if (
                $event['name'] === $username &&
                $event['problem']['alias'] === $problemAlias
            ) {
                $eventFound = $event;
                break;
            }
        }

        if ($shouldBeIn !== true) {
            if (!is_null($eventFound)) {
                $this->fail(
                    "$username $problemAlias combination was found on events when it was not expected."
                );
            }
            return;
        }

        if (is_null($eventFound)) {
            $this->fail(
                "$username $problemAlias combination not found on events."
            );
        }
        if ($eventFound['problem']['points'] != $runMapEntry['points'] * 100) {
            $this->fail("$username $problemAlias has unexpected points.");
        }
    }

    /**
     * Test scoreboard cache for contestants
     */
    public function testScoreboardFromUserCache() {
        $this->scoreboardCacheHelper();
    }

    /**
     * Test scoreboard cache for admin
     */
    public function testScoreboardFromAdminCache() {
        $this->scoreboardCacheHelper(true /*isAdmin*/);
    }

        /**
     * Test scoreboard cache for contestants
     */
    public function testScoreboardEventsFromUserCache() {
        $this->scoreboardCacheHelper(false, 'apiScoreboardEvents');
    }

    /**
     * Test scoreboard cache for admin
     */
    public function testScoreboardEventsFromAdminCache() {
        $this->scoreboardCacheHelper(true /*isAdmin*/, 'apiScoreboardEvents');
    }

    /**
     * E2E generic test for Scoreboard cache usage
     * @param bool $isAdmin
     * @param string $testApi
     */
    private function scoreboardCacheHelper(
        $isAdmin = false,
        $testApi = 'apiScoreboard'
    ) {
        $_scoreboardTestRun = new \OmegaUp\Test\ScopedScoreboardTestRun();

        $runMap = [
            [
                'problem_idx' => 0,
                'contestant_idx' => 0,
                'points' => 0,
                'verdict' => 'CE',
                'submit_delay' => 60,
            ],
            [
                'problem_idx' => 0,
                'contestant_idx' => 0,
                'points' => 1,
                'verdict' => 'AC',
                'submit_delay' => 60,
            ],
        ];

        $testData = $this->prepareContestScoreboardData(2, $runMap);
        $login = self::login(
            ($isAdmin ? $testData['contestData']['director'] : $testData['contestants'][0])
        );
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problemset_id' => $testData['contestData']['contest']->problemset_id,
        ]);

        $response1 = \OmegaUp\Controllers\Problemset::$testApi($r);
        $this->assertFalse(
            \OmegaUp\Scoreboard::getIsLastRunFromCacheForTesting()
        );

        $response2 = \OmegaUp\Controllers\Problemset::$testApi($r);
        if ($isAdmin && $testApi == 'apiScoreboardEvents') {
            $this->assertFalse(
                \OmegaUp\Scoreboard::getIsLastRunFromCacheForTesting()
            );
        } else {
            $this->assertTrue(
                \OmegaUp\Scoreboard::getIsLastRunFromCacheForTesting()
            );
        }

        $this->assertSame($response1, $response2);

        // Invalidate previously cached scoreboard
        \OmegaUp\Scoreboard::invalidateScoreboardCache(
            \OmegaUp\ScoreboardParams::fromContest(
                $testData['contestData']['contest']
            )
        );
        $response3 = \OmegaUp\Controllers\Problemset::$testApi($r);
        $this->assertSame(
            false,
            \OmegaUp\Scoreboard::getIsLastRunFromCacheForTesting()
        );

        // Single invalidation works, now invalidate again and check force referesh API
        \OmegaUp\Scoreboard::invalidateScoreboardCache(
            \OmegaUp\ScoreboardParams::fromContest(
                $testData['contestData']['contest']
            )
        );
        \OmegaUp\Scoreboard::refreshScoreboardCache(
            \OmegaUp\ScoreboardParams::fromContest(
                $testData['contestData']['contest']
            )
        );
        $response4 = \OmegaUp\Controllers\Problemset::$testApi($r);
        if ($isAdmin && $testApi == 'apiScoreboardEvents') {
            $this->assertFalse(
                \OmegaUp\Scoreboard::getIsLastRunFromCacheForTesting()
            );
        } else {
            $this->assertTrue(
                \OmegaUp\Scoreboard::getIsLastRunFromCacheForTesting()
            );
        }
        $this->assertEquals($response3, $response4);
    }

    /**
     * Test for Scoreboard with an added and removed problem from the problemset.
     */
    public function testScoreboardWithRemovedProblem() {
        $runMap = [
            [
                'problem_idx' => 0,
                'contestant_idx' => 0,
                'points' => 1,
                'verdict' => 'AC',
                'submit_delay' => 60,
            ],
            [
                'problem_idx' => 1,
                'contestant_idx' => 0,
                'points' => 1,
                'verdict' => 'AC',
                'submit_delay' => 60,
            ],
        ];

        $testData = $this->prepareContestScoreboardData(2, $runMap);

        // Only system admins can remove problems from a problemset where at
        // least one run has been added.
        \OmegaUp\Test\Factories\User::addSystemRole(
            $testData['contestData']['userDirector'],
            \OmegaUp\Authorization::ADMIN_ROLE
        );
        $login = self::login($testData['contestData']['director']);
        \OmegaUp\Controllers\Contest::apiRemoveProblem(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $testData['contestData']['contest']->alias,
            'problem_alias' => $testData['problemData'][1]['problem']->alias,
        ]));

        // Now the scoreboard should be available with a single problem.
        $response = \OmegaUp\Controllers\Problemset::apiScoreboard(new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problemset_id' => $testData['contestData']['contest']->problemset_id,
        ]));
        $this->assertSame(1, count($response['ranking']));
        $this->assertSame(1, count($response['problems']));
        $this->assertSame(1, count($response['ranking'][0]['problems']));
    }

    /**
     * A user who was removed for a contest can not see their scoreboard
     */
    public function testScoreboardForRemovedUser() {
        $runMap = [
            [
                'problem_idx' => 0,
                'contestant_idx' => 0,
                'points' => 0,
                'verdict' => 'CE',
                'submit_delay' => 60,
            ],
        ];
        $testData = $this->prepareContestScoreboardData(
            nUsers: 3,
            runMap: $runMap,
            runForAdmin: true,
            runForDirector: true,
            admissionMode: 'private'
        );

        // Create request
        $identityToRemove = $testData['contestants'][0];
        $login = self::login($identityToRemove);

        // Create API
        $contestAlias = $testData['contestData']['contest']->alias;
        $response = \OmegaUp\Controllers\Contest::apiScoreboard(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestAlias,
            ])
        );

        $this->assertArrayHasKey('ranking', $response);
        $this->assertArrayHasKey('problems', $response);

        // Admin removes user from contest
        $login = self::login($testData['contestData']['director']);

        $response = \OmegaUp\Controllers\Contest::apiUsers(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestAlias,
            ])
        );

        \OmegaUp\Controllers\Contest::apiRemoveUser(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestAlias,
                'usernameOrEmail' => $identityToRemove->username,
            ])
        );

        $response = \OmegaUp\Controllers\Contest::apiUsers(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestAlias,
            ])
        );

        $login = self::login($identityToRemove);

        try {
            \OmegaUp\Controllers\Contest::apiScoreboard(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'contest_alias' => $contestAlias,
                ])
            );
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    public function testScoreboardHideAdminRuns() {
        $runMap = [
            [
                'problem_idx' => 0,
                'contestant_idx' => 0,
                'points' => 0,
                'verdict' => 'CE',
                'submit_delay' => 60,
            ],
        ];
        $testData = $this->prepareContestScoreboardData(
            nUsers: 3,
            runMap: $runMap,
            runForAdmin: true,
            runForDirector: true,
            admissionMode: 'private'
        );

        // Add contestant as an admin via a group
        $contestData = $testData['contestData'];
        $adminGroup = \OmegaUp\Test\Factories\Groups::createGroup();
        $identityToRemove = $testData['contestants'][0];
        \OmegaUp\Test\Factories\Groups::addUserToGroup(
            $adminGroup,
            $identityToRemove
        );
        \OmegaUp\Test\Factories\Contest::addGroupAdmin(
            $contestData,
            $adminGroup['group']
        );

        // Create API
        $login = self::login($identityToRemove);
        $contestAlias = $contestData['contest']->alias;
        $response = \OmegaUp\Controllers\Contest::apiScoreboard(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestAlias,
            ])
        );

        $admins = [
            $identityToRemove->username,
            $contestData['director']->username,
            $testData['contestIdentityAdmin']->username
        ];

        // Check admin scoreboard.
        $this->assertArrayHasKey('ranking', $response);
        foreach ($response['ranking'] as $entry) {
            $this->assertNotContains($entry['username'], $admins);
        }

        // Check the public scoreboard.
        $contestant = $testData['contestants'][1];
        $login = self::login($contestant);
        $response = \OmegaUp\Controllers\Contest::apiScoreboard(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestAlias,
            ])
        );
        $this->assertArrayHasKey('ranking', $response);
        foreach ($response['ranking'] as $entry) {
            $this->assertNotContains($entry['username'], $admins);
        }
    }

    public function testScoreboardMergeDetailsForTypeScript() {
        $contests = [];
        // Get two contests
        $contests[] = \OmegaUp\Test\Factories\Contest::createContest();
        $contests[] = \OmegaUp\Test\Factories\Contest::createContest();

        // Get user to be added as contest admin
        [
            'identity' => $contestIdentityAdmin,
        ] = \OmegaUp\Test\Factories\User::createUser();

        // Set admin profile to user
        \OmegaUp\Test\Factories\Contest::addAdminUser(
            $contests[0],
            $contestIdentityAdmin
        );
        \OmegaUp\Test\Factories\Contest::addAdminUser(
            $contests[1],
            $contestIdentityAdmin
        );

        $login = self::login($contestIdentityAdmin);

        $contests = \OmegaUp\Controllers\Contest::getScoreboardMergeDetailsForTypeScript(
            new \OmegaUp\Request([ 'auth_token' => $login->auth_token ])
        )['templateProperties']['payload']['contests'];

        $this->assertCount(2, $contests);
    }

    /**
     * A PHPUnit data provider for the contest with max_per_group mode.
     *
     * @return array{0: int, 1: list<array: {runs: int, score: float, execution: string, output: string, status_memory: string, status_runtime: string}>, 2: list<array{total: float, points_per_group:array{group_name: string, score: float, verdict: string}}>, 3: int}
     */
    public function runsMappingProvider(): array {
        $runsMapping = [
            [
                'total' => 0.4,
                'points_per_group' => [
                    ['group_name' => 'easy', 'score' => (0.8 / 3), 'verdict' => 'PA'],
                    ['group_name' => 'medium', 'score' => (0.4 / 3), 'verdict' => 'PA'],
                    ['group_name' => 'hard', 'score' => 0.0,'verdict' => 'WA'],
                ],
            ],
            [
                'total' => 0.7,
                'points_per_group' => [
                    ['group_name' => 'easy', 'score' => (0.8 / 3), 'verdict' => 'PA'],
                    ['group_name' => 'medium', 'score' => (0.3 / 3), 'verdict' => 'PA'],
                    ['group_name' => 'hard', 'score' => (1.0 / 3),'verdict' => 'AC'],
                ],
            ],
            [
                'total' => 0.4,
                'points_per_group' => [
                    ['group_name' => 'easy', 'score' => (0.2 / 3), 'verdict' => 'PA'],
                    ['group_name' => 'medium', 'score' => (0.6 / 3), 'verdict' => 'PA'],
                    ['group_name' => 'hard', 'score' => (0.4 / 3),'verdict' => 'PA'],
                ],
            ],
        ];

        return [
            [
                100,
                [
                    ['runs' => 1, 'score' => 0.4, 'execution' => 'EXECUTION_FINISHED', 'output' => 'OUTPUT_INCORRECT', 'status_memory' => 'MEMORY_AVAILABLE', 'status_runtime' => 'RUNTIME_AVAILABLE'],
                    ['runs' => 2, 'score' => 0.7333, 'execution' => 'EXECUTION_FINISHED', 'output' => 'OUTPUT_INCORRECT', 'status_memory' => 'MEMORY_AVAILABLE', 'status_runtime' => 'RUNTIME_AVAILABLE'],
                    ['runs' => 3, 'score' => 0.8, 'execution' => 'EXECUTION_FINISHED', 'output' => 'OUTPUT_INCORRECT', 'status_memory' => 'MEMORY_AVAILABLE', 'status_runtime' => 'RUNTIME_AVAILABLE'],
                ],
                $runsMapping,
                100,
            ],
            [
                60,
                [
                    ['runs' => 1, 'score' => 0.4, 'execution' => 'EXECUTION_FINISHED', 'output' => 'OUTPUT_INCORRECT', 'status_memory' => 'MEMORY_AVAILABLE', 'status_runtime' => 'RUNTIME_AVAILABLE'],
                    ['runs' => 2, 'score' => 0.73, 'execution' => 'EXECUTION_FINISHED', 'output' => 'OUTPUT_INCORRECT', 'status_memory' => 'MEMORY_AVAILABLE', 'status_runtime' => 'RUNTIME_AVAILABLE'],
                    // Only the number of runs should be updated, because of the
                    // contest's settings
                    ['runs' => 3, 'score' => 0.73, 'execution' => 'EXECUTION_FINISHED', 'output' => 'OUTPUT_INCORRECT', 'status_memory' => 'MEMORY_AVAILABLE', 'status_runtime' => 'RUNTIME_AVAILABLE'],
                ],
                $runsMapping,
                1
            ],
            [
                100,
                [
                    ['runs' => 1, 'score' => 0.0, 'execution' => 'EXECUTION_COMPILATION_ERROR', 'output' => 'OUTPUT_INCORRECT', 'status_memory' => 'MEMORY_NOT_AVAILABLE', 'status_runtime' => 'RUNTIME_NOT_AVAILABLE'],
                    ['runs' => 2, 'score' => 0.5, 'execution' => 'EXECUTION_JUDGE_ERROR', 'output' => 'OUTPUT_EXCEEDED', 'status_memory' => 'MEMORY_NOT_AVAILABLE', 'status_runtime' => 'RUNTIME_NOT_AVAILABLE'],
                    ['runs' => 3, 'score' => 0.8333, 'execution' => 'EXECUTION_INTERRUPTED', 'output' => 'OUTPUT_INTERRUPTED', 'status_memory' => 'MEMORY_AVAILABLE', 'status_runtime' => 'RUNTIME_EXCEEDED'],
                    ['runs' => 4, 'score' => 1.0, 'execution' => 'EXECUTION_FINISHED', 'output' => 'OUTPUT_CORRECT', 'status_memory' => 'MEMORY_AVAILABLE', 'status_runtime' => 'RUNTIME_AVAILABLE'],
                ],
                [

                    [
                        'total' => 0.0,
                        'points_per_group' => [
                            ['group_name' => 'easy', 'score' => 0.0, 'verdict' => 'WA'],          // 0.00
                            ['group_name' => 'medium', 'score' => 0.0, 'verdict' => 'CE'],        // 0.00
                            ['group_name' => 'hard', 'score' => 0.0,'verdict' => 'RTE'],          // 0.00
                        ],
                    ],
                    [
                        'total' => 0.5,
                        'points_per_group' => [
                            ['group_name' => 'easy', 'score' => (1.0 / 3), 'verdict' => 'AC'],    // 0.33
                            ['group_name' => 'medium', 'score' => (0.5 / 3), 'verdict' => 'JE'],  // 0.16
                            ['group_name' => 'hard', 'score' => 0.0,'verdict' => 'OLE'],           // 0.00
                        ],
                    ],
                    [
                        'total' => 0.7,
                        'points_per_group' => [
                            ['group_name' => 'easy', 'score' => (0.8 / 3), 'verdict' => 'TLE'],    // 0.26
                            ['group_name' => 'medium', 'score' => (0.3 / 3), 'verdict' => 'TLE'], // 0.10
                            ['group_name' => 'hard', 'score' => (1.0 / 3),'verdict' => 'AC'],     // 0.33
                        ],
                    ],
                    [
                        'total' => 0.6,
                        'points_per_group' => [
                            ['group_name' => 'easy', 'score' => (0.4 / 3), 'verdict' => 'AC'],    // 0.13
                            ['group_name' => 'medium', 'score' => (1.0 / 3), 'verdict' => 'AC'],  // 0.33
                            ['group_name' => 'hard', 'score' => (0.4 / 3),'verdict' => 'AC'],     // 0.13
                        ],
                    ],
                ],
                100,
            ],
        ];
    }

    /**
     * @param list<array: {runs: int, score: float, execution: string, output: string, status_memory: string, status_runtime: string}> $expectedResultsInEverySubmission
     * @param list<array{total: float, points_per_group:array{group_name: string, score: float, verdict: string}}> $runsMapping
     *
     * @dataProvider runsMappingProvider
     */
    public function testScoreboardForContestInMaxPerGroupMode(
        int $scoreboardPercentage,
        array $expectedResultsInEverySubmission,
        array $runsMapping,
        int $pointsPerProblem
    ) {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a contest scoreMode
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'scoreMode' => 'max_per_group',
                'scoreboardPct' => $scoreboardPercentage,
            ])
        );

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData,
            $pointsPerProblem
        );

        // Create our contestant
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $time = \OmegaUp\Time::get();

        // Create and grade some runs every five minutes
        foreach ($runsMapping as $index => $run) {
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

            // Create request as a contestant
            $login = self::login($identity);

            // Create admin to get the runs
            ['identity' => $admin] = \OmegaUp\Test\Factories\User::createAdminUser();
            $adminLogin = self::login($admin);

            $runsList = \OmegaUp\Controllers\Run::apiList(new \OmegaUp\Request([
                'auth_token' => $adminLogin->auth_token,
            ]))['runs'];

            // Call API
            $response = \OmegaUp\Controllers\Contest::apiScoreboard(
                new \OmegaUp\Request([
                    'contest_alias' => $contestData['request']['alias'],
                    'auth_token' => $login->auth_token,
                ])
            )['ranking'];

            $this->assertSame(
                $response[0]['problems'][0]['points'],
                $expectedResultsInEverySubmission[$index]['score'] * $pointsPerProblem
            );
            $this->assertSame(
                $response[0]['problems'][0]['runs'],
                $expectedResultsInEverySubmission[$index]['runs']
            );
            $this->assertSame(
                $runsList[0]['execution'],
                $expectedResultsInEverySubmission[$index]['execution']
            );
            $this->assertSame(
                $runsList[0]['output'],
                $expectedResultsInEverySubmission[$index]['output']
            );
            $this->assertSame(
                $runsList[0]['status_runtime'],
                $expectedResultsInEverySubmission[$index]['status_runtime']
            );
            $this->assertSame(
                $runsList[0]['status_memory'],
                $expectedResultsInEverySubmission[$index]['status_memory']
            );
        }
    }

    /**
     * A PHPUnit data provider for the contest with max_per_group mode.
     *
     * @return array{0: bool, 1: list<array: {total: float, expectedScore: float, points_per_group: list<array: {group_name: string, score: float, verdict: string}>}>}
     */
    public function runsMappingPerGroupProvider(): array {
        return [
            [
                true,
                [
                    [
                        'total' => 0.25,
                        'expectedScore' => 0.25,
                        'points_per_group' => [
                            ['group_name' => 'sample', 'score' => 0.0, 'verdict' => 'WA'],
                            ['group_name' => 'easy', 'score' => 0.25, 'verdict' => 'AC'],
                            ['group_name' => 'medium', 'score' => 0.0, 'verdict' => 'JE'],
                            ['group_name' => 'hard', 'score' => 0.0,'verdict' => 'OLE'],
                        ],
                    ],
                    [
                        'total' => 0.25,
                        'expectedScore' => 0.50,
                        'points_per_group' => [
                            ['group_name' => 'sample', 'score' => 0.0, 'verdict' => 'WA'],
                            ['group_name' => 'easy', 'score' => 0.0, 'verdict' => 'TLE'],
                            ['group_name' => 'medium', 'score' => 0.0, 'verdict' => 'TLE'],
                            ['group_name' => 'hard', 'score' => 0.25,'verdict' => 'AC'],
                        ],
                    ],
                    [
                        'total' => 0.50,
                        'expectedScore' => 0.75,
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
                true,
                [
                    [
                        'total' => 0.25,
                        'expectedScore' => 0.25,
                        'points_per_group' => [
                            ['group_name' => 'sample', 'score' => 0.25, 'verdict' => 'AC'],
                            ['group_name' => 'easy', 'score' => 0.0, 'verdict' => 'WA'],
                            ['group_name' => 'medium', 'score' => 0.0, 'verdict' => 'JE'],
                            ['group_name' => 'hard', 'score' => 0.0,'verdict' => 'OLE'],
                        ],
                    ],
                    [
                        'total' => 0.75,
                        'expectedScore' => 0.75,
                        'points_per_group' => [
                            ['group_name' => 'sample', 'score' => 0.25, 'verdict' => 'AC'],
                            ['group_name' => 'easy', 'score' => 0.25, 'verdict' => 'AC'],
                            ['group_name' => 'medium', 'score' => 0.0, 'verdict' => 'TLE'],
                            ['group_name' => 'hard', 'score' => 0.25,'verdict' => 'AC'],
                        ],
                    ],
                    [
                        'total' => 0.5,
                        'expectedScore' => 1.0,
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
                false, // No events should be generated for this case
                [
                    [
                        'total' => 0.0,
                        'expectedScore' => 0.0,
                        'points_per_group' => [
                            ['group_name' => 'sample', 'score' => 0.0, 'verdict' => 'WA'],
                            ['group_name' => 'easy', 'score' => 0.0, 'verdict' => 'WA'],
                            ['group_name' => 'medium', 'score' => 0.0, 'verdict' => 'JE'],
                            ['group_name' => 'hard', 'score' => 0.0,'verdict' => 'OLE'],
                        ],
                    ],
                    [
                        'total' => 0.0,
                        'expectedScore' => 0.0,
                        'points_per_group' => [
                            ['group_name' => 'sample', 'score' => 0.0, 'verdict' => 'WA'],
                            ['group_name' => 'easy', 'score' => 0.0, 'verdict' => 'WA'],
                            ['group_name' => 'medium', 'score' => 0.0, 'verdict' => 'TLE'],
                            ['group_name' => 'hard', 'score' => 0.0,'verdict' => 'WA'],
                        ],
                    ],
                    [
                        'total' => 0.0,
                        'expectedScore' => 0.0,
                        'points_per_group' => [
                            ['group_name' => 'sample', 'score' => 0.0, 'verdict' => 'WA'],
                            ['group_name' => 'easy', 'score' => 0.0, 'verdict' => 'WA'],
                            ['group_name' => 'medium', 'score' => 0.0, 'verdict' => 'WA'],
                            ['group_name' => 'hard', 'score' => 0.0,'verdict' => 'WA'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param list<array: {total: float, expectedScore: float, points_per_group: list<array: {group_name: string, score: float, verdict: string}>}> $runsMapping
     *
     * @dataProvider runsMappingPerGroupProvider
     */
    public function testScoreboardEventsForContestInMaxPerGroupMode(
        bool $hasEvents,
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
        $eventsResponse = \OmegaUp\Controllers\Contest::apiScoreboardEvents(
            new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $login->auth_token,
            ])
        )['events'];

        if (!$hasEvents) {
            $this->assertEmpty($eventsResponse);
            return;
        }
        $delta = $eventsResponse[0]['delta'];
        $lastExpectedScore = 0.0;
        foreach ($runsMapping as $index => $run) {
            $event = $eventsResponse[$index];
            $this->assertSame($run['expectedScore'], $event['total']['points']);
            // Assert every 5 seconds one run was submitted
            $this->assertSame($delta, $event['delta']);
            $delta += 5;
            if ($lastExpectedScore != $run['expectedScore']) {
                $lastExpectedScore = $run['expectedScore'];
            }
        }
    }
}
