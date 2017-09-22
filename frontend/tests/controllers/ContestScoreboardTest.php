<?php

/**
 * Description of ContestScoreboardTest
 *
 * @author joemmanuel
 */

class ContestScoreboardTest extends OmegaupTestCase {
    /**
     * Sets the context for a basic scoreboard test
     * @param  integer $nUsers
     * @param  array  $runMap
     * @param  boolean $runForAdmin
     * @param  boolean $runForDirector
     * @return array
     */
    private function prepareContestScoreboardData($nUsers = 3, array $runMap, $runForAdmin = true, $runForDirector = true) {
        $problemData = [ProblemsFactory::createProblem(), ProblemsFactory::createProblem()];
        $contestFactory = new ContestsFactory(new ContestsParams([]));
        $contestData = $contestFactory->createContest();

        // Add the problems to the contest
        ContestsFactory::addProblemToContest($problemData[0], $contestData);
        ContestsFactory::addProblemToContest($problemData[1], $contestData);

        // Create our contestants
        $contestants = [];
        for ($i = 0; $i < $nUsers; $i++) {
            $contestants[] = UserFactory::createUser();
        }
        $contestDirector = $contestData['director'];
        $contestAdmin = UserFactory::createUser();
        ContestsFactory::addAdminUser($contestData, $contestAdmin);

        foreach ($runMap as $runDescription) {
            $runData = RunsFactory::createRun(
                $problemData[$runDescription['problem_idx']],
                $contestData,
                $contestants[$runDescription['contestant_idx']]
            );

            RunsFactory::gradeRun(
                $runData,
                $runDescription['points'],
                $runDescription['verdict'],
                $runDescription['submit_delay']
            );
        }

        if ($runForDirector) {
            $runDataDirector = RunsFactory::createRun($problemData[0], $contestData, $contestDirector);
            RunsFactory::gradeRun($runDataDirector);
        }

        if ($runForAdmin) {
            $runDataAdmin = RunsFactory::createRun($problemData[0], $contestData, $contestAdmin);
            RunsFactory::gradeRun($runDataAdmin);
        }

        return [
            'problemData' => $problemData,
            'contestData' => $contestData,
            'contestants' => $contestants,
            'contestAdmin' => $contestAdmin,
            'runMap' => $runMap
        ];
    }

    /**
     * Basic test of scoreboard, shows at least the run
     * just submitted
     */
    public function testBasicScoreboard() {
        $runMap = [
            ['problem_idx' => 0,
             'contestant_idx' => 0,
             'points' => 0,
             'verdict' => 'CE',
             'submit_delay' => 60
            ],
            ['problem_idx' => 0,
             'contestant_idx' => 0,
             'points' => 1,
             'verdict' => 'AC',
             'submit_delay' => 60
            ],
            ['problem_idx' => 0,
             'contestant_idx' => 1,
             'points' => .9,
             'verdict' => 'PA',
             'submit_delay' => 60
            ],
            ['problem_idx' => 0,
             'contestant_idx' => 2,
             'points' => 1,
             'verdict' => 'AC',
             'submit_delay' => 200
            ],
            ['problem_idx' => 1,
             'contestant_idx' => 0,
             'points' => 1,
             'verdict' => 'AC',
             'submit_delay' => 200
            ],
        ];
        $testData = $this->prepareContestScoreboardData(3, $runMap);

        // Create request
        $login = self::login($testData['contestants'][0]);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $testData['contestData']['request']['alias'],
        ]);

        // Create API
        $response = ContestController::apiScoreboard($r);
        unset($login);

        // Validate that we have ranking
        $this->assertEquals(3, count($response['ranking']));
        $this->assertEquals($testData['contestants'][0]->username, $response['ranking'][0]['username']);

        //Check totals
        $this->assertEquals(200, $response['ranking'][0]['total']['points']);
        $this->assertEquals(260, $response['ranking'][0]['total']['penalty']);

        // Check places
        $this->assertEquals(1, $response['ranking'][0]['place']);
        $this->assertEquals(2, $response['ranking'][1]['place']);
        $this->assertEquals(3, $response['ranking'][2]['place']);

        // Check data per problem
        $this->assertEquals(100, $response['ranking'][0]['problems'][0]['points']);
        $this->assertEquals(60, $response['ranking'][0]['problems'][0]['penalty']);
        $this->assertEquals(1, $response['ranking'][0]['problems'][0]['runs']);
        $this->assertEquals(100, $response['ranking'][0]['problems'][1]['points']);
        $this->assertEquals(200, $response['ranking'][0]['problems'][1]['penalty']);
        $this->assertEquals(1, $response['ranking'][0]['problems'][1]['runs']);

        // Now get the scoreboard as an contest director
        $login = self::login($testData['contestData']['director']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $testData['contestData']['request']['alias'],
        ]);

        // Create API
        $response = ContestController::apiScoreboard($r);

        // Validate that we have ranking
        $this->assertEquals(3, count($response['ranking']));
        $this->assertEquals($testData['contestants'][0]->username, $response['ranking'][0]['username']);

        //Check totals
        $this->assertEquals(200, $response['ranking'][0]['total']['points']);
        $this->assertEquals(260, $response['ranking'][0]['total']['penalty']);

        // Check places
        $this->assertEquals(1, $response['ranking'][0]['place']);
        $this->assertEquals(2, $response['ranking'][1]['place']);
        $this->assertEquals(3, $response['ranking'][2]['place']);

        // Check data per problem
        $this->assertEquals(100, $response['ranking'][0]['problems'][0]['points']);
        $this->assertEquals(60, $response['ranking'][0]['problems'][0]['penalty']);
        $this->assertEquals(1, $response['ranking'][0]['problems'][0]['runs']);
        $this->assertEquals(100, $response['ranking'][0]['problems'][1]['points']);
        $this->assertEquals(200, $response['ranking'][0]['problems'][1]['penalty']);
        $this->assertEquals(1, $response['ranking'][0]['problems'][1]['runs']);
    }

    /**
     * Basic test of scoreboard with max policy.
     */
    public function testMaxPolicyScoreboard() {
        // Get two problems
        $problemData = ProblemsFactory::createProblem();
        $problemData2 = ProblemsFactory::createProblem();

        // Get a contest
        $contestFactory = new ContestsFactory(new ContestsParams(['penalty_calc_policy' => 'max']));
        $contestData = $contestFactory->createContest();

        // Add the problems to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);
        ContestsFactory::addProblemToContest($problemData2, $contestData);

        // Create our contestants
        $contestant = UserFactory::createUser();

        // Create runs
        $runData = RunsFactory::createRun($problemData, $contestData, $contestant);
        $runData1 = RunsFactory::createRun($problemData2, $contestData, $contestant);

        // Grade the runs
        RunsFactory::gradeRun($runData, 1, 'AC', 60);
        RunsFactory::gradeRun($runData1, 1, 'AC', 200);

        // Create request
        $login = self::login($contestant);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Create API
        $response = ContestController::apiScoreboard($r);

        // Validate that we have ranking
        $this->assertEquals(1, count($response['ranking']));
        $this->assertEquals($contestant->username, $response['ranking'][0]['username']);

        //Check totals
        $this->assertEquals(200, $response['ranking'][0]['total']['points']);
        $this->assertEquals(200, $response['ranking'][0]['total']['penalty']);
    }

    /**
     * Set 0% of scoreboard for contestants, should show all 0s
     */
    public function testScoreboardPercentajeForContestant() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestFactory = new ContestsFactory(new ContestsParams([]));
        $contestData = $contestFactory->createContest();

        // Set 0% of scoreboard show
        ContestsFactory::setScoreboardPercentage($contestData, 0);

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant
        $contestant = UserFactory::createUser();

        // Create a run
        $runData = RunsFactory::createRun($problemData, $contestData, $contestant);

        // Grade the run
        RunsFactory::gradeRun($runData);

        // Create request
        $login = self::login($contestant);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Create API
        $response = ContestController::apiScoreboard($r);

        // Validate that we have ranking
        $this->assertEquals(1, count($response['ranking']));

        $this->assertEquals($contestant->username, $response['ranking'][0]['username']);

        //Check totals
        $this->assertEquals(0, $response['ranking'][0]['total']['points']);
        $this->assertEquals(0, $response['ranking'][0]['total']['penalty']); /* 60 because contest started 60 mins ago in the default factory */

        // Check data per problem
        $this->assertEquals(0, $response['ranking'][0]['problems'][0]['points']);
        $this->assertEquals(0, $response['ranking'][0]['problems'][0]['penalty']);
        $this->assertEquals(1, $response['ranking'][0]['problems'][0]['runs']);
    }

    /**
     * Set 0% of scoreboard for admins
     */
    public function testScoreboardPercentageForContestAdmin() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get a contest
        $contestFactory = new ContestsFactory(new ContestsParams([]));
        $contestData = $contestFactory->createContest();

        // Set 0% of scoreboard show
        ContestsFactory::setScoreboardPercentage($contestData, 0);

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant
        $contestant = UserFactory::createUser();

        // Create a run
        $runData = RunsFactory::createRun($problemData, $contestData, $contestant);

        // Grade the run
        RunsFactory::gradeRun($runData);

        // Create request
        $login = self::login($contestData['director']);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Create API
        $response = ContestController::apiScoreboard($r);

        // Validate that we have ranking
        $this->assertEquals(1, count($response['ranking']));

        $this->assertEquals($contestant->username, $response['ranking'][0]['username']);

        //Check totals
        $this->assertEquals(100, $response['ranking'][0]['total']['points']);
        $this->assertEquals(60, $response['ranking'][0]['total']['penalty']); /* 60 because contest started 60 mins ago in the default factory */

        // Check data per problem
        $this->assertEquals(100, $response['ranking'][0]['problems'][0]['points']);
        $this->assertEquals(60, $response['ranking'][0]['problems'][0]['penalty']);
        $this->assertEquals(1, $response['ranking'][0]['problems'][0]['runs']);
    }

    /**
     * Scoreboard merge basic test
     */
    public function testScoreboardMerge() {
        // Get a problem
        $problemData = ProblemsFactory::createProblem();

        // Get contests
        $contestFactory = new ContestsFactory(new ContestsParams([]));
        $contestData = $contestFactory->createContest();
        $contestFactory = new ContestsFactory(new ContestsParams([]));
        $contestData2 = $contestFactory->createContest();

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);
        ContestsFactory::addProblemToContest($problemData, $contestData2);

        // Create our contestants
        $contestant = UserFactory::createUser();
        $contestant2 = UserFactory::createUser();

        // Create a run
        $runData = RunsFactory::createRun($problemData, $contestData, $contestant);
        $runData2 = RunsFactory::createRun($problemData, $contestData, $contestant2);
        $runData3 = RunsFactory::createRun($problemData, $contestData2, $contestant2);

        // Grade the run
        RunsFactory::gradeRun($runData);
        RunsFactory::gradeRun($runData2);
        RunsFactory::gradeRun($runData3);

        // Create request
        $login = self::login($contestant);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_aliases' => $contestData['request']['alias'] . ',' . $contestData2['request']['alias'],
        ]);

        // Call API
        $response = ContestController::apiScoreboardMerge($r);

        $this->assertEquals(200, $response['ranking'][0]['total']['points']);
        $this->assertEquals(100, $response['ranking'][1]['total']['points']);
        $this->assertEquals(0, $response['ranking'][1]['contests'][$contestData2['request']['alias']]['points']);
    }

    /**
     * Basic tests for shareable scoreboard url
     */
    public function testScoreboardUrl() {
        // Get a private contest with 0% of scoreboard show percentage
        $contestFactory = new ContestsFactory(new ContestsParams(['public' => 0]));
        $contestData = $contestFactory->createContest();
        ContestsFactory::setScoreboardPercentage($contestData, 0);

        // Create problem
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our user not added to the contest
        $externalUser = UserFactory::createUser();

        // Create our contestant, will submit 1 run
        $contestant = UserFactory::createUser();

        ContestsFactory::addUser($contestData, $contestant);
        $runData = RunsFactory::createRun($problemData, $contestData, $contestant);
        RunsFactory::gradeRun($runData);

        // Get the scoreboard url by using the MyList api being the
        // contest director
        $login = self::login($contestData['director']);
        $r = new Request([
            'auth_token' => $login->auth_token,
        ]);
        $response = ContestController::apiMyList($r);
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
        $login = self::login($externalUser);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'token' => $scoreboard_url,
        ]);
        $scoreboardResponse = ContestController::apiScoreboard($r);

        $this->assertEquals('0', $scoreboardResponse['ranking'][0]['total']['points']);

        // Call scoreboard api from the user with admin token
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'token' => $scoreboard_admin_url,
        ]);
        $scoreboardResponse = ContestController::apiScoreboard($r);

        $this->assertEquals('100', $scoreboardResponse['ranking'][0]['total']['points']);
    }

    /**
     * Test invalid token
     *
     * @expectedException ForbiddenAccessException
     */
    public function testScoreboardUrlInvalidToken() {
        // Create our user not added to the contest
        $externalUser = UserFactory::createUser();

        // Get a contest with 0% of scoreboard show percentage
        $contestFactory = new ContestsFactory(new ContestsParams([]));
        $contestData = $contestFactory->createContest();

        // Call scoreboard api from the user
        $login = self::login($externalUser);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'token' => 'invalid token',
        ]);
        $scoreboardResponse = ContestController::apiScoreboard($r);
    }

    /**
     * Basic tests for shareable scoreboard url
     */
    public function testScoreboardUrlNoLogin() {
        // Get a private contest with 0% of scoreboard show percentage
        $contestFactory = new ContestsFactory(new ContestsParams(['public' => 0]));
        $contestData = $contestFactory->createContest();
        ContestsFactory::setScoreboardPercentage($contestData, 0);

        // Create problem
        $problemData = ProblemsFactory::createProblem();
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestant, will submit 1 run
        $contestant = UserFactory::createUser();

        ContestsFactory::addUser($contestData, $contestant);
        $runData = RunsFactory::createRun($problemData, $contestData, $contestant);
        RunsFactory::gradeRun($runData);

        // Get the scoreboard url by using the MyList api being the
        // contest director
        $login = self::login($contestData['director']);
        $r = new Request([
            'auth_token' => $login->auth_token,
        ]);
        $response = ContestController::apiMyList($r);
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
        $scoreboardResponse = ContestController::apiScoreboard(new Request([
            'contest_alias' => $contestData['request']['alias'],
            'token' => $scoreboard_url
        ]));

        $this->assertEquals('0', $scoreboardResponse['ranking'][0]['total']['points']);

        // Call scoreboard api from the user with admin token
        $scoreboardResponse = ContestController::apiScoreboard(new Request([
            'contest_alias' => $contestData['request']['alias'],
            'token' => $scoreboard_admin_url
        ]));

        $this->assertEquals('100', $scoreboardResponse['ranking'][0]['total']['points']);
    }

    /**
     * Basic happy path for Scoreboard events
     */
    public function testBasicScoreboardEventsPositive() {
        $runMap = [
            ['problem_idx' => 0,
             'contestant_idx' => 0,
             'points' => 0,
             'verdict' => 'CE',
             'submit_delay' => 60
            ],
            ['problem_idx' => 0,
             'contestant_idx' => 0,
             'points' => 1,
             'verdict' => 'AC',
             'submit_delay' => 60
            ],
            ['problem_idx' => 0,
             'contestant_idx' => 1,
             'points' => .9,
             'verdict' => 'PA',
             'submit_delay' => 60
            ],
            ['problem_idx' => 0,
             'contestant_idx' => 2,
             'points' => 1,
             'verdict' => 'AC',
             'submit_delay' => 200
            ],
            ['problem_idx' => 1,
             'contestant_idx' => 0,
             'points' => 1,
             'verdict' => 'AC',
             'submit_delay' => 200
            ],
            ['problem_idx' => 1,
             'contestant_idx' => 2,
             'points' => 0,
             'verdict' => 'CE',
             'submit_delay' => 200
            ],
        ];

        $testData = $this->prepareContestScoreboardData(3, $runMap);
        $login = self::login($testData['contestants'][0]);
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $testData['contestData']['request']['alias'],
        ]);

        $response = ContestController::apiScoreboardEvents($r);

        // From the map above, there are 4 meaningful combinations for events
        $this->assertEquals(4, count($response['events']));
        $this->assertRunMapEntryIsOnEvents($runMap[1], $testData, $response['events']);
        $this->assertRunMapEntryIsOnEvents($runMap[2], $testData, $response['events']);
        $this->assertRunMapEntryIsOnEvents($runMap[3], $testData, $response['events']);
        $this->assertRunMapEntryIsOnEvents($runMap[4], $testData, $response['events']);
        $this->assertRunMapEntryIsOnEvents($runMap[5], $testData, $response['events'], false /*sholdBeIn*/);
    }

    /**
     * Verify an entry on Scoreboard events maps to an expected input value
     * @param  array  $runMapEntry
     * @param  array  $testData
     * @param  array  $events
     */
    private function assertRunMapEntryIsOnEvents(array $runMapEntry, array $testData, array $events, $shouldBeIn = true) {
        $username = $testData['contestants'][$runMapEntry['contestant_idx']]->username;
        $problemAlias = $testData['problemData'][$runMapEntry['problem_idx']]['request']['alias'];
        $eventFound = null;
        foreach ($events as $event) {
            if ($event['name'] === $username &&
                $event['problem']['alias'] === $problemAlias) {
                $eventFound = $event;
            }
        }

        if ($shouldBeIn === true) {
            if (is_null($eventFound)) {
                $this->fail("$username $problemAlias combination not found on events.");
            }
        } else {
            if (!is_null($eventFound)) {
                $this->fail("$username $problemAlias combination was found on events when it was not expected.");
            }
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
    private function scoreboardCacheHelper($isAdmin = false, $testApi = 'apiScoreboard') {
        $scoreboardTestRun = new ScopedScoreboardTestRun();

        $runMap = [
            ['problem_idx' => 0,
             'contestant_idx' => 0,
             'points' => 0,
             'verdict' => 'CE',
             'submit_delay' => 60
            ],
            ['problem_idx' => 0,
             'contestant_idx' => 0,
             'points' => 1,
             'verdict' => 'AC',
             'submit_delay' => 60
            ]
        ];

        $testData = $this->prepareContestScoreboardData(2, $runMap);
        $login = self::login(($isAdmin ? $testData['contestData']['director'] : $testData['contestants'][0]));
        $r = new Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $testData['contestData']['request']['alias'],
        ]);

        $response1 = ContestController::$testApi($r);
        $this->assertEquals(false, Scoreboard::getIsLastRunFromCacheForTesting());

        $response2 = ContestController::$testApi($r);
        $this->assertEquals(true, Scoreboard::getIsLastRunFromCacheForTesting());

        $this->assertEquals($response1, $response2);

        // Invalidate previously cached scoreboard
        Scoreboard::invalidateScoreboardCache(ScoreboardParams::fromContest($testData['contestData']['contest']));
        $response3 = ContestController::$testApi($r);
        $this->assertEquals(false, Scoreboard::getIsLastRunFromCacheForTesting());

        // Single invalidation works, now invalidate again and check force referesh API
        Scoreboard::invalidateScoreboardCache(ScoreboardParams::fromContest($testData['contestData']['contest']));
        Scoreboard::refreshScoreboardCache(ScoreboardParams::fromContest($testData['contestData']['contest']));
        $response4 = ContestController::$testApi($r);
        $this->assertEquals(true, Scoreboard::getIsLastRunFromCacheForTesting());
        $this->assertEquals($response3, $response4);
    }
}
