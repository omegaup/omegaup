<?php
/**
 * Description of DetailsContest
 */

class ContestDetailsTest extends \OmegaUp\Test\ControllerTestCase {
    /**
     * Checks the contest details response
     *
     * @param type $contestData
     * @param type $problems
     * @param type $response
     */
    private function assertContestDetails($contestData, $problems, $response) {
        // To validate, grab the contest object directly from the DB
        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );

        // Assert we are getting correct data
        $this->assertSame($contest->description, $response['description']);
        $this->assertSame(
            $contest->start_time->time,
            $response['start_time']->time
        );
        $this->assertSame(
            $contest->finish_time->time,
            $response['finish_time']->time
        );
        $this->assertSame(
            $contest->window_length,
            $response['window_length']
        );
        $this->assertSame($contest->alias, $response['alias']);
        $this->assertSame(
            $contest->points_decay_factor,
            $response['points_decay_factor']
        );
        $this->assertSame($contest->score_mode, $response['score_mode']);
        $this->assertSame(
            $contest->submissions_gap,
            $response['submissions_gap']
        );
        $this->assertSame($contest->feedback, $response['feedback']);
        $this->assertSame($contest->penalty, $response['penalty']);
        $this->assertSame($contest->scoreboard, $response['scoreboard']);
        $this->assertSame($contest->penalty_type, $response['penalty_type']);
        $this->assertSame(
            $contest->penalty_calc_policy,
            $response['penalty_calc_policy']
        );

        // Assert we have our problems
        $numOfProblems = count($problems);
        $this->assertSame($numOfProblems, count($response['problems']));

        // Assert problem data
        $i = 0;
        foreach ($response['problems'] as $problem_array) {
            // Get problem from DB
            $problem = \OmegaUp\DAO\Problems::getByAlias(
                $problems[$i]['request']['problem_alias']
            );

            // Assert data in DB
            $this->assertSame($problem->title, $problem_array['title']);
            $this->assertSame($problem->alias, $problem_array['alias']);
            $this->assertSame($problem->visits, $problem_array['visits']);
            $this->assertSame(
                $problem->submissions,
                $problem_array['submissions']
            );
            $this->assertSame($problem->accepted, $problem_array['accepted']);

            // Get points of problem from Contest-Problem relationship
            $problemInContest = \OmegaUp\DAO\ProblemsetProblems::getByPK(
                $contest->problemset_id,
                $problem->problem_id
            );
            $this->assertSame(
                $problemInContest->points,
                $problem_array['points']
            );
            $this->assertSame(
                $problemInContest->commit,
                $problem_array['commit']
            );
            $this->assertSame(
                $problemInContest->version,
                $problem_array['version']
            );

            $i++;
        }
    }

    /**
     * Get contest details for a public contest
     */
    public function testGetContestDetailsValid() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Get some problems into the contest
        $problems = \OmegaUp\Test\Factories\Contest::insertProblemsInContest(
            $contestData
        );

        // Get a user for our scenario
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Assert the log is empty.
        $this->assertSame(0, count(\OmegaUp\DAO\ProblemsetAccessLog::getByProblemsetIdentityId(
            $contestData['contest']->problemset_id,
            $identity->identity_id
        )));

        // Prepare our request
        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token,
        ]);

        // Explicitly join contest
        \OmegaUp\Controllers\Contest::apiOpen($r);

        // Call api
        $response = \OmegaUp\Controllers\Contest::apiDetails($r);

        $this->assertContestDetails($contestData, $problems, $response);

        // Assert the log is not empty.
        $this->assertSame(1, count(\OmegaUp\DAO\ProblemsetAccessLog::getByProblemsetIdentityId(
            $contestData['contest']->problemset_id,
            $identity->identity_id
        )));
    }

    /**
     * Language filter works.
     */
    public function testGetContestDetailsWithLanguageFilter() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['languages' => ['c11-gcc','cpp17-gcc','java']]
            )
        );

        // Get some problems into the contest
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem(new \OmegaUp\Test\Factories\ProblemParams([
            'visibility' => 'public',
            'languages' => 'cpp17-gcc,java,py3'
        ]));
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Get a user for our scenario
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Prepare our request
        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token,
        ]);

        // Explicitly join contest
        \OmegaUp\Controllers\Contest::apiOpen($r);

        // Call api
        $response = \OmegaUp\Controllers\Contest::apiDetails($r);

        $this->assertSame(1, count($response['problems']));
        // Verify that the allowed languages for the problem are the intersection of
        // the allowed languages.
        $this->assertSame(
            'cpp17-gcc,java',
            $response['problems'][0]['languages']
        );
    }

    /**
     * Check that user in private list can view private contest
     */
    public function testShowValidPrivateContest() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );

        // Get some problems into the contest
        $problems = \OmegaUp\Test\Factories\Contest::insertProblemsInContest(
            $contestData
        );

        // Get a user for our scenario
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Add user to our private contest
        \OmegaUp\Test\Factories\Contest::addUser($contestData, $identity);

        // Prepare our request
        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token,
        ]);

        // Call api
        $response = \OmegaUp\Controllers\Contest::apiDetails($r);

        $this->assertContestDetails($contestData, $problems, $response);
    }

    /**
     * Check if the plagiarism value is stored correctly in the database when a
     * contest is updated.
     */
    public function testPlagiarismThresholdValueInUpdatedContest() {
        // Create a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'checkPlagiarism' => true,
            ])
        );

        $response = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );
        $this->assertTrue($response->check_plagiarism);
    }

     /**
     * Check that user in private group list can view private contest
     */
    public function testShowValidPrivateContestFromGroup() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );

        // Get some problems into the contest
        $problems = \OmegaUp\Test\Factories\Contest::insertProblemsInContest(
            $contestData
        );

        // Get a user for our scenario
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Add user to our private contest
        {
            $login = self::login($contestData['director']);
            $groupData = \OmegaUp\Test\Factories\Groups::createGroup(
                login: $login
            );
            \OmegaUp\Test\Factories\Groups::addUserToGroup(
                $groupData,
                $identity,
                $login
            );
            \OmegaUp\Controllers\Contest::apiAddGroup(
                new \OmegaUp\Request([
                    'contest_alias' => strval($contestData['request']['alias']),
                    'group' => $groupData['group']->alias,
                    'auth_token' => $login->auth_token,
                ])
            );
        }

        $login = self::login($identity);
        $response = \OmegaUp\Controllers\Contest::apiDetails(
            new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $login->auth_token,
            ])
        );
        $this->assertContestDetails($contestData, $problems, $response);
    }

    /**
     * Don't show private contests for users that are not in the private list
     */
    public function testDontShowPrivateContestForAnyUser() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );

        // Get some problems into the contest
        \OmegaUp\Test\Factories\Contest::insertProblemsInContest(
            $contestData
        );

        // Get a user for our scenario
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Prepare our request
        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\Contest::apiDetails(new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $login->auth_token,
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * First access time should not change for Window Length contests
     */
    public function testAccessTimeIsAlwaysFirstAccessForWindowLength() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(new \OmegaUp\Test\Factories\ContestParams([
            'windowLength' => 20,
        ]));

        // Get a user for our scenario
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Prepare our request
        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token,
        ]);

        // Explicitly join contest
        \OmegaUp\Controllers\Contest::apiOpen($r);

        // Call api
        \OmegaUp\Controllers\Contest::apiDetails($r);

        // We need to grab the access time from the ContestUsers table
        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );

        $problemset_identity = \OmegaUp\DAO\ProblemsetIdentities::getByPK(
            $identity->identity_id,
            $contest->problemset_id
        );
        $firstAccessTime = $problemset_identity->access_time;

        // Call API again, access time should not change
        \OmegaUp\Controllers\Contest::apiDetails($r);

        $problemset_identity = \OmegaUp\DAO\ProblemsetIdentities::getByPK(
            $identity->identity_id,
            $contest->problemset_id
        );
        $this->assertSame(
            $firstAccessTime->time,
            $problemset_identity->access_time->time
        );
    }

    /**
     * First access time should not change
     */
    public function testAccessTimeIsAlwaysFirstAccess() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Get a user for our scenario
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Prepare our request
        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token,
        ]);

        // Explicitly join contest
        \OmegaUp\Controllers\Contest::apiOpen($r);

        // Call api
        \OmegaUp\Controllers\Contest::apiDetails($r);

        // We need to grab the access time from the ContestUsers table
        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );
        $problemset_identity = \OmegaUp\DAO\ProblemsetIdentities::getByPK(
            $identity->identity_id,
            $contest->problemset_id
        );
        $firstAccessTime = $problemset_identity->access_time;

        // Call API again, access time should not change
        \OmegaUp\Controllers\Contest::apiDetails($r);

        $problemset_identity = \OmegaUp\DAO\ProblemsetIdentities::getByPK(
            $identity->identity_id,
            $contest->problemset_id
        );
        $this->assertSame(
            $firstAccessTime->time,
            $problemset_identity->access_time->time
        );
    }

    /**
     * First access time should not change
     */
    public function testAccessTimeIsAlwaysFirstAccessForPrivate() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );

        // Get a user for our scenario
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Add user to our private contest
        \OmegaUp\Test\Factories\Contest::addUser($contestData, $identity);

        // Prepare our request
        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token,
        ]);

        // Call api
        \OmegaUp\Controllers\Contest::apiDetails($r);

        // We need to grab the access time from the ContestUsers table
        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );
        $problemset_identity = \OmegaUp\DAO\ProblemsetIdentities::getByPK(
            $identity->identity_id,
            $contest->problemset_id
        );
        $firstAccessTime = $problemset_identity->access_time;

        // Call API again, access time should not change
        \OmegaUp\Controllers\Contest::apiDetails($r);

        $problemset_identity = \OmegaUp\DAO\ProblemsetIdentities::getByPK(
            $identity->identity_id,
            $contest->problemset_id
        );
        $this->assertSame(
            $firstAccessTime->time,
            $problemset_identity->access_time->time
        );
    }

    /**
     * Try to view a contest before it has started
     */
    public function testContestNotStartedYet() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Get a user for our scenario
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Set contest to not started yet
        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );
        $contest->start_time = \OmegaUp\Time::get() + 30;
        \OmegaUp\DAO\Contests::update($contest);

        // Prepare our request
        $login = self::login($identity);

        try {
            \OmegaUp\Controllers\Contest::apiDetails(new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $login->auth_token,
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\PreconditionFailedException $e) {
            $this->assertSame('contestNotStarted', $e->getMessage());
        }
    }

    /**
     * Tests that user can get contest details with the scoreboard token
     */
    public function testDetailsUsingToken() {
        // Get a private contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );

        // Create our user not added to the contest
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        $originalContestAccessLog = \OmegaUp\DAO\ProblemsetAccessLog::getAll();

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

        // Call details using token
        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'token' => $scoreboard_url,
        ]);
        $detailsResponse = \OmegaUp\Controllers\Contest::apiDetails($r);
        unset($login);

        $this->assertContestDetails($contestData, [], $detailsResponse);

        // Call details using admin token
        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'token' => $scoreboard_admin_url,
        ]);
        $detailsResponse = \OmegaUp\Controllers\Contest::apiDetails($r);
        unset($login);

        $this->assertContestDetails($contestData, [], $detailsResponse);

        // All requests were done using tokens, so the log must be identical.
        $contestAccessLog = \OmegaUp\DAO\ProblemsetAccessLog::getAll();
        $this->assertSame($originalContestAccessLog, $contestAccessLog);
    }

    /**
     * Tests admin details. For /contest/.../edit/.
     */
    public function testContestAdminDetails() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        $contestDirector = $contestData['director'];

        $login = self::login($contestDirector);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Call apiDetails and apiAdminDetails. Now both calls should succeed.
        $detailsResponse = \OmegaUp\Controllers\Contest::apiDetails($r);
        $this->assertContestDetails($contestData, [], $detailsResponse);

        $adminDetailsResponse = \OmegaUp\Controllers\Contest::apiAdminDetails(
            $r
        );
        $this->assertContestDetails($contestData, [], $adminDetailsResponse);
    }

    /**
     * Test accesing api with invalid scoreboard token. Should fail.
     */
    public function testDetailsUsingInvalidToken() {
        // Get a private contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );

        // Create our user not added to the contest
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Call details using token
        $login = self::login($identity);
        try {
            \OmegaUp\Controllers\Contest::apiDetails(new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'token' => 'invalid token',
            ]));
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame('invalidScoreboardUrl', $e->getMessage());
        }
    }

    /**
     * Tests that user can get contest details with the scoreboard token
     */
    public function testDetailsNoLoginUsingToken() {
        // Get a private contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );

        // Get the scoreboard url by using the MyList api being the
        // contest director
        $login = self::login($contestData['director']);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
        ]);
        $response = \OmegaUp\Controllers\Contest::apiMyList($r);

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

        // Call details using token
        $detailsResponse = \OmegaUp\Controllers\Contest::apiDetails(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'token' => $scoreboard_url
        ]));

        $this->assertContestDetails($contestData, [], $detailsResponse);

        // Call details using admin token
        $detailsResponse = \OmegaUp\Controllers\Contest::apiDetails(new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'token' => $scoreboard_admin_url
        ]));

        $this->assertContestDetails($contestData, [], $detailsResponse);
    }

    /**
     * Tests contest report used in OMI
     */
    public function testContestReport() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        $contestDirector = $contestData['director'];

        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblemWithAuthor(
            $contestDirector
        );

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create our contestants
        $identities = [];
        for ($i = 0; $i < 3; $i++) {
            ['identity' => $identities[$i]] = \OmegaUp\Test\Factories\User::createUser();
        }

        ['identity' => $contestIdentityAdmin] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Contest::addAdminUser(
            $contestData,
            $contestIdentityAdmin
        );

        try {
            $detourGrader = new \OmegaUp\Test\ScopedGraderDetour();

            // Create runs
            $runsData = [];
            $runsData[0] = \OmegaUp\Test\Factories\Run::createRun(
                $problemData,
                $contestData,
                $identities[0]
            );
            \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
            $runsData[1] = \OmegaUp\Test\Factories\Run::createRun(
                $problemData,
                $contestData,
                $identities[0]
            );
            \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
            $runsData[2] = \OmegaUp\Test\Factories\Run::createRun(
                $problemData,
                $contestData,
                $identities[1]
            );
            \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
            $runsData[3] = \OmegaUp\Test\Factories\Run::createRun(
                $problemData,
                $contestData,
                $identities[2]
            );
            \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
            $runDataDirector = \OmegaUp\Test\Factories\Run::createRun(
                $problemData,
                $contestData,
                $contestDirector
            );
            \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
            $runDataAdmin = \OmegaUp\Test\Factories\Run::createRun(
                $problemData,
                $contestData,
                $contestIdentityAdmin
            );

            // Grade the runs
            \OmegaUp\Test\Factories\Run::gradeRun($runsData[0], 0, 'CE');
            \OmegaUp\Test\Factories\Run::gradeRun($runsData[1]);
            \OmegaUp\Test\Factories\Run::gradeRun($runsData[2], .9, 'PA');
            \OmegaUp\Test\Factories\Run::gradeRun($runsData[3], 1, 'AC', 180);
            \OmegaUp\Test\Factories\Run::gradeRun(
                $runDataDirector,
                1,
                'AC',
                120
            );
            \OmegaUp\Test\Factories\Run::gradeRun($runDataAdmin, 1, 'AC', 110);

            // Create API
            $login = self::login($contestDirector);
            $response = \OmegaUp\Controllers\Contest::apiReport(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'contest_alias' => $contestData['request']['alias'],
                ])
            );

            $this->assertSame(
                $problemData['request']['problem_alias'],
                $response['problems'][0]['alias']
            );

            foreach ($identities as $contestant) {
                $found = false;
                foreach ($response['ranking'] as $rank) {
                    if ($rank['username'] == $contestant->username) {
                        $found = true;
                        break;
                    }
                }
                $this->assertTrue($found);
            }

            // We can get ranking in getContestReportDetailsForTypeScript function
            $ranking = \OmegaUp\Controllers\Contest::getContestReportDetailsForTypeScript(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'contest_alias' => $contestData['request']['alias'],
                ])
            )['templateProperties']['payload']['contestReport'];
            unset($login);

            foreach ($identities as $contestant) {
                $found = false;
                foreach ($ranking as $rank) {
                    if ($rank['username'] == $contestant->username) {
                        $found = true;
                        break;
                    }
                }
                $this->assertTrue($found);
            }
        } finally {
            unset($detourGrader);
        }
    }

    /**
     * Check that user in private list can view private contest
     */
    public function testNoPrivilegeEscalationOccurs() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams(
                ['admissionMode' => 'private']
            )
        );

        // Get some problems into the contest
        $problems = \OmegaUp\Test\Factories\Contest::insertProblemsInContest(
            $contestData
        );

        // Get a user for our scenario
        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();

        // Prepare our request
        $login = self::login($identity);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Call api. This should fail.
        try {
            \OmegaUp\Controllers\Contest::apiDetails($r);
            $this->assertTrue(
                false,
                'User with no access could see the contest'
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            // Pass
            $this->assertSame('userNotAllowed', $e->getMessage());
        }

        // Get details from a problem in that contest. This should also fail.
        try {
            $problem_request = new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'problem_alias' => $problems[0]['request']['problem_alias'],
            ]);

            \OmegaUp\Controllers\Problem::apiDetails($problem_request);
            $this->assertTrue(
                false,
                'User with no access could see the problem'
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            // Pass
            $this->assertSame('userNotAllowed', $e->getMessage());
        }

        // Call api again. This should (still) fail.
        try {
            \OmegaUp\Controllers\Contest::apiDetails($r);
            $this->assertTrue(
                false,
                'User with no access could see the contest'
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            // Pass
            $this->assertSame('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Check that the download functionality works.
     */
    public function testDownload() {
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();
        $contestDirector = $contestData['director'];

        // Get a problem
        $problemData = \OmegaUp\Test\Factories\Problem::createProblemWithAuthor(
            $contestDirector
        );

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );

        // Create our contestants
        $identities = [];
        for ($i = 0; $i < 2; $i++) {
            ['identity' => $identities[$i]] = \OmegaUp\Test\Factories\User::createUser();
        }

        try {
            $detourGrader = new \OmegaUp\Test\ScopedGraderDetour();

            // Create runs
            $runsData = [];
            {
                $run = \OmegaUp\Test\Factories\Run::createRun(
                    $problemData,
                    $contestData,
                    $identities[0]
                );
                \OmegaUp\Test\Factories\Run::gradeRun($run, 0, 'CE');
                $runsData[] = $run;
            }

            {
                \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
                $run = \OmegaUp\Test\Factories\Run::createRun(
                    $problemData,
                    $contestData,
                    $identities[0]
                );
                \OmegaUp\Test\Factories\Run::gradeRun($run, 1, 'AC', 60);
                $runsData[] = $run;
            }

            {
                \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
                $run = \OmegaUp\Test\Factories\Run::createRun(
                    $problemData,
                    $contestData,
                    $identities[1]
                );
                \OmegaUp\Test\Factories\Run::gradeRun($run, .9, 'PA');
                $runsData[] = $run;
            }

            // Create a mock that stores the file name-contents mapping into an associative array.
            $files = [];
            include_once __DIR__ . '/../../server/libs/third_party/ZipStream.php';
            $zip = $this->createMock(ZipStream::class);
            $zip->method('add_file')
                ->will($this->returnCallback(function (
                    string $path,
                    string $contents,
                    array $opt = []
                ) use (&$files) {
                    $files[$path] = $contents;
                }));

            \OmegaUp\Controllers\Problemset::downloadRuns(
                $contestData['contest']->problemset_id,
                $zip
            );

            // Verify that the data is there.
            $summary = $files['summary.csv'];
            $this->assertNotEquals($summary, '');
            foreach ($runsData as $runData) {
                $this->assertSame(
                    $files["runs/{$runData['response']['guid']}.{$runData['request']['language']}"],
                    $runData['request']['source']
                );
                $this->assertStringContainsString(
                    "{$runData['response']['guid']},{$runData['contestant']->username},{$problemData['problem']->alias}",
                    $summary
                );
            }
        } finally {
            unset($detourGrader);
        }
    }

    public function testReplaceTeamsGroupInContestForTeamsViaApi() {
        // Create two teams groups
        [
            'teamGroup' => $teamGroup,
        ] = \OmegaUp\Test\Factories\Groups::createTeamsGroup();
        [
            'teamGroup' => $otherTeamGroup,
        ] = \OmegaUp\Test\Factories\Groups::createTeamsGroup();

        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'contestForTeams' => true,
                'teamsGroupAlias' => $teamGroup->alias,
            ])
        );

        $login = self::login($contestData['director']);

        $response = \OmegaUp\Controllers\Contest::getContestEditForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
            ])
        )['templateProperties']['payload'];

        $this->assertSame([
            'alias' => $teamGroup->alias,
            'name' =>  $teamGroup->name,
        ], $response['teams_group']);

        \OmegaUp\Controllers\Contest::apiReplaceTeamsGroup(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
                'teams_group_alias' => $otherTeamGroup->alias,
            ])
        );

        $response = \OmegaUp\Controllers\Contest::getContestEditForTypeScript(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'contest_alias' => $contestData['request']['alias'],
            ])
        )['templateProperties']['payload'];

        $this->assertSame([
            'alias' => $otherTeamGroup->alias,
            'name' =>  $otherTeamGroup->name,
        ], $response['teams_group']);
    }

    public function testReplaceTeamsGroupInContestViaApi() {
        // Get a teams group
        [
            'teamGroup' => $teamGroup,
        ] = \OmegaUp\Test\Factories\Groups::createTeamsGroup();

        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        $login = self::login($contestData['director']);

        try {
            \OmegaUp\Controllers\Contest::apiReplaceTeamsGroup(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'contest_alias' => $contestData['request']['alias'],
                    'teams_group_alias' => $teamGroup->alias,
                ])
            );
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            $this->assertSame(
                'teamsGroupsCanNotBeAddedInNormalContest',
                $e->getMessage()
            );
        }
    }

    public function testReplaceTeamsGroupInContestWithSubmissions() {
        // Identity creator group member will upload csv file
        [
            'identity' => $creatorIdentity,
        ] = \OmegaUp\Test\Factories\User::createGroupIdentityCreator();
        $creatorLogin = self::login($creatorIdentity);
        [
            'teamGroup' => $teamGroup,
        ] = \OmegaUp\Test\Factories\Groups::createTeamsGroup(
            $creatorIdentity,
            null,
            null,
            null,
            null,
            $creatorLogin
        );

        // Users to associate
        $identities = [];
        foreach (range(0, 9) as $id) {
            [
                'identity' => $identity,
            ] = \OmegaUp\Test\Factories\User::createUser(
                new \OmegaUp\Test\Factories\UserParams([
                    'username' => "user{$id}",
                ])
            );

            $identities[] = $identity;
        }

        // Call api using identity creator group member
        \OmegaUp\Controllers\Identity::apiBulkCreateForTeams(
            new \OmegaUp\Request([
                'auth_token' => $creatorLogin->auth_token,
                'team_identities' => \OmegaUp\Test\Factories\Identity::getCsvData(
                    'team_identities.csv',
                    $teamGroup->alias,
                    password: null,
                    forTeams: true
                ),
                'team_group_alias' => $teamGroup->alias,
            ])
        );

        $contestData = \OmegaUp\Test\Factories\Contest::createContest(
            new \OmegaUp\Test\Factories\ContestParams([
                'title' => 'Contest_For_Teams',
                'contestForTeams' => true,
                'teamsGroupAlias' => $teamGroup->alias,
            ])
        );

        // Get a problem
        $problem = \OmegaUp\Test\Factories\Problem::createProblem();

        // Add the problem to the contest
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problem,
            $contestData
        );

        $login = self::login($identities[0]);

        [
            'identities' => $associatedIdentities,
        ] = \OmegaUp\Controllers\User::apiListAssociatedIdentities(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
            ])
        );

        // User switch the account
        \OmegaUp\Controllers\Identity::apiSelectIdentity(
            new \OmegaUp\Request([
                'auth_token' => $login->auth_token,
                'usernameOrEmail' => $associatedIdentities[1]['username'],
            ])
        );

        \OmegaUp\Test\Factories\Contest::openContest(
            $contestData['contest'],
            $identities[0]
        );
        \OmegaUp\Test\Factories\Contest::openProblemInContest(
            $contestData,
            $problem,
            $identities[0]
        );

        // User creates a run in a valid time
        $run = \OmegaUp\Test\Factories\Run::createRun(
            $problem,
            $contestData,
            $identities[0]
        );
        \OmegaUp\Test\Factories\Run::gradeRun($run, 1.0, 'AC', 10);

        $login = self::login($contestData['director']);
        try {
            \OmegaUp\Controllers\Contest::apiReplaceTeamsGroup(
                new \OmegaUp\Request([
                    'auth_token' => $login->auth_token,
                    'contest_alias' => $contestData['request']['alias'],
                    'teams_group_alias' => $teamGroup->alias,
                ])
            );
            $this->fail('Should have failed');
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            $this->assertSame(
                'contestEditCannotReplaceTeamsGroupWithSubmissions',
                $e->getMessage()
            );
        }
    }

    public function testContestPrintDetails() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        ['identity' => $identity] = \OmegaUp\Test\Factories\User::createUser();
        \OmegaUp\Test\Factories\Contest::addAdminUser(
            $contestData,
            $identity
        );

        $expectedAliases = [];
        // Get some problems into the contest
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );
        $expectedAliases[] = $problemData['problem']->alias;
        $problemData = \OmegaUp\Test\Factories\Problem::createProblem();
        \OmegaUp\Test\Factories\Contest::addProblemToContest(
            $problemData,
            $contestData
        );
        $expectedAliases[] = $problemData['problem']->alias;

        $login = self::login($identity);

        $response = \OmegaUp\Controllers\Contest::getContestPrintDetailsForTypeScript(
            new \OmegaUp\Request([
                'alias' => $contestData['request']['alias'],
                'auth_token' => $login->auth_token,
            ])
        )['templateProperties'];
        $problems = $response['payload']['problems'];

        $this->assertTrue($response['hideFooterAndHeader']);
        $this->assertCount(2, $problems);

        $aliases = array_map(fn ($problem) => $problem['alias'], $problems);
        $this->assertSame($aliases, $expectedAliases);
    }

    /**
     * Check that support team members can access contest details without
     * recording first access time
     */
    public function testSupportTeamMemberCanAccessContest() {
        // Get a contest
        $contestData = \OmegaUp\Test\Factories\Contest::createContest();

        // Get some problems into the contest
        $problems = \OmegaUp\Test\Factories\Contest::insertProblemsInContest(
            $contestData
        );

        // Create a support team member
        ['identity' => $supportTeamMember] = \OmegaUp\Test\Factories\User::createSupportUser();

        // Assert the log is empty
        $this->assertEmpty(\OmegaUp\DAO\ProblemsetAccessLog::getByProblemsetIdentityId(
            $contestData['contest']->problemset_id,
            $supportTeamMember->identity_id
        ));

        // Prepare our request
        $login = self::login($supportTeamMember);

        // Call api directly (without explicitly joining the contest)
        $response = \OmegaUp\Controllers\Contest::apiDetails(
            new \OmegaUp\Request([
                'contest_alias' => $contestData['request']['alias'],
                'auth_token' => $login->auth_token,
            ])
        );

        $this->assertContestDetails($contestData, $problems, $response);

        // Assert that a problemset identity was NOT created for the support team member
        $problemsetIdentity = \OmegaUp\DAO\ProblemsetIdentities::getByPK(
            $contestData['contest']->problemset_id,
            $supportTeamMember->identity_id
        );
        $this->assertNull($problemsetIdentity);

        // Assert the log is not empty (should still log access)
        $this->assertCount(1, \OmegaUp\DAO\ProblemsetAccessLog::getByProblemsetIdentityId(
            $contestData['contest']->problemset_id,
            $supportTeamMember->identity_id
        ));
    }
}
