<?php

/**
 * Description of DetailsContest
 *
 * @author joemmanuel
 */

class ContestDetailsTest extends OmegaupTestCase {
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
        $this->assertEquals($contest->description, $response['description']);
        $this->assertEquals($contest->start_time, $response['start_time']);
        $this->assertEquals($contest->finish_time, $response['finish_time']);
        $this->assertEquals(
            $contest->window_length,
            $response['window_length']
        );
        $this->assertEquals($contest->alias, $response['alias']);
        $this->assertEquals(
            $contest->points_decay_factor,
            $response['points_decay_factor']
        );
        $this->assertEquals(
            $contest->partial_score,
            $response['partial_score']
        );
        $this->assertEquals(
            $contest->submissions_gap,
            $response['submissions_gap']
        );
        $this->assertEquals($contest->feedback, $response['feedback']);
        $this->assertEquals($contest->penalty, $response['penalty']);
        $this->assertEquals($contest->scoreboard, $response['scoreboard']);
        $this->assertEquals($contest->penalty_type, $response['penalty_type']);
        $this->assertEquals(
            $contest->penalty_calc_policy,
            $response['penalty_calc_policy']
        );

        // Assert we have our problems
        $numOfProblems = count($problems);
        $this->assertEquals($numOfProblems, count($response['problems']));

        // Assert problem data
        $i = 0;
        foreach ($response['problems'] as $problem_array) {
            // Get problem from DB
            $problem = \OmegaUp\DAO\Problems::getByAlias(
                $problems[$i]['request']['problem_alias']
            );

            // Assert data in DB
            $this->assertEquals($problem->title, $problem_array['title']);
            $this->assertEquals($problem->alias, $problem_array['alias']);
            $this->assertEquals($problem->visits, $problem_array['visits']);
            $this->assertEquals(
                $problem->submissions,
                $problem_array['submissions']
            );
            $this->assertEquals($problem->accepted, $problem_array['accepted']);

            // Get points of problem from Contest-Problem relationship
            $problemInContest = \OmegaUp\DAO\ProblemsetProblems::getByPK(
                $contest->problemset_id,
                $problem->problem_id
            );
            $this->assertEquals(
                $problemInContest->points,
                $problem_array['points']
            );
            $this->assertEquals(
                $problemInContest->commit,
                $problem_array['commit']
            );
            $this->assertEquals(
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
        $contestData = ContestsFactory::createContest();

        // Get some problems into the contest
        $problems = ContestsFactory::insertProblemsInContest($contestData);

        // Get a user for our scenario
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        // Assert the log is empty.
        $this->assertEquals(0, count(\OmegaUp\DAO\ProblemsetAccessLog::getByProblemsetIdentityId(
            $contestData['contest']->problemset_id,
            $contestant->identity_id
        )));

        // Prepare our request
        $login = self::login($contestant);
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
        $this->assertEquals(1, count(\OmegaUp\DAO\ProblemsetAccessLog::getByProblemsetIdentityId(
            $contestData['contest']->problemset_id,
            $contestant->identity_id
        )));
    }

    /**
     * Language filter works.
     */
    public function testGetContestDetailsWithLanguageFilter() {
        // Get a contest
        $contestData = ContestsFactory::createContest(
            new ContestParams(
                ['languages' => ['c','cpp','java']]
            )
        );

        // Get some problems into the contest
        $problemData = ProblemsFactory::createProblem(new ProblemParams([
            'visibility' => 1,
            'languages' => 'cpp,java,py'
        ]));
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Get a user for our scenario
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        // Prepare our request
        $login = self::login($contestant);
        $r = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token,
        ]);

        // Explicitly join contest
        \OmegaUp\Controllers\Contest::apiOpen($r);

        // Call api
        $response = \OmegaUp\Controllers\Contest::apiDetails($r);

        $this->assertEquals(1, count($response['problems']));
        // Verify that the allowed languages for the problem are the intersection of
        // the allowed languages.
        $this->assertEquals('cpp,java', $response['problems'][0]['languages']);
    }

    /**
     * Check that user in private list can view private contest
     */
    public function testShowValidPrivateContest() {
        // Get a contest
        $contestData = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
            )
        );

        // Get some problems into the contest
        $problems = ContestsFactory::insertProblemsInContest($contestData);

        // Get a user for our scenario
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        // Add user to our private contest
        ContestsFactory::addUser($contestData, $contestant);

        // Prepare our request
        $login = self::login($contestant);
        $r = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token,
        ]);

        // Call api
        $response = \OmegaUp\Controllers\Contest::apiDetails($r);

        $this->assertContestDetails($contestData, $problems, $response);
    }

    /**
     * Dont show private contests for users that are not in the private list
     *
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testDontShowPrivateContestForAnyUser() {
        // Get a contest
        $contestData = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
            )
        );

        // Get some problems into the contest
        $problems = ContestsFactory::insertProblemsInContest($contestData);

        // Get a user for our scenario
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        // Prepare our request
        $login = self::login($contestant);
        $r = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token,
        ]);

        // Call api
        $response = \OmegaUp\Controllers\Contest::apiDetails($r);
    }

    /**
     * First access time should not change for Window Length contests
     */
    public function testAccessTimeIsAlwaysFirstAccessForWindowLength() {
        // Get a contest
        $contestData = ContestsFactory::createContest(new ContestParams([
            'window_length' => 20
        ]));

        // Get a user for our scenario
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        // Prepare our request
        $login = self::login($contestant);
        $r = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token,
        ]);

        // Explicitly join contest
        \OmegaUp\Controllers\Contest::apiOpen($r);

        // Call api
        $response = \OmegaUp\Controllers\Contest::apiDetails($r);

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
        $response = \OmegaUp\Controllers\Contest::apiDetails($r);

        $problemset_identity = \OmegaUp\DAO\ProblemsetIdentities::getByPK(
            $identity->identity_id,
            $contest->problemset_id
        );
        $this->assertEquals(
            $firstAccessTime,
            $problemset_identity->access_time
        );
>>>>>>> upstream/master
    }

    /**
     * First access time should not change
     */
    public function testAccessTimeIsAlwaysFirstAccess() {
        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Get a user for our scenario
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        // Prepare our request
        $login = self::login($contestant);
        $r = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token,
        ]);

        // Explicitly join contest
        \OmegaUp\Controllers\Contest::apiOpen($r);

        // Call api
        $response = \OmegaUp\Controllers\Contest::apiDetails($r);

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
        $response = \OmegaUp\Controllers\Contest::apiDetails($r);

        $problemset_identity = \OmegaUp\DAO\ProblemsetIdentities::getByPK(
            $identity->identity_id,
            $contest->problemset_id
        );
        $this->assertEquals(
            $firstAccessTime,
            $problemset_identity->access_time
        );
    }

    /**
     * First access time should not change
     */
    public function testAccessTimeIsAlwaysFirstAccessForPrivate() {
        // Get a contest
        $contestData = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
            )
        );

        // Get a user for our scenario
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        // Add user to our private contest
        ContestsFactory::addUser($contestData, $contestant);

        // Prepare our request
        $login = self::login($contestant);
        $r = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token,
        ]);

        // Call api
        $response = \OmegaUp\Controllers\Contest::apiDetails($r);

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
        $response = \OmegaUp\Controllers\Contest::apiDetails($r);

        $problemset_identity = \OmegaUp\DAO\ProblemsetIdentities::getByPK(
            $identity->identity_id,
            $contest->problemset_id
        );
        $this->assertEquals(
            $firstAccessTime,
            $problemset_identity->access_time
        );
    }

    /**
     * Try to view a contest before it has started
     *
     * @expectedException \OmegaUp\Exceptions\PreconditionFailedException
     */
    public function testContestNotStartedYet() {
        // Get a contest
        $contestData = ContestsFactory::createContest();

        // Get a user for our scenario
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        // Set contest to not started yet
        $contest = \OmegaUp\DAO\Contests::getByAlias(
            $contestData['request']['alias']
        );
        $contest->start_time = Utils::GetTimeFromUnixTimestamp(
            \OmegaUp\Time::get() + 30
        );
        \OmegaUp\DAO\Contests::update($contest);

        // Prepare our request
        $login = self::login($contestant);
        $r = new \OmegaUp\Request([
            'contest_alias' => $contestData['request']['alias'],
            'auth_token' => $login->auth_token,
        ]);

        // Call api
        $response = \OmegaUp\Controllers\Contest::apiDetails($r);
    }

    /**
     * Tests that user can get contest details with the scoreboard token
     */
    public function testDetailsUsingToken() {
        // Get a private contest
        $contestData = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
            )
        );

        // Create our user not added to the contest
        ['user' => $externalUser, 'identity' => $identity] = UserFactory::createUser();

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
        $login = self::login($externalUser);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'token' => $scoreboard_url,
        ]);
        $detailsResponse = \OmegaUp\Controllers\Contest::apiDetails($r);
        unset($login);

        $this->assertContestDetails($contestData, [], $detailsResponse);

        // Call details using admin token
        $login = self::login($externalUser);
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
        $this->assertEquals($originalContestAccessLog, $contestAccessLog);
    }

    /**
     * Tests admin details. For /contest/.../edit/.
     */
    public function testContestAdminDetails() {
        // Get a contest
        $contestData = ContestsFactory::createContest();
        $contestDirector = $contestData['director'];

        $login = self::login($contestDirector);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]);

        // Call api. This should fail.
        try {
            \OmegaUp\Controllers\Contest::apiDetails($r);
            $this->assertTrue(
                false,
                'User that has not opened contest was able to see its details'
            );
        } catch (\OmegaUp\Exceptions\ForbiddenAccessException $e) {
            // Pass
        }

        // Call admin api. This should succeed.
        $detailsResponse = \OmegaUp\Controllers\Contest::apiAdminDetails($r);
        $this->assertContestDetails($contestData, [], $detailsResponse);
    }

    /**
     * Test accesing api with invalid scoreboard token. Should fail.
     *
     * @expectedException \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public function testDetailsUsingInvalidToken() {
        // Get a private contest
        $contestData = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
            )
        );

        // Create our user not added to the contest
        ['user' => $externalUser, 'identity' => $identity] = UserFactory::createUser();

        // Call details using token
        $login = self::login($externalUser);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
            'token' => 'invalid token',
        ]);
        $detailsResponse = \OmegaUp\Controllers\Contest::apiDetails($r);
    }

    /**
     * Tests that user can get contest details with the scoreboard token
     */
    public function testDetailsNoLoginUsingToken() {
        // Get a private contest
        $contestData = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
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
        $contestData = ContestsFactory::createContest();
        $contestDirector = $contestData['director'];

        // Get a problem
        $problemData = ProblemsFactory::createProblemWithAuthor(
            $contestDirector
        );

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestants
        $contestants = [];
        $identities = [];
        for ($i = 0; $i < 3; $i++) {
            ['user' => $contestants[$i], 'identity' => $identities[$i]] = UserFactory::createUser();
        }

        ['user' => $contestAdmin, 'identity' => $identity] = UserFactory::createUser();
        ContestsFactory::addAdminUser($contestData, $contestAdmin);

        $detourGrader = new ScopedGraderDetour();

        // Create runs
        $runsData = [];
        $runsData[0] = RunsFactory::createRun(
            $problemData,
            $contestData,
            $contestants[0]
        );
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
        $runsData[1] = RunsFactory::createRun(
            $problemData,
            $contestData,
            $contestants[0]
        );
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
        $runsData[2] = RunsFactory::createRun(
            $problemData,
            $contestData,
            $contestants[1]
        );
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
        $runsData[3] = RunsFactory::createRun(
            $problemData,
            $contestData,
            $contestants[2]
        );
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
        $runDataDirector = RunsFactory::createRun(
            $problemData,
            $contestData,
            $contestDirector
        );
        \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
        $runDataAdmin = RunsFactory::createRun(
            $problemData,
            $contestData,
            $contestAdmin
        );

        // Grade the runs
        RunsFactory::gradeRun($runsData[0], 0, 'CE');
        RunsFactory::gradeRun($runsData[1]);
        RunsFactory::gradeRun($runsData[2], .9, 'PA');
        RunsFactory::gradeRun($runsData[3], 1, 'AC', 180);
        RunsFactory::gradeRun($runDataDirector, 1, 'AC', 120);
        RunsFactory::gradeRun($runDataAdmin, 1, 'AC', 110);

        // Create API
        $login = self::login($contestDirector);
        $r = new \OmegaUp\Request([
            'auth_token' => $login->auth_token,
            'contest_alias' => $contestData['request']['alias'],
        ]);
        $response = \OmegaUp\Controllers\Contest::apiReport($r);
        unset($login);

        $this->assertEquals(
            $problemData['request']['problem_alias'],
            $response['problems'][0]['alias']
        );

        foreach ($contestants as $contestant) {
            $found = false;
            foreach ($response['ranking'] as $rank) {
                if ($rank['username'] == $contestant->username) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found);
        }
    }

    /**
     * Check that user in private list can view private contest
     */
    public function testNoPrivilegeEscalationOccurs() {
        // Get a contest
        $contestData = ContestsFactory::createContest(
            new ContestParams(
                ['admission_mode' => 'private']
            )
        );

        // Get some problems into the contest
        $problems = ContestsFactory::insertProblemsInContest($contestData);

        // Get a user for our scenario
        ['user' => $contestant, 'identity' => $identity] = UserFactory::createUser();

        // Prepare our request
        $login = self::login($contestant);
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
            $this->assertEquals('userNotAllowed', $e->getMessage());
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
            $this->assertEquals('userNotAllowed', $e->getMessage());
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
            $this->assertEquals('userNotAllowed', $e->getMessage());
        }
    }

    /**
     * Check that the download functionality works.
     */
    public function testDownload() {
        $contestData = ContestsFactory::createContest();
        $contestDirector = $contestData['director'];

        // Get a problem
        $problemData = ProblemsFactory::createProblemWithAuthor(
            $contestDirector
        );

        // Add the problem to the contest
        ContestsFactory::addProblemToContest($problemData, $contestData);

        // Create our contestants
        $contestants = [];
        $identities = [];
        for ($i = 0; $i < 2; $i++) {
            ['user' => $contestants[$i], 'identity' => $identities[$i]] = UserFactory::createUser();
        }

        $detourGrader = new ScopedGraderDetour();

        // Create runs
        $runsData = [];
        {
            $run = RunsFactory::createRun(
                $problemData,
                $contestData,
                $contestants[0]
            );
            RunsFactory::gradeRun($run, 0, 'CE');
            $runsData[] = $run;
        }

        {
            \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
            $run = RunsFactory::createRun(
                $problemData,
                $contestData,
                $contestants[0]
            );
            RunsFactory::gradeRun($run, 1, 'AC', 60);
            $runsData[] = $run;
        }

        {
            \OmegaUp\Time::setTimeForTesting(\OmegaUp\Time::get() + 60);
            $run = RunsFactory::createRun(
                $problemData,
                $contestData,
                $contestants[1]
            );
            RunsFactory::gradeRun($run, .9, 'PA');
            $runsData[] = $run;
        }

        // Create a mock that stores the file name-contents mapping into an associative array.
        $files = [];
        include_once 'libs/third_party/ZipStream.php';
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
            $this->assertEquals(
                $files["runs/{$runData['response']['guid']}.{$runData['request']['language']}"],
                $runData['request']['source']
            );
            $this->assertContains(
                "{$runData['response']['guid']},{$runData['contestant']->username},{$problemData['problem']->alias}",
                $summary
            );
        }
    }
}
