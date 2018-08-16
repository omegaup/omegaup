<?php

/**
 * Description of CourseScoreboardTest
 *
 * @author joemmanuel
 */

class CourseScoreboardTest extends OmegaupTestCase {
    /**
     * Sets the context for a basic scoreboard test
     * @param  integer $nUsers
     * @param  array  $runMap
     * @param  boolean $runForAdmin
     * @return array
     */
    private function prepareCourseScoreboardData(array $runMap, $numberOfUsers = 3, $runForAdmin = true) {
        $problemData = [ProblemsFactory::createProblem(), ProblemsFactory::createProblem()];
        // Creating one public course
        $courseAssignmentData = CoursesFactory::createCourseWithOneAssignment(null, null, true);
        $adminLogin = self::login($courseAssignmentData['admin']);

        // Add the problems to the course
        CoursesFactory::addProblemsToAssignment(
            $adminLogin,
            $courseAssignmentData['course_alias'],
            $courseAssignmentData['assignment_alias'],
            $problemData
        );

        // Create our participants
        $participants = [];
        for ($i = 0; $i < $numberOfUsers; $i++) {
            $participants[$i] = UserFactory::createUser();
            CoursesFactory::addStudentToCourse($courseAssignmentData, $participants[$i]);
        }
        $courseAdmin = $courseAssignmentData['admin'];

        foreach ($runMap as $runDescription) {
            $runData = RunsFactory::createCourseAssignmentRun(
                $problemData[$runDescription['problem_idx']],
                $courseAssignmentData,
                $participants[$runDescription['participant_idx']]
            );

            RunsFactory::gradeRun(
                $runData,
                $runDescription['points'],
                $runDescription['verdict'],
                $runDescription['submit_delay']
            );
        }

        if ($runForAdmin) {
            $runDataAdmin = RunsFactory::createCourseAssignmentRun($problemData[0], $courseAssignmentData, $courseAdmin);
            RunsFactory::gradeRun($runDataAdmin);
        }

        return [
            'problemData' => $problemData,
            'courseAssignmentData' => $courseAssignmentData,
            'participants' => $participants,
            'courseAdmin' => $courseAdmin,
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
             'participant_idx' => 0,
             'points' => 0,
             'verdict' => 'CE',
             'submit_delay' => 60
            ],
            ['problem_idx' => 0,
             'participant_idx' => 0,
             'points' => 1,
             'verdict' => 'AC',
             'submit_delay' => 60
            ],
            ['problem_idx' => 0,
             'participant_idx' => 1,
             'points' => .9,
             'verdict' => 'PA',
             'submit_delay' => 60
            ],
            ['problem_idx' => 0,
             'participant_idx' => 2,
             'points' => 1,
             'verdict' => 'AC',
             'submit_delay' => 200
            ],
            ['problem_idx' => 1,
             'participant_idx' => 0,
             'points' => 1,
             'verdict' => 'AC',
             'submit_delay' => 200
            ],
        ];
        $testData = $this->prepareCourseScoreboardData($runMap);

        // Create request
        $login = self::login($testData['participants'][0]);

        // Create API
        $response = ProblemsetController::apiScoreboard(new Request([
            'auth_token' => $login->auth_token,
            'problemset_id' => $testData['courseAssignmentData']['assignment']->problemset_id,
        ]));
        unset($login);

        // Validate that we have ranking
        $this->assertEquals(3, count($response['ranking']));
        $this->assertEquals($testData['participants'][0]->username, $response['ranking'][0]['username']);

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

        // Now get the scoreboard as an course admin
        $login = self::login($testData['courseAssignmentData']['admin']);

        // Create API
        $response = ProblemsetController::apiScoreboard(new Request([
            'auth_token' => $login->auth_token,
            'problemset_id' => $testData['courseAssignmentData']['assignment']->problemset_id,
        ]));

        // Validate that we have ranking
        $this->assertEquals(3, count($response['ranking']));
        $this->assertEquals($testData['participants'][0]->username, $response['ranking'][0]['username']);

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
     * Basic tests for shareable scoreboard url
     */
    public function testScoreboardUrl() {
        // Get a private course with one assignment
        $courseAssignmentData = CoursesFactory::createCourseWithOneAssignment();
        $adminLogin = self::login($courseAssignmentData['admin']);
        // Create problem
        $problemData = [ProblemsFactory::createProblem()];
        CoursesFactory::addProblemsToAssignment(
            $adminLogin,
            $courseAssignmentData['request']['course_alias'],
            $courseAssignmentData['request']['alias'],
            $problemData
        );

        // Create our user not added to the course
        $externalUser = UserFactory::createUser();

        // Create our participant, will submit 1 run
        $participant = UserFactory::createUser();

        CoursesFactory::addStudentToCourse($courseAssignmentData, $participant);
        $runData = RunsFactory::createCourseAssignmentRun($problemData[0], $courseAssignmentData, $participant);
        RunsFactory::gradeRun($runData);

        // Get the scoreboard url by using the ListAssignments api being the
        // course admin
        $login = self::login($courseAssignmentData['admin']);
        $response = CourseController::apiListAssignments(new Request([
            'auth_token' => $login->auth_token,
            'course_alias' => $courseAssignmentData['request']['course_alias'],
        ]));
        unset($login);

        // Look for our assignment in course from the list and save the scoreboard tokens
        $scoreboard_url = null;
        $scoreboard_admin_url = null;
        foreach ($response['assignments'] as $assignment) {
            if ($assignment['alias'] === $courseAssignmentData['request']['course_alias']) {
                $scoreboard_url = $assignment['scoreboard_url'];
                $scoreboard_admin_url = $assignment['scoreboard_url_admin'];
                break;
            }
        }
        $this->assertNotNull($scoreboard_url);
        $this->assertNotNull($scoreboard_admin_url);

        // Call scoreboard api from the user
        $login = self::login($externalUser);
        $scoreboardResponse = ProblemsetController::apiScoreboard(new Request([
            'auth_token' => $login->auth_token,
            'problemset_id' =>  $courseAssignmentData['assignment']->problemset_id,
            'token' => $scoreboard_url,
        ]));

        $this->assertEquals('0', $scoreboardResponse['ranking'][0]['total']['points']);

        // Call scoreboard api from the user with admin token
        $scoreboardResponse = ProblemsetController::apiScoreboard(new Request([
            'auth_token' => $login->auth_token,
            'problemset_id' => $courseAssignmentData['assignment']->problemset_id,
            'token' => $scoreboard_admin_url,
        ]));

        $this->assertEquals('100', $scoreboardResponse['ranking'][0]['total']['points']);
    }

    /**
     * Test invalid token
     *
     * @expectedException ForbiddenAccessException
     */
    public function testScoreboardUrlInvalidToken() {
        // Create our user not added to the course
        $externalUser = UserFactory::createUser();

        // Get a course
        $courseAssignmentData = UserFactory::createCourseWithOneAssignment();

        // Call scoreboard api from the user
        $login = self::login($externalUser);
        $scoreboardResponse = ProblemsetController::apiScoreboard(new Request([
            'auth_token' => $login->auth_token,
            'problemset_id' =>  $courseAssignmentData['assignment']->problemset_id,
            'token' => 'invalid token',
        ]));
    }

    /**
     * Basic tests for shareable scoreboard url
     */
    public function testScoreboardUrlNoLogin() {
        // Get a private course with one assignment
        $courseAssignmentData = CoursesFactory::createCourseWithOneAssignment();
        $adminLogin = self::login($courseAssignmentData['admin']);

        // Create problem
        $problemData = [ProblemsFactory::createProblem()];
        CoursesFactory::addProblemsToAssignment(
            $adminLogin,
            $courseAssignmentData['course_alias'],
            $courseAssignmentData['assignment_alias'],
            $problemData
        );

        // Create our participant, will submit 1 run
        $participant = UserFactory::createUser();

        CoursesFactory::addStudentToCourse($courseAssignmentData, $participant);
        $runData = RunsFactory::createCourseAssignmentRun($problemData[0], $courseAssignmentData, $participant);
        RunsFactory::gradeRun($runData);

        // Get the scoreboard url by using the ListAssignments api being the
        // course admin
        $login = self::login($courseAssignmentData['admin']);
        $response = CourseController::apiListAssignments(new Request([
            'auth_token' => $login->auth_token,
            'course_alias' => $courseAssignmentData['request']['course_alias'],
        ]));
        unset($login);

        // Look for our assignment in course from the list and save the scoreboard tokens
        $scoreboard_url = null;
        $scoreboard_admin_url = null;
        foreach ($response['assignments'] as $assignment) {
            if ($assignment['alias'] === $courseAssignmentData['request']['alias']) {
                $scoreboard_url = $assignment['scoreboard_url'];
                $scoreboard_admin_url = $assignment['scoreboard_url_admin'];
                break;
            }
        }
        $this->assertNotNull($scoreboard_url);
        $this->assertNotNull($scoreboard_admin_url);

        // Call scoreboard api from the user
        $scoreboardResponse = ProblemsetController::apiScoreboard(new Request([
            'problemset_id' =>  $courseAssignmentData['assignment']->problemset_id,
            'token' => $scoreboard_url
        ]));

        $this->assertEquals('0', $scoreboardResponse['ranking'][0]['total']['points']);

        // Call scoreboard api from the user with admin token
        $scoreboardResponse = ProblemsetController::apiScoreboard(new Request([
            'problemset_id' => $courseAssignmentData['assignment']->problemset_id,
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
             'participant_idx' => 0,
             'points' => 0,
             'verdict' => 'CE',
             'submit_delay' => 60
            ],
            ['problem_idx' => 0,
             'participant_idx' => 0,
             'points' => 1,
             'verdict' => 'AC',
             'submit_delay' => 60
            ],
            ['problem_idx' => 0,
             'participant_idx' => 1,
             'points' => .9,
             'verdict' => 'PA',
             'submit_delay' => 60
            ],
            ['problem_idx' => 0,
             'participant_idx' => 2,
             'points' => 1,
             'verdict' => 'AC',
             'submit_delay' => 200
            ],
            ['problem_idx' => 1,
             'participant_idx' => 0,
             'points' => 1,
             'verdict' => 'AC',
             'submit_delay' => 200
            ],
            ['problem_idx' => 1,
             'participant_idx' => 2,
             'points' => 0,
             'verdict' => 'CE',
             'submit_delay' => 200
            ],
        ];

        $testData = $this->prepareCourseScoreboardData($runMap);
        $login = self::login($testData['participants'][0]);

        $response = ProblemsetController::apiScoreboardEvents(new Request([
            'auth_token' => $login->auth_token,
            'problemset_id' => $testData['courseAssignmentData']['assignment']->problemset_id,
        ]));

        // From the map above, there are 4 meaningful combinations for events
        $this->assertEquals(4, count($response['events']));
        $this->assertRunMapEntryIsOnEvents($runMap[1], $testData, $response['events']);
        $this->assertRunMapEntryIsOnEvents($runMap[2], $testData, $response['events']);
        $this->assertRunMapEntryIsOnEvents($runMap[3], $testData, $response['events']);
        $this->assertRunMapEntryIsOnEvents($runMap[4], $testData, $response['events']);
        $this->assertRunMapEntryIsOnEvents($runMap[5], $testData, $response['events'], false /*shouldBeIn*/);
    }

    /**
     * Verify an entry on Scoreboard events maps to an expected input value
     * @param  array  $runMapEntry
     * @param  array  $testData
     * @param  array  $events
     */
    private function assertRunMapEntryIsOnEvents(array $runMapEntry, array $testData, array $events, $shouldBeIn = true) {
        $username = $testData['participants'][$runMapEntry['participant_idx']]->username;
        $problemAlias = $testData['problemData'][$runMapEntry['problem_idx']]['request']['problem_alias'];
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
     * Test scoreboard cache for participants
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
     * Test scoreboard cache for participants
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
             'participant_idx' => 0,
             'points' => 0,
             'verdict' => 'CE',
             'submit_delay' => 60
            ],
            ['problem_idx' => 0,
             'participant_idx' => 0,
             'points' => 1,
             'verdict' => 'AC',
             'submit_delay' => 60
            ]
        ];

        $testData = $this->prepareCourseScoreboardData($runMap, 2);
        $login = self::login(($isAdmin ? $testData['courseAssignmentData']['admin'] : $testData['participants'][0]));
        $r = new Request([
            'auth_token' => $login->auth_token,
            'problemset_id' => $testData['courseAssignmentData']['assignment']->problemset_id,
        ]);

        $response1 = ProblemsetController::$testApi($r);
        $this->assertEquals(false, Scoreboard::getIsLastRunFromCacheForTesting());

        $response2 = ProblemsetController::$testApi($r);
        $this->assertEquals(true, Scoreboard::getIsLastRunFromCacheForTesting());

        $this->assertEquals($response1, $response2);

        // Invalidate previously cached scoreboard
        Scoreboard::invalidateScoreboardCache(ScoreboardParams::fromAssignment($testData['courseAssignmentData']['assignment']));
        $response3 = ProblemsetController::$testApi($r);
        $this->assertEquals(false, Scoreboard::getIsLastRunFromCacheForTesting());

        // Single invalidation works, now invalidate again and check force referesh API
        Scoreboard::invalidateScoreboardCache(ScoreboardParams::fromAssignment($testData['courseAssignmentData']['assignment']));
        Scoreboard::refreshScoreboardCache(ScoreboardParams::fromAssignment($testData['courseAssignmentData']['assignment']));
        $response4 = ProblemsetController::$testApi($r);
        $this->assertEquals(true, Scoreboard::getIsLastRunFromCacheForTesting());
        $this->assertEquals($response3, $response4);
    }
}
