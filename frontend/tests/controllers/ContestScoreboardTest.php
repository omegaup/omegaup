<?php

/**
 * Description of ContestScoreboardTest
 *
 * @author joemmanuel
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

        // Create our contestants and add them explictly to contest
        $contestants = [];
        $identities = [];
        for ($i = 0; $i < $nUsers; $i++) {
            [
                'identity' => $identities[$i],
            ] = \OmegaUp\Test\Factories\User::createUser();
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
        $this->assertEquals(3, count($response['ranking']));
        $this->assertEquals(
            $testData['contestants'][0]->username,
            $response['ranking'][0]['username']
        );

        //Check totals
        $this->assertEquals(200, $response['ranking'][0]['total']['points']);
        $this->assertEquals(260, $response['ranking'][0]['total']['penalty']);

        // Check places
        $this->assertEquals(1, $response['ranking'][0]['place']);
        $this->assertEquals(2, $response['ranking'][1]['place']);
        $this->assertEquals(3, $response['ranking'][2]['place']);

        // Check data per problem
        $this->assertEquals(
            100,
            $response['ranking'][0]['problems'][0]['points']
        );
        $this->assertEquals(
            60,
            $response['ranking'][0]['problems'][0]['penalty']
        );
        $this->assertEquals(1, $response['ranking'][0]['problems'][0]['runs']);
        $this->assertEquals(
            100,
            $response['ranking'][0]['problems'][1]['points']
        );
        $this->assertEquals(
            200,
            $response['ranking'][0]['problems'][1]['penalty']
        );
        $this->assertEquals(1, $response['ranking'][0]['problems'][1]['runs']);

        // Now get the scoreboard as an contest director
        $login = self::login($testData['contestData']['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'problemset_id' => $testData['contestData']['contest']->problemset_id,
        ]);

        // Create API
        $response = \OmegaUp\Controllers\Problemset::apiScoreboard($r);

        // Validate that we have ranking
        $this->assertEquals(3, count($response['ranking']));
        $this->assertEquals(
            $testData['contestants'][0]->username,
            $response['ranking'][0]['username']
        );

        //Check totals
        $this->assertEquals(200, $response['ranking'][0]['total']['points']);
        $this->assertEquals(260, $response['ranking'][0]['total']['penalty']);

        // Check places
        $this->assertEquals(1, $response['ranking'][0]['place']);
        $this->assertEquals(2, $response['ranking'][1]['place']);
        $this->assertEquals(3, $response['ranking'][2]['place']);

        // Check data per problem
        $this->assertEquals(
            100,
            $response['ranking'][0]['problems'][0]['points']
        );
        $this->assertEquals(
            60,
            $response['ranking'][0]['problems'][0]['penalty']
        );
        $this->assertEquals(1, $response['ranking'][0]['problems'][0]['runs']);
        $this->assertEquals(
            100,
            $response['ranking'][0]['problems'][1]['points']
        );
        $this->assertEquals(
            200,
            $response['ranking'][0]['problems'][1]['penalty']
        );
        $this->assertEquals(1, $response['ranking'][0]['problems'][1]['runs']);
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
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

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
        $this->assertEquals(1, count($response['ranking']));
        $this->assertEquals(
            $identity->username,
            $response['ranking'][0]['username']
        );

        //Check totals
        $this->assertEquals(200, $response['ranking'][0]['total']['points']);
        $this->assertEquals(200, $response['ranking'][0]['total']['penalty']);
    }

    /**
     * A PHPUnit data provider for all percentages of scoreboard show.
     *
     * @return list<list<int>>
     */
    public function contestPercentageScoreboardShowProvider(): array {
        return [
            [0],
            [1],
        ];
    }

    /**
     * Set different percentage of scoreboard for contestants
     *
     * @dataProvider contestPercentageScoreboardShowProvider
     */
    public function testScoreboardPercentajeForContestant(int $percentage) {
        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();

        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Set percentage of scoreboard show
        \OmegaUp\Test\Factories\Contest::setScoreboardPercentage(
            $contestData,
            $percentage
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

        $runs = 0;
        if ($percentage !== 0) {
            $runs++;
        }

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
        $this->assertEquals(1, count($response['ranking']));

        $this->assertEquals(
            $identity->username,
            $response['ranking'][0]['username']
        );

        //Check totals
        $this->assertEquals(0, $response['ranking'][0]['total']['points']);
        $this->assertEquals(0, $response['ranking'][0]['total']['penalty']);

        // Check data per problem
        $this->assertEquals(
            0,
            $response['ranking'][0]['problems'][0]['points']
        );
        $this->assertEquals(
            0,
            $response['ranking'][0]['problems'][0]['penalty']
        );
        $this->assertEquals(
            $runs,
            $response['ranking'][0]['problems'][0]['runs']
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
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

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
        $this->assertEquals(1, count($response['ranking']));

        $this->assertEquals(
            $identity->username,
            $response['ranking'][0]['username']
        );

        //Check totals
        $this->assertEquals(100, $response['ranking'][0]['total']['points']);
        $this->assertEquals(60, $response['ranking'][0]['total']['penalty']); /* 60 because contest started 60 mins ago in the default factory */

        // Check data per problem
        $this->assertEquals(
            100,
            $response['ranking'][0]['problems'][0]['points']
        );
        $this->assertEquals(
            60,
            $response['ranking'][0]['problems'][0]['penalty']
        );
        $this->assertEquals(1, $response['ranking'][0]['problems'][0]['runs']);
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
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        ['user' => $contestant2, 'identity' => $identity2] = \OmegaUp\Test\Factories\User::createUser();

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

        $this->assertEquals(200, $response['ranking'][0]['total']['points']);
        $this->assertEquals(100, $response['ranking'][1]['total']['points']);
        $this->assertEquals(
            0,
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
        ['user' => $externalUser, 'identity' => $externalIdentity] = \OmegaUp\Test\Factories\User::createUser();

        // Create our contestant, will submit 1 run
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

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

        $this->assertEquals(
            '0',
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

        $this->assertEquals(
            '100',
            $scoreboardResponse['ranking'][0]['total']['points']
        );
    }

    /**
     * Test invalid token
     */
    public function testScoreboardUrlInvalidToken() {
        // Create our user not added to the contest
        ['user' => $externalUser, 'identity' => $externalIdentity] = \OmegaUp\Test\Factories\User::createUser();

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
            $this->assertEquals('invalidScoreboardUrl', $e->getMessage());
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
        ['user' => $contestant, 'identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

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

        $this->assertEquals(
            '0',
            $scoreboardResponse['ranking'][0]['total']['points']
        );

        // Call scoreboard api from the user with admin token
        $scoreboardResponse = \OmegaUp\Controllers\Problemset::apiScoreboard(new \OmegaUp\Request([
            'problemset_id' => $contestData['contest']->problemset_id,
            'token' => $scoreboard_admin_url
        ]));

        $this->assertEquals(
            '100',
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
        $this->assertEquals(4, count($response['events']));
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
        $scoreboardTestRun = new \OmegaUp\Test\ScopedScoreboardTestRun();

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
        $this->assertEquals(
            false,
            \OmegaUp\Scoreboard::getIsLastRunFromCacheForTesting()
        );

        $response2 = \OmegaUp\Controllers\Problemset::$testApi($r);
        $this->assertEquals(
            true,
            \OmegaUp\Scoreboard::getIsLastRunFromCacheForTesting()
        );

        $this->assertEquals($response1, $response2);

        // Invalidate previously cached scoreboard
        \OmegaUp\Scoreboard::invalidateScoreboardCache(
            \OmegaUp\ScoreboardParams::fromContest(
                $testData['contestData']['contest']
            )
        );
        $response3 = \OmegaUp\Controllers\Problemset::$testApi($r);
        $this->assertEquals(
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
        $this->assertEquals(
            true,
            \OmegaUp\Scoreboard::getIsLastRunFromCacheForTesting()
        );
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
        $this->assertEquals(1, count($response['ranking']));
        $this->assertEquals(1, count($response['problems']));
        $this->assertEquals(1, count($response['ranking'][0]['problems']));
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
            /*$nUsers=*/            3,
            $runMap,
            /*$runForAdmin=*/ true,
            /*$runForDirector=*/ true,
            /*$admissionMode=*/ 'private'
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
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }
}
